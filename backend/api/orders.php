<?php

require_once "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$method = $_SERVER["REQUEST_METHOD"];
$input  = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case "POST":
        createOrders($conn, $input);
        break;
        
    case "GET":
        getOrders($conn, $input);
        break;
        
    case "DELETE":
        //deleteOrderItem($conn, $input);
        break;
        
    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

function createOrders($conn, $input)
{
    $token = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        return;
    }
    
    $carts = getCartItems($conn, $input);
    if (is_string($carts)) {
        $carts = json_decode($carts, true); // decode to associative array
    }
    
    
    $successCount = 0;
    $failureCount = 0;
    
    foreach ( $carts as $cart ){
        
        $cart_item_id = $cart['cart_item_id'];
        $buyer_id     = $cart['user_id'];
        $seller_id    = $cart['product']['user_id'];
        $product_id   = $cart['product']['product_id'];
        $price        = (int) $cart['product']['price'];
        $quantity     = (int) $cart['quantity'];
        $total_amount = $price * $quantity;


        $result = createSingleOrder($conn, $buyer_id, $seller_id, $product_id, $quantity, $total_amount);
        if ($result) {
            $successCount++;
            deleteCartItem($conn, $cart_item_id);
        } else {
            $failureCount++;
        }
    }
    
     if ($failureCount === 0) {
        http_response_code(201);
        echo json_encode(["message" => "Orders created successfully.", "orders_created" => (int) $successCount]);
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Some orders failed to be created.",
            "orders_created" => $successCount,
            "orders_failed" => $failureCount
        ]);
    }
}

function createSingleOrder($conn, $buyer_id, $seller_id, $product_id, $quantity, $total_amount )
{
    
    $status      = 'Pending';
    $box_id      = 0;
    $pickup_code = 0;
    
    
    $sql    =   "INSERT INTO Orders (buyer_id, seller_id, product_id, quantity, total_amount, status, box_id, pickup_code) 
                VALUES ('$buyer_id', '$seller_id', '$product_id', '$quantity', '$total_amount', '$status', '$box_id', '$pickup_code')";
    $query  = mysqli_query($conn, $sql);
    if (!$query) {
        return false;
    }
    return true;
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
        $query = mysqli_query($conn, $get_product_sql);
        
        if ($product = mysqli_fetch_assoc($query)) {
            $cart_item['product'] = $product;
        }
    }
    

    return json_encode($cart_items);
}

function deleteCartItem($conn, $cart_item_id)
{

    $sql    = "DELETE FROM CartItems WHERE cart_item_id = '$cart_item_id' ";
    $query  = mysqli_query($conn, $sql);
    if (!$query) {
        return false;
    }
    return true;
}

function getOrders($conn, $input){

    
    $token = getBearerToken();
    $user_id = get_user_id($conn, $token);
    if ($user_id == null) {
        http_response_code(401);
        return;
    }
    
    $headers = getallheaders();
    if (isset($headers['isasseller']) && !empty($headers['isasseller'])) {
        $get_orders_sql = "SELECT * FROM Orders WHERE seller_id = '$user_id' ";
    }
    else{
        $get_orders_sql = "SELECT * FROM Orders WHERE buyer_id = '$user_id' ";
    }

    $query  = mysqli_query($conn, $get_orders_sql);
    $orders = [];
    while ($order = mysqli_fetch_assoc($query)) {
        $orders[] = $order;
    }
    
    foreach ($orders as &$order ) {
        
        $product_id      = $order['product_id'];
        $get_product_sql = "SELECT * FROM Products WHERE product_id = '$product_id'";
        $query           = mysqli_query($conn, $get_product_sql);
        if ($product = mysqli_fetch_assoc($query)) {
            
            $order['order_id']      = (int) $order['order_id'];
            $order['buyer_id']      = (int) $order['buyer_id'];
            $order['seller_id']     = (int) $order['seller_id'];
            $order['product_id']    = (int) $order['product_id'];
            
            $order['quantity']      = (int) $order['quantity'];
            $order['total_amount']  = (int) $order['total_amount'];
            $order['seller_id']     = (int) $order['seller_id'];
            $order['product_id']    = (int) $order['product_id'];
            
            $product['product_id']    = (int) $product['product_id'];
            $product['user_id']       = (int) $product['user_id'];
            $product['product_id']    = (int) $product['product_id'];
            $product['price']         = (int) $product['price'];
            $product['stock']         = (int) $product['stock'];
                
            $order['product'] = $product;
        }
        


    }
    
    http_response_code(200);
    echo json_encode($orders);
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