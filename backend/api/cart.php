<?php

require_once "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$method = $_SERVER["REQUEST_METHOD"];
$input  = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case "POST":
        addCartItem($conn, $input);
        break;
        
    case "GET":
        getCartItems($conn, $input);
        break;
        
    case "PATCH":
        patchCartItems($conn, $input);
        break;
        
    case "DELETE":
        deleteCartItem($conn, $input);
        break;
        
    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

function addCartItem($conn, $input)
{
    $token = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        return;
    }
    

    $check_sql  = "SELECT COUNT(*) AS total_items FROM CartItems WHERE user_id = '$user_id'";
    $result     = mysqli_query($conn, $check_sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_items = (int) $row['total_items'];
    
        if ($total_items >= 6) {
            http_response_code(201);
            echo json_encode(["message" => "Cart is full!"]);
            return;
        }
    }
    

    $product_id = $input["product_id"];
    $quantity   = $input["quantity"];
    
    
    $add_to_cart_sql = "INSERT INTO CartItems (user_id, product_id, quantity, updated_at)
                        VALUES ('$user_id', '$product_id', '$quantity', CURRENT_DATE)
                        ON DUPLICATE KEY UPDATE 
                            quantity = quantity + VALUES(quantity),
                            updated_at = CURRENT_DATE
                        ";
    if (mysqli_query($conn, $add_to_cart_sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Successfully added to cart"]);
        return;
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Internal Error!"]);
        return;
    }

}

function getCartItems($conn, $input)
{
    
    $token = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        return;
    }
    
    
    $get_cart_items_sql = "SELECT * FROM CartItems WHERE user_id = '$user_id'";
    $query              = mysqli_query($conn, $get_cart_items_sql);

     // Fetch all cart items and store them in an array
    $cart_items = [];
    while ($cart_item = mysqli_fetch_assoc($query)) {
        $cart_item['cart_item_id']  = (int) $cart_item['cart_item_id'];
        $cart_item['user_id']       = (int) $cart_item['user_id'];
        $cart_item['product_id']    = (int) $cart_item['product_id'];
        $cart_item['quantity']      = (int) $cart_item['quantity'];
        $cart_items[]               = $cart_item;
    }
    
    foreach ($cart_items as &$cart_item) {
        $product_id      = $cart_item['product_id'];
        $get_product_sql = "SELECT * FROM Products WHERE product_id = '$product_id'";
        $query           = mysqli_query($conn, $get_product_sql);
        if ($product = mysqli_fetch_assoc($query)) {
            $cart_item['product'] = $product;
        }
    }

    http_response_code(201);
    echo json_encode($cart_items);
}

function deleteCartItem($conn, $input)
{
    $token   = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized"]);
        return;
    }
    
    $product_id = $input["product_id"];

    $sql = "DELETE FROM CartItems WHERE user_id = '$user_id' AND product_id = '$product_id'";
    if (mysqli_query($conn, $sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Cart item deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete cart item"]);
    }
}

function patchCartItems($conn, $input)
{
    $token   = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized"]);
        return;
    }
    
    $product_id = $input["product_id"];
    $operation  = $input["operation"];
    
    // Get current quantity
    $select_sql = "SELECT quantity FROM CartItems WHERE user_id = '$user_id' AND product_id = '$product_id'";
    $result     = mysqli_query($conn, $select_sql);

    if (!$result || mysqli_num_rows($result) == 0) {
        http_response_code(404);
        echo json_encode(["message" => "Cart item not found"]);
        return;
    }
    
    $row = mysqli_fetch_assoc($result);
    $current_quantity = (int) $row['quantity'];
    
    if ( $operation == "+" ){
         $new_quantity = $current_quantity + 1;
    }
    else if ( $operation  == "-" ){
         $new_quantity = max(1, $current_quantity - 1);
    }
    else{
        http_response_code(400);
        echo json_encode(["message" => "Invalid operation"]);
    }
    
    $update_sql = "UPDATE CartItems 
                   SET quantity = '$new_quantity', updated_at = CURRENT_DATE 
                   WHERE user_id = '$user_id' AND product_id = '$product_id'";

    if (mysqli_query($conn, $update_sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Quantity Updated", "quantity" => (int) $new_quantity]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update quantity"]);
    }
}

function get_user_id($conn, $token)
{
    try {
        $check_session_sql = "SELECT * FROM Sessions WHERE session_id = '$token'";
        $query = mysqli_query($conn, $check_session_sql);

        if (mysqli_num_rows($query) <= 0) {
            return null;
        }

        $session = mysqli_fetch_assoc($query);
        $expires_at = (int) $session["expires_at"];
        $today = (int) time();
        if ($today > $expires_at) {
            return null;
        }

        $user_id = $session["user_id"];
        return $user_id;
    } catch (mysqli_sql_exception $e) {
        return null;
    }
}

function getBearerToken()
{
    $headers = getallheaders();

    // Check for Authorization header
    if (isset($headers["Authorization"])) {
        $authHeader = $headers["Authorization"];
    }

    // Fallback to $_SERVER
    elseif (isset($_SERVER["Authorization"])) {
        $authHeader = $_SERVER["Authorization"];
    } else {
        return null;
    }

    // Extract the token
    if (preg_match("/Bearer\s(\S+)/", $authHeader, $matches)) {
        return $matches[1];
    }

    return null;
}

?>