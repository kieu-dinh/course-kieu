# Exercise 9.2 - CRUD API

## Objective

Build a full CRUD API for managing products using RESTful principles.

## Duration

3-4 hours

## Task

Create a RESTful API with Create, Read, Update, Delete operations for products.

## Requirements

- [ ] Implement all CRUD operations:
  - GET `/api/products.php` - Get all products
  - GET `/api/products.php?id=1` - Get single product
  - POST `/api/products.php` - Create new product
  - PUT `/api/products.php?id=1` - Update product
  - DELETE `/api/products.php?id=1` - Delete product
- [ ] Use proper HTTP methods (GET, POST, PUT, DELETE)
- [ ] Store data in database
- [ ] Validate input data
- [ ] Return appropriate HTTP status codes
- [ ] Include error handling

## Database Schema

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Starter Files

Work in `products.php` and `db.php` - see starter code there.

## API Endpoints

**GET /products.php**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Laptop", "price": 999.99, "stock": 10}
  ]
}
```

**POST /products.php**
```json
Input: {"name": "Phone", "price": 499.99, "stock": 20}
Response: {"success": true, "id": 2, "message": "Product created"}
```

**PUT /products.php?id=1**
```json
Input: {"name": "Laptop Pro", "price": 1299.99}
Response: {"success": true, "message": "Product updated"}
```

**DELETE /products.php?id=1**
```json
{"success": true, "message": "Product deleted"}
```

## Checklist

- [ ] All CRUD operations work
- [ ] HTTP methods used correctly
- [ ] Database properly integrated
- [ ] Input validation implemented
- [ ] Error handling robust
- [ ] JSON responses consistent
- [ ] SQL injection prevented

## Tips

- Get HTTP method: `$_SERVER['REQUEST_METHOD']`
- Parse JSON input: `json_decode(file_get_contents('php://input'))`
- Use prepared statements for all queries
- Validate required fields before inserting
- Return 201 for successful creation
- Return 204 or 200 for successful deletion
