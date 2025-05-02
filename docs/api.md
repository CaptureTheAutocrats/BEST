## API Documentation

### Register a New User

**Method:** POST  
**Path:** `/api/register.php`  

**Headers:**
```
Content-Type: application/json
```

**Request Body (JSON):**
```json
{
    "name": "string, required",
    "email": "string, required, unique",
    "password": "string, required, min 8 chars",
    "student_id": "string, required, unique"
}
```

**Responses:**
- 201 - User registered successfully
- 409 - User already exists
- 500 - Internal error

---

### Login User

**Method:** POST  
**Path:** `/api/login.php`  

**Headers:**
```
Content-Type: application/json
```

**Request Body (JSON):**
```json
{
    "email": "string, required, unique",
    "password": "string, required, min 8 chars"
}
```

**Responses:**
- 200 - User logged in successfully
- 401 - Invalid email/password
- 500 - Internal error

**Response (200):**
```json
{
    "token": "<your_access_token>"
}
```

---

### Fetch All Products

**Method:** GET  
**Path:** `/api/products.php?page=<page>&limit=<limit>`  

**Headers:**
```
Content-Type: application/json
```

**Responses:**
- 200 - Successfully got the products
- 500 - Internal error

**Response (200):**
```json
[
    {
        "product_id": "3",
        "user_id": "13",
        "name": "Awesome Product",
        "description": "This is a great product.",
        "price": "49.99",
        "image_path": "uploads/13/3/1744450314.png",
        "stock": "10",
        "product_condition": "New",
        "created_at": "2025-04-12 15:31:54",
        "updated_at": "2025-04-12 15:31:54"
    }
]
```

### Upload a Product

**Method:** POST  
**Path:** `/api/add-products.php?`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Request Body (JSON):**
```json
{
    "name": "Awesome Product",
    "description": "This is a great product.",
    "price": 49.99,
    "product_condition": "New",
    "stock": 10,
    "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhE..."
}
```

**Responses:**
- 201 - Successfully added the product
- 401 - Invalid session token
- 500 - Internal error

