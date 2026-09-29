<?php 
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Gadgets, devices, and electronic accessories.',
            ],
            [
                'name' => 'Clothing & Apparel',
                'description' => 'Men and women fashion, activewear, and shoes.',
            ],
            [
                'name' => 'Home & Kitchen',
                'description' => 'Kitchenware, small appliances, and home decor.',
            ],
            [
                'name' => 'Books & Stationeries',
                'description' => 'Fiction, educational materials, and office supplies.',
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Fitness equipment, outdoor gear, and apparel.',
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = Category::create($cat);
        }

        // 2. Create Suppliers
        $suppliers = [
            [
                'name' => 'TechGlobe Logistics',
                'email' => 'contact@techglobe.com',
                'phone' => '+1 (555) 019-2834',
                'address' => '100 Silicon Way, San Jose, CA',
            ],
            [
                'name' => 'Apex Apparel Traders',
                'email' => 'sales@apexapparel.com',
                'phone' => '+1 (555) 014-9982',
                'address' => '45 Fashion Ave, New York, NY',
            ],
            [
                'name' => 'HomeStyle Distributors',
                'email' => 'support@homestyle.com',
                'phone' => '+1 (555) 017-3310',
                'address' => '789 Industrial Pkwy, Chicago, IL',
            ],
            [
                'name' => 'Global Merchandise Ltd',
                'email' => 'info@globalmerch.com',
                'phone' => '+1 (555) 012-7741',
                'address' => '12 Commerce Blvd, Houston, TX',
            ],
        ];

        $createdSuppliers = [];
        foreach ($suppliers as $sup) {
            $createdSuppliers[] = Supplier::create($sup);
        }

        // 3. Create Products linked to Categories and Suppliers
        $products = [
            [
                'category_id' => $createdCategories[0]->id, // Electronics
                'supplier_id' => $createdSuppliers[0]->id,
                'name'        => 'Wireless Noise-Canceling Headphones',
                'sku'         => 'ELEC-ANC-001',
                'price'       => 199.99,
                'description' => 'Over-ear Bluetooth headphones with active noise cancellation and 30-hour battery life.',
            ],
            [
                'category_id' => $createdCategories[0]->id, // Electronics
                'supplier_id' => $createdSuppliers[0]->id,
                'name'        => 'Ergonomic Wireless Mouse',
                'sku'         => 'ELEC-MSE-002',
                'price'       => 29.50,
                'description' => '2.4GHz optical mouse with customizable side buttons and silent click feature.',
            ],
            [
                'category_id' => $createdCategories[0]->id, // Electronics
                'supplier_id' => $createdSuppliers[3]->id,
                'name'        => 'Mechanical Gaming Keyboard',
                'sku'         => 'ELEC-KBD-003',
                'price'       => 89.00,
                'description' => 'RGB backlit mechanical keyboard with tactile blue switches.',
            ],
            [
                'category_id' => $createdCategories[1]->id, // Clothing
                'supplier_id' => $createdSuppliers[1]->id,
                'name'        => 'Classic Cotton Crewneck T-Shirt',
                'sku'         => 'CLOT-TSH-001',
                'price'       => 15.00,
                'description' => '100% organic cotton breathable T-shirt, available in multiple colors.',
            ],
            [
                'category_id' => $createdCategories[1]->id, // Clothing
                'supplier_id' => $createdSuppliers[1]->id,
                'name'        => 'Slim-Fit Denim Jeans',
                'sku'         => 'CLOT-JNS-002',
                'price'       => 49.99,
                'description' => 'Durable stretch denim jeans with five-pocket styling.',
            ],
            [
                'category_id' => $createdCategories[2]->id, // Home & Kitchen
                'supplier_id' => $createdSuppliers[2]->id,
                'name'        => 'Stainless Steel Electric Kettle (1.7L)',
                'sku'         => 'HOME-KTL-001',
                'price'       => 34.95,
                'description' => 'Fast-boiling cordless electric kettle with auto shut-off protection.',
            ],
            [
                'category_id' => $createdCategories[2]->id, // Home & Kitchen
                'supplier_id' => $createdSuppliers[2]->id,
                'name'        => 'Non-Stick Ceramic Frying Pan (10-inch)',
                'sku'         => 'HOME-PAN-002',
                'price'       => 24.50,
                'description' => 'Non-toxic ceramic coated skillet with heat-resistant handle.',
            ],
            [
                'category_id' => $createdCategories[3]->id, // Books
                'supplier_id' => $createdSuppliers[3]->id,
                'name'        => 'Hardcover Leather Journal',
                'sku'         => 'BOOK-JRN-001',
                'price'       => 18.75,
                'description' => '200 pages lined notebook with premium thick paper and ribbon bookmark.',
            ],
            [
                'category_id' => $createdCategories[4]->id, // Sports
                'supplier_id' => $createdSuppliers[3]->id,
                'name'        => 'Non-Slip Yoga Mat (6mm)',
                'sku'         => 'SPRT-YGA-001',
                'price'       => 22.00,
                'description' => 'Eco-friendly TPE foam yoga mat with alignment lines and carrying strap.',
            ],
            [
                'category_id' => $createdCategories[4]->id, // Sports
                'supplier_id' => $createdSuppliers[0]->id,
                'name'        => 'Insulated Stainless Steel Water Bottle (32oz)',
                'sku'         => 'SPRT-BTL-002',
                'price'       => 16.99,
                'description' => 'Double-wall vacuum insulated bottle keeping drinks cold for up to 24 hours.',
            ],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}