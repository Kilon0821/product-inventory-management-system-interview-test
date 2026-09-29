<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Catalog</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header with Add Product Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <h1 class="text-3xl font-bold text-gray-900">All Products</h1>
            <button 
                type="button" 
                onclick="openProductModal('create')"
                class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg shadow-sm transition gap-2"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Product</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Sidebar / Filter Options -->
            <aside class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit lg:col-span-1">
                <form id="filterForm" onsubmit="event.preventDefault(); loadProducts(1);">
                    
                    <!-- Search Bar (Name or SKU) -->
                    <div class="mb-6">
                        <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                id="search" 
                                placeholder="Search by name or SKU..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                            >
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Categories Filter -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Categories</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($categories as $category)
                                <label class="flex items-center space-x-3 text-sm text-gray-600 cursor-pointer hover:text-gray-900">
                                    <input 
                                        type="checkbox" 
                                        name="categories[]" 
                                        value="{{ $category->id }}"
                                        onchange="loadProducts(1)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                    >
                                    <span>{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Suppliers Filter -->
                    @if(isset($suppliers) &&$suppliers->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Suppliers</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                @foreach($suppliers as $supplier)
                                    <label class="flex items-center space-x-3 text-sm text-gray-600 cursor-pointer hover:text-gray-900">
                                        <input 
                                            type="checkbox" 
                                            name="suppliers[]" 
                                            value="{{ $supplier->id }}"
                                            onchange="loadProducts(1)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                        >
                                        <span>{{ $supplier->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <hr class="my-6 border-gray-200">
                    @endif

                    <!-- Price Range -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Price Range</h3>
                        <div class="flex items-center space-x-2">
                            <input 
                                type="number" 
                                step="0.01" 
                                name="min_price" 
                                placeholder="Min" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            >
                            <span class="text-gray-400">-</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="max_price" 
                                placeholder="Max" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            >
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Filter Actions -->
                    <div class="space-y-2">
                        <button 
                            type="submit" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition duration-150 ease-in-out shadow-sm"
                        >
                            Apply Filters
                        </button>

                        <button 
                            type="button" 
                            onclick="resetFilters()"
                            class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg text-sm transition"
                        >
                            Clear All
                        </button>
                    </div>
                </form>
            </aside>

            <!-- Product Table Section (局部动态更新区域) -->
            <main class="lg:col-span-3">
                
                <!-- Sorting & Product Count Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 gap-4">
                    <p class="text-sm text-gray-600">
                        Showing <span id="totalProductsCount" class="font-semibold text-gray-900">0</span> products
                    </p>

                    <div class="flex items-center space-x-2 w-full sm:w-auto">
                        <label for="sortSelect" class="text-sm text-gray-600 whitespace-nowrap">Sort by:</label>
                        <select 
                            name="sort" 
                            id="sortSelect"
                            onchange="loadProducts(1)" 
                            class="w-full sm:w-auto border border-gray-300 rounded-lg py-1.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700"
                        >
                            <option value="latest">Latest</option>
                            <option value="price_low_high">Price: Low to High</option>
                            <option value="price_high_low">Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Products Table Container -->
                <div id="tableContainer" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden relative">
                    
                    <!-- Loading Mask -->
                    <div id="tableLoader" class="absolute inset-0 bg-white/70 flex items-center justify-center z-10 hidden">
                        <i class="fa-solid fa-spinner fa-spin text-2xl text-indigo-600"></i>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th scope="col" class="py-3.5 px-4">Product</th>
                                    <th scope="col" class="py-3.5 px-4">SKU</th>
                                    <th scope="col" class="py-3.5 px-4">Category</th>
                                    <th scope="col" class="py-3.5 px-4">Supplier</th>
                                    <th scope="col" class="py-3.5 px-4 text-right">Price</th>
                                    <th scope="col" class="py-3.5 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody" class="divide-y divide-gray-100 text-sm text-gray-700">
                                <!-- 由 JS 异步填充 -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State Container -->
                <div id="emptyState" class="hidden text-center py-16 bg-white rounded-xl border border-gray-100 shadow-sm">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-900">No products found</h3>
                    <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or search terms.</p>
                    <button type="button" onclick="resetFilters()" class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Reset all filters
                    </button>
                </div>

                <!-- Pagination Container -->
                <div id="paginationContainer" class="mt-8 flex justify-center items-center gap-2">
                    <!-- 由 JS 动态生成分页按钮 -->
                </div>

            </main>
        </div>
    </div>

    <!-- Product Modal (Create & Edit) -->
    <div id="productModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden transform transition-all">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Add Product</h3>
                <button type="button" onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="productForm" onsubmit="handleProductSubmit(event)">
                <input type="hidden" id="productId" name="id">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="modal_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div>
                        <label for="modal_sku" class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input type="text" id="modal_sku" name="sku" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div>
                        <label for="modal_price" class="block text-sm font-medium text-gray-700 mb-1">Price ($) *</label>
                        <input type="number" step="0.01" id="modal_price" name="price" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div>
                        <label for="modal_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="modal_category_id" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($suppliers))
                    <div>
                        <label for="modal_supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <select id="modal_supplier_id" name="supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div>
                        <label for="modal_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="modal_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-100 space-x-3">
                    <button type="button" onclick="closeProductModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" id="saveProductBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Save Product</button>
                </div>
            </form>
        </div>
    </div>

<script>
    let currentMode = 'create';
    let currentPage = 1;

    // 页面进入时自动触发一次 AJAX 加载表格
    document.addEventListener('DOMContentLoaded', function () {
        loadProducts(1);
    });

    /**
     * 核心函数：仅请求后端 getProducts API 并刷新 Table 区域
     */
    function loadProducts(page = 1) {
        currentPage = page;
        
        const loader = document.getElementById('tableLoader');
        if(loader) loader.classList.remove('hidden');

        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        
        const params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('sort', document.getElementById('sortSelect').value);

        fetch(`{{ route('getProducts') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const paginator = res.data; // Laravel Paginate 对象的 JSON 格式
                renderProductTable(paginator.data);
                renderPagination(paginator);
                document.getElementById('totalProductsCount').innerText = paginator.total;
            } else {
                alert(res.message || 'Failed to fetch products.');
            }
        })
        .catch(err => {
            console.error('Error fetching products:', err);
        })
        .finally(() => {
            if(loader) loader.classList.add('hidden');
        });
    }

    /**
     * 动态渲染 <tbody> 中的数据行
     */
    function renderProductTable(products) {
        const tbody = document.getElementById('productTableBody');
        const tableContainer = document.getElementById('tableContainer');
        const emptyState = document.getElementById('emptyState');

        tbody.innerHTML = '';

        if (!products || products.length === 0) {
            tableContainer.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        tableContainer.classList.remove('hidden');
        emptyState.classList.add('hidden');

        products.forEach(product => {
            const categoryName = product.category ? product.category.name : 'Uncategorized';
            const supplierName = product.supplier ? product.supplier.name : '-';
            const description = product.description ? `<p class="text-xs text-gray-500 font-normal line-clamp-1 mt-0.5">${escapeHtml(product.description)}</p>` : '';
            const price = parseFloat(product.price).toFixed(2);

            const row = `
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="py-4 px-4 font-medium text-gray-900 max-w-xs">
                        <div class="font-semibold text-gray-900 line-clamp-1">${escapeHtml(product.name)}</div>
                        ${description}
                    </td>
                    <td class="py-4 px-4 font-mono text-xs text-gray-600 whitespace-nowrap">
                        <span class="bg-gray-100 px-2 py-1 rounded">${escapeHtml(product.sku)}</span>
                    </td>
                    <td class="py-4 px-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                            ${escapeHtml(categoryName)}
                        </span>
                    </td>
                    <td class="py-4 px-4 whitespace-nowrap text-gray-600">
                        ${escapeHtml(supplierName)}
                    </td>
                    <td class="py-4 px-4 text-right font-bold text-gray-900 whitespace-nowrap">
                        $${price}
                    </td>
                    <td class="py-4 px-4 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center space-x-2">
                            <button 
                                type="button" 
                                onclick="openProductModal('edit', ${product.id})"
                                class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" 
                                title="Edit Product"
                            >
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <button 
                                type="button" 
                                onclick="deleteProduct('/products/${product.id}')"
                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" 
                                title="Delete Product"
                            >
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    /**
     * 动态渲染分页 HTML
     */
    function renderPagination(meta) {
        const container = document.getElementById('paginationContainer');
        container.innerHTML = '';

        if (!meta.last_page || meta.last_page <= 1) return;

        let html = '';

        // 上一页
        if (meta.current_page > 1) {
            html += `<button onclick="loadProducts(${meta.current_page - 1})" class="px-3 py-1 bg-white border border-gray-300 text-sm text-gray-700 rounded-lg hover:bg-gray-50">Previous</button>`;
        }

        // 页码
        for (let page = 1; page <= meta.last_page; page++) {
            if (page === meta.current_page) {
                html += `<span class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg font-medium">${page}</span>`;
            } else {
                html += `<button onclick="loadProducts(${page})" class="px-3 py-1 bg-white border border-gray-300 text-sm text-gray-700 rounded-lg hover:bg-gray-50">${page}</button>`;
            }
        }

        // 下一页
        if (meta.current_page < meta.last_page) {
            html += `<button onclick="loadProducts(${meta.current_page + 1})" class="px-3 py-1 bg-white border border-gray-300 text-sm text-gray-700 rounded-lg hover:bg-gray-50">Next</button>`;
        }

        container.innerHTML = html;
    }

    // 重置筛选
    function resetFilters() {
        document.getElementById('filterForm').reset();
        document.getElementById('sortSelect').value = 'latest';
        loadProducts(1);
    }

    // XSS 防护辅助函数
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Modal & Delete 相关操作方法
    function deleteProduct(url) {
        if (!confirm('Are you sure you want to delete this product?')) return;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadProducts(currentPage);
            } else {
                alert(data.message || 'Failed to delete product.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function openProductModal(mode, productId = null) {
        currentMode = mode;
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');
        document.getElementById('modalTitle').innerText = mode === 'create' ? 'Add Product' : 'Edit Product';

        form.reset();
        document.getElementById('productId').value = '';

        if (mode === 'create') {
            modal.classList.remove('hidden');
        } else {
            fetch(`/products/${productId}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const product = res.data;
                    document.getElementById('productId').value = product.id;
                    document.getElementById('modal_name').value = product.name || '';
                    document.getElementById('modal_sku').value = product.sku || '';
                    document.getElementById('modal_price').value = product.price || '';
                    document.getElementById('modal_category_id').value = product.category_id || '';
                    document.getElementById('modal_supplier_id').value = product.supplier_id || '';
                    document.getElementById('modal_description').value = product.description || '';
                    modal.classList.remove('hidden');
                }
            });
        }
    }

    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
    }

    function handleProductSubmit(event) {
        event.preventDefault();
        const productId = document.getElementById('productId').value;
        const url = (currentMode === 'edit' && productId) ? `/products/${productId}` : '/products';
        const method = (currentMode === 'edit' && productId) ? 'PUT' : 'POST';

        const formData = new FormData(event.target);
        const bodyData = Object.fromEntries(formData.entries());

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(bodyData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeProductModal();
                loadProducts(currentPage);
            } else {
                alert(data.message || 'Operation failed');
            }
        });
    }
</script>
</body>
</html>