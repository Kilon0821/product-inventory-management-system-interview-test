# Product Management System

An efficient Laravel-based Product Management System supporting category and supplier relational management, complete CRUD operations, and advanced filtering capabilities.

---

## Features

* **Product Management**: Create, read, edit, and delete products (supports Soft Deletes).
* **Filter & Search**: Quickly filter and search products by category, supplier, SKU or Price Range.
* **Database Relationships**:
  * **Category Association**: Each product belongs to one category (1-to-Many). Deleting a category cascades and deletes all associated products (`Cascade Delete`).
  * **Supplier Association**: Each product belongs to one supplier (1-to-Many). Deleting a supplier sets the product's supplier ID to null (`Null On Delete`).

---

## Test Credentials

Use the following credentials to log in for local development and testing:

| Email | Password | Role |
| :--- | :--- | :--- |
| `test@example.com` | `test` | Admin / Test Account |

---

## Database Schema

The system consists of three core tables: `categories`, `suppliers`, and `products`.

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
| `price` | `decimal(10,2)` | Price | Required |
| `description` | `text` | Description | Nullable |
| `deleted_at` | `timestamp` | Soft Delete Flag | Soft Deletes |
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