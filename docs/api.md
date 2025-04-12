# API Documentation

### Register a new user
 
    Method:  POST
    URL:     /api/register.php

    Headers:
    - Content-Type: application/json

    Request Body (JSON):
    {
        "name": "string, required",
        "email": "string, required, unique",
        "password": "string, required, min 8 chars"
        "student_id" "string", required, unique
    }
    
    Responses:
        201 - User registered successfully
        409 - User already exists
        500 - Internal error



### Login user

    Method:  POST
    URL:     /api/login.php

    Headers:
    - Content-Type: application/json

    Request Body (JSON):
    {
        "email": "string, required, unique",
        "password": "string, required, min 8 chars"
    }
    
    Responses:
        200 - User logged in successfully
        401 - Invalid email/password
        500 - Internal error

    Responses(200):
    {
        "token" : <your_access_token>
    }






### Fetch All Products:

    Method:  GET
    URL:     /api/products.php?page=<page>&limit=<limit>

    Headers:
    - Content-Type: application/json

    Responses:
        200 - Successfully got the products
        404 - No products available
        500 - Internal error

    Example Responses(200):
    [
        {
            "product_id": "2",
            "user_id": "13",
            "name": "Android",
            "description": "Google",
            "price": "50.00",
            "image_id": "https://image.made-in-china.com/202f0j00BdGimwkMGRbC/A18-5-Inch-Android-Smartphone-HD-Face-Global-Version-Mobile-Phone.webp",
            "stock": "300",
            "product_condition": "New",
            "created_at": "2025-04-12 04:08:14",
            "updated_at": "2025-04-12 04:08:14"
        }
    ]

        
