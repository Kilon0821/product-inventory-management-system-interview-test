<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    /**
     * 1. 只负责渲染初始页面（加载分类和供应商数据到侧边栏）
     */
    public function index()
    {
        try {
            $categories = Category::all();
            $suppliers = Supplier::all();

            return view('product_filter', compact('categories', 'suppliers'));
        } catch (Exception $e) {
            Log::error('Product index error: ' . $e->getMessage());
            return back()->with('error', 'Load page failed, please try again.');
        }
    }

    /**
     * 2. 只影响 Table 的 AJAX 接口（搜索、筛选、排序、分页）
     */
    public function getProducts(Request $request)
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

            // 只获取 Product 数据及分页信息
            $products = $query->paginate(9)->withQueryString();

            // 纯粹返回 JSON 数据供表格渲染
            return response()->json([
                'success' => true,
                'data'    => $products
            ]);

        } catch (Exception $e) {
            Log::error('getProducts error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Load products failed, please try again.'
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|max:100|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            $product = DB::transaction(function () use ($validated) {
                return Product::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data'    => $product
            ], 201);

        } catch (Exception $e) {
            Log::error('Product store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'create fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        try {
            return response()->json(['success' => true,'data' => $product->load(['category', 'supplier'])]);
        } catch (Exception $e) {
            Log::error('Product show error: ' . $e->getMessage());
            return response()->json(['success' => false,'message' => 'get product details fail'], 500);
        }
    }

    /**
     * Update the specified product using Database Transaction.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name'        => 'sometimes|required|string|max:255',
            'sku'         => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'price'       => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        try {
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
            Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'update fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        try {
            DB::transaction(function () use ($product) {
                $product->delete();
            });

            return response()->json(['success' => true,'message' => 'Product deleted successfully'], 200);

        } catch (Exception $e) {
            Log::error('Product destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'delete fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}