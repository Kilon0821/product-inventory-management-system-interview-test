<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApiController extends Controller
{
    /**
     * User Login (Generate Token)
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ]);

            // 使用 Auth::attempt 验证账号密码
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials do not match our records.'
                ], 401);
            }

            // 获取当前登录用户
            $user = Auth::user();

            // 生成 Sanctum Token (需要 User 模型引入 HasApiTokens)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successfully',
                'token'   => $token,
                'data'    => $user
            ], 200);

        } catch (Exception $e) {
            Log::error('API login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login failed, please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * User Logout (Revoke Token)
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('API logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Logout failed, please try again.'
            ], 500);
        }
    }

    /**
     * 1. Get product list (supports search, filtering, sorting, and pagination)
     */
    public function product_list(Request $request)
    {
        try {
            $query = Product::with(['category', 'supplier']);

            // Search Filter (by Name or SKU)
            if ($request->filled('search')) {
                $search = addcslashes($request->search, '%_');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            // Category Filter
            if ($request->filled('categories')) {
                $categories = is_array($request->categories) 
                    ? $request->categories 
                    : explode(',', $request->categories);
                $query->whereIn('category_id', $categories);
            }

            // Supplier Filter
            if ($request->filled('suppliers')) {
                $suppliers = is_array($request->suppliers) 
                    ? $request->suppliers 
                    : explode(',', $request->suppliers);
                $query->whereIn('supplier_id', $suppliers);
            }

            // Price Range Filter
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Sorting
            switch ($request->sort) {
                case 'price_low_high':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high_low':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }

            $products = $query->paginate(9)->withQueryString();

            return response()->json([
                'success' => true,
                'data'    => $products
            ]);

        } catch (Exception $e) {
            Log::error('API product_list error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Load products failed, please try again.'
            ], 500);
        }
    }

    /**
     * 2. Get single product details
     */
    public function get_product(Request $request, $id)
    {
        try {
            $product = Product::with(['category', 'supplier'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $product
            ], 200);

        } catch (Exception $e) {
            Log::error('API get_product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Product not found or get details failed'
            ], 404);
        }
    }

    /**
     * 3. Store a newly created product
     */
    public function store_product(StoreProductRequest $request)
    {
        $validated = $request->validated();

        try {
            $product = DB::transaction(function () use ($validated) {
                return Product::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data'    => $product->load(['category', 'supplier'])
            ], 201);

        } catch (Exception $e) {
            Log::error('API store_product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Create fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 4. Update the specified product
     */
    public function update_product(UpdateProductRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            // 1. get product by id
            $product = Product::findOrFail($id);

            // 2. refresh product data
            $product = DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
                return $product->fresh(['category', 'supplier']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data'    => $product
            ], 200);

        } catch (Exception $e) {
            Log::error('API update_product error: ' . $e->getMessage());
            
            // if not found return 404
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Update fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 5. Remove the specified product
     */
    public function destroy_product(Request $request, $id)
    {
        try {
            // 1. get product by id
            $product = Product::findOrFail($id);

            // 2. delete product
            DB::transaction(function () use ($product) {
                $product->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('API destroy_product error: ' . $e->getMessage());

            // if not found return 404
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Delete fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get all categories with limit and offset
     */
    public function get_categories(Request $request)
    {
        try {
            // request params limit and offset，if no value will set default（example:  limit 10, offset 0）
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);

            $categories = \App\Models\Category::offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $categories
            ], 200);

        } catch (Exception $e) {
            Log::error('API get_categories error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Load categories failed, please try again.'
            ], 500);
        }
    }

    /**
     * Get all suppliers with limit and offset
     */
    public function get_suppliers(Request $request)
    {
        try {
            // request params limit and offset，if no value will set default（example:  limit 10, offset 0）
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);

            $suppliers = \App\Models\Supplier::offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $suppliers
            ], 200);

        } catch (Exception $e) {
            Log::error('API get_suppliers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Load suppliers failed, please try again.'
            ], 500);
        }
    }
}