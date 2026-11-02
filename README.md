# Product Management System

An efficient Laravel-based Product Management System supporting category and supplier relational management, complete CRUD operations, and advanced filtering capabilities.

---

## Features

* **Product Management**: Create, read, edit, and delete products (supports Soft Deletes).
* **Filter & Search**: Quickly filter and search products by category, supplier, SKU or Price Range.
* **Database Relationships**:
  * **Category Association**: Each product belongs to one category (1-to-Many). Deleting a category cascades and deletes all associated products (`Cascade Delete`).
  * **Supplier Association**: Each product belongs to one supplier (1-to-Many). Deleting a supplier sets the product's supplier ID to null (`Null On Delete`).
* **API Endpoints**: Access RESTful API endpoints for product management, including CRUD operations and filtering capabilities.
* **Inbound Management**: Manage product inbound activities, including recording stock transactions.
* **Stock Adjustment**: Track and adjust product stock levels with detailed transaction logs. Press the product List "Quantity" to update stock levels

---

## Test Credentials

Use the following credentials to log in for local development and testing:

| Email | Password | Role |
| :--- | :--- | :--- |
| `test@example.com` | `test` | Admin / Test Account |

---

## Database Schema

### 1. Categories (`categories`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key | Auto Increment |
| `name` | `varchar` | Category Name | Required |
| `description` | `text` | Description | Nullable |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

### 2. Suppliers (`suppliers`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key | Auto Increment |
| `name` | `varchar` | Supplier Name | Required |
| `email` | `varchar` | Email Address | Unique |
| `phone` | `varchar` | Phone Number | Nullable |
| `address` | `text` | Full Address | Nullable |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

### 3. Products (`products`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key | Auto Increment |
| `category_id` | `bigint` | Foreign Key (Category) | Foreign Key (`Cascade Delete`) |
| `supplier_id` | `bigint` | Foreign Key (Supplier) | Foreign Key (`Null On Delete`, Nullable) |
| `name` | `varchar` | Product Name | Required |
| `sku` | `varchar` | Stock Keeping Unit | Unique |
| `stock_quantity` | `integer` | Stock Quantity | Default 0 |
| `price` | `decimal(10,2)` | Price | Required |
| `description` | `text` | Description | Nullable |
| `deleted_at` | `timestamp` | Soft Delete Flag | Soft Deletes |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

### 4. Stock Transactions (`stock_transactions`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key | Auto Increment |
| `product_id` | `integer` | Foreign Key (Products) | Required |
| `quantity` | `integer` | Transaction Quantity | Required |
| `type` | `string` | Transaction Type (e.g., "in", "out") | Nullable |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

### 5. Orders (`orders`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key | Auto Increment |
| `user_id` | `integer` | Foreign Key (Users) | Required |
| `product_id` | `integer` | Foreign Key (Products) | Required |
| `quantity` | `integer` | Order Quantity | Required |
| `price` | `decimal(10,2)` | Price | Required |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

### 6. Order Products (`orders_products`)
| Field | Type | Description | Constraints / Default |
| :--- | :--- | :--- | :--- |
| `order_id` | `integer` | Foreign Key (Orders) | Required |
| `product_id` | `integer` | Foreign Key (Products) | Required |
| `quantity` | `integer` | Order Product Quantity | Required |
| `price` | `decimal(10,2)` | Price | Required |
| `created_at` / `updated_at` | `timestamp` | Timestamps | Nullable |

---

## Setup & Installation

### 1. Requirements
* PHP >= 8.1
* Composer
* MySQL / PostgreSQL
* Node.js & NPM

### 2. Local Installation Steps

1. **Clone the repository and enter directory:**
   ```bash
   git clone <repository-url>
   cd <project-folder>

2. **Install dependencies:**
   composer install
   setup .env file with your database credentials
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   php artisan serve