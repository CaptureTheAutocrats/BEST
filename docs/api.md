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
- 200 - User registered successfully
- 409 - User already exists
- 500 - Internal error

**Response (200):**
```json
{
  "token": <string: access_token>,
  "tokenExpiresAt": <number: unix_timestamp>
}
```

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
  "token": <string: access_token>,
  "tokenExpiresAt": <number: unix_timestamp>
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

**Response (200): Product Object **
```json
[
    {
        "product_id": <int>,
        "user_id": <int>,
        "name": <string>,
        "description": <string>,
        "price": <int>,
        "image_path": <string: uploads/13/3/1744450314.png>,
        "stock": <int>,
        "product_condition": <string>,
        "created_at": <string: 2025-04-12 15:31:54>,
        "updated_at": <string: 2025-04-12 15:31:54">
    }
]
```

### Upload a Product

**Method:** POST  
**Path:** `/api/add-product.php`  

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
    "image": "iVBORw0KGgoAAAANSUhE...",
    "image_ext": ".png"
}
```

**Responses:**
- 201 - Successfully added the product
- 401 - Invalid session token
- 500 - Internal error

### Add a product to Cart

**Method:** POST  
**Path:** `/api/cart.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Request Body (JSON):**
```json
{
    "product_id": <int>,
    "quantity": <int>
}
```

**Responses:**
- 201 - Successfully added to cart | Cart is full!
- 401 - Invalid session token
- 500 - Internal error

### Get cart items

**Method:** GET  
**Path:** `/api/cart.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Responses:**
- 201 - Successfully retrieved cart Items
- 401 - Invalid session token

**Response (200):**
```json
[
    {
        "cart_item_id": <int>,
        "user_id": <int>,
        "product_id": <int>,
        "quantity": <int>,
        "updated_at": <string>,
        "product" : <Product Object>
    }
]
```


### Delete a product from Cart

**Method:** DELETE  
**Path:** `/api/cart.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Request Body (JSON):**
```json
{
    "product_id": <int>
}
```

**Responses:**
- 201 - Cart item deleted
- 401 - Invalid session token
- 500 - Internal error

### Change a product quantity of a cart

**Method:** PATCH  
**Path:** `/api/cart.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Request Body (JSON):**
```json
{
    "product_id": <int>,
    "operation" : <string>
}
```
**operation**
- (+ for adding quantity by 1)
- (- for reducing quantity by 1)

**Responses:**
- 201 - Quantity Updated 
- 400 - Invalid Operation
- 401 - Invalid session token
- 404 - Cart item not found
- 500 - Internal error

**Response (201):**
```json
[
    {
        "message": <string>,
        "quantity": <int>,
    }
]
```

### Place orders with cart items

**Method:** POST  
**Path:** `/api/orders.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Request Body (JSON):**
```json
{}
```

**Responses:**
- 201 - Orders created successfully
- 401 - Invalid session token
- 500 - Internal error
- 501 - Some orders failed to be created
  
**Response (201):**
```json
[
    {
        "message": <string>,
        "orders_created": <int>,
    }
]
```
**Response (501):**
```json
[
    {
        "message": <string>,
        "orders_created": <int>,
        "orders_failed": <int>
    }
]
```

### Fetch all orders

**Method:** GET  
**Path:** `/api/orders.php`  

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_session_token>
```

**Responses:**
- 201 - Successfully got the orders
- 401 - Invalid session token
- 500 - Internal error
  
**Response (201):**
```json
[
    {
        "order_id": <int>,
        "buyer_id": <int>,
        "seller_id": <int>,
        "product_id": <int>,
        "quantity": <int>,
        "total_amount": <int>,
        "status": <string>,
        "box_id": <string>,
        "pickup_code": <string>,
        "created_at": <string>,
        "updated_at": <string>,
        "product": <Product Object>
    }
]
```
