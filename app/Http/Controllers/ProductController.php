<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    /**
     * 1. load product filter page (categories, suppliers)
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
     * 2. Ajax call to fetch products (Search,filter,sort,pagination)
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

            // make pagination
            $products = $query->paginate(9)->withQueryString();

            // json response
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

    /**
     * Get product details by SKU (POST request)
     */
    public function getProductBySku(Request $request)
    {
        try {
            // 1. validate the request data
            $validated = $request->validate([
                'sku' => 'required|string|max:100',
            ]);

            // 2. get the product by SKU
            $product = Product::with(['category', 'supplier'])
                ->where('sku', $validated['sku'])
                ->first();

            // 3. check if the product exists
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with the given SKU'
                ], 404);
            }

            // 4. respond with the product details
            return response()->json([
                'success' => true,
                'data'    => $product
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // get validation error and return response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);

        } catch (Exception $e) {
            // get error message and return response
            Log::error('Product getProductBySku error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Get product details fail, please try again',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function inbound(Request $request)
    {
        // 1. validate request data
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // use transaction to ensure data integrity
        DB::beginTransaction();
        try {
            $items = $request->input('items');

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $addQty = (int) $item['quantity'];

                // search product by id
                $product = Product::findOrFail($productId);

                // 2. update quantity
                $product->increment('stock_quantity', $addQty);

                // 3. create stock transaction record
                StockTransaction::create([
                    'product_id' => $product->id,
                    'quantity'   => $addQty,
                    'action'       => 'inbound',
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inbound processed successfully!'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Inbound transaction error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Inbound failed, please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getProductsStockTransaction(Request $request)
    {
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $perPage = 10;
        
        $transactions = StockTransaction::where('product_id', $productId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // format the data
        $transformedData = collect($transactions->items())->map(function ($tx) {
            return [
                'id' => $tx->id,
                'action' => $tx->action,
                'quantity' => $tx->quantity,
                'create_at' => $tx->create_at ? date('Y-m-d H:i:s', strtotime($tx->create_at)) : null,
            ];
        });

        return response()->json([
            'data' => $transformedData,
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'total' => $transactions->total(),
        ]);
    }

    public function updateStock(Request $request)
    {
        // 1. validate
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'value' => 'required|string', 
        ]);

        $productId = $request->input('product_id');
        $inputVal = trim($request->input('value'));

        // 2. search product
        $product = Product::findOrFail($productId);
        $currentStock = $product->stock_quantity ?? 0;

        $quantityChange = 0;
        $actionType = 'Adjustment'; 

        // 3. check input value and calculate new stock quantity
        if (str_starts_with($inputVal, '+')) {
            $quantityChange = (int) substr($inputVal, 1);
            $newStock = $currentStock + $quantityChange;
            $actionType = 'Restock / Increase';
        } elseif (str_starts_with($inputVal, '-')) {
            $quantityChange = -(int) substr($inputVal, 1);
            $newStock = $currentStock + $quantityChange;
            $actionType = 'Reduce / Outbound';
        } else {
            // if input is a number, set stock to that number
            $targetStock = (int) $inputVal;
            $quantityChange = $targetStock - $currentStock;
            $newStock = $targetStock;
            $actionType = 'Manual Set';
        }

        // let 's check if the new stock quantity is valid
        if ($newStock < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Stock quantity cannot be less than 0.'
            ], 422);
        }

        // 4. transaction to update stock and create a new stock
        try {
            DB::transaction(function () use ($product, $newStock, $quantityChange, $actionType) {
                // update stock quantity
                $product->update([
                    'stock_quantity' => $newStock
                ]);

                // insert a new stock transaction record
                StockTransaction::create([
                    'product_id' => $product->id,
                    'quantity' => $quantityChange,
                    'action' => $actionType,
                    'create_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully.',
                'new_stock' => $newStock
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }
}