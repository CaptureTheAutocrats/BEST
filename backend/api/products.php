<?php

    require_once 'db.php';
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $method = $_SERVER['REQUEST_METHOD'];
    $input  = json_decode(file_get_contents("php://input"), true);

    switch ($method) {

        case 'GET':
            handleProducts($conn, $input);
            break;

        default:
            echo json_encode(['message' => 'Invalid request method']);
            break;
    }


    function handleProducts($conn, $input){

        $page       = $_GET['page'] ;
        $limit      = $_GET['limit'];
        $offset     = ($page - 1) * $limit;

        // $token      = getBearerToken();
        // $user_id    = get_user_id($conn, $token);
        // if ( $user_id == null ){
        //     http_response_code(401);
        //     return;
        // }


        $get_products_sql = "SELECT * FROM Products LIMIT $limit OFFSET $offset";
        $query              = mysqli_query($conn, $get_products_sql);

         // Fetch all products and store them in an array
        $products = [];
        while ($product = mysqli_fetch_assoc($query)) {
            $products[] = $product;
        }

        echo json_encode($products);
        http_response_code(200);
    }

    function get_user_id($conn, $token){
        
        try{

            $check_session_sql  = "SELECT * FROM Sessions WHERE session_id = '$token'";
            $query              = mysqli_query($conn, $check_session_sql);

            if ( mysqli_num_rows($query) <= 0 ){
                return null;
            }

            $session                = mysqli_fetch_assoc($query);
            $expires_at             = $session['expires_at'];
            $today                  = date('Y-m-d H:i:s');
            if ( $today > $expires_at ){
                return null;
            }

            $user_id                = $session['user_id'];
            return $user_id;

        }catch (mysqli_sql_exception $e) {
            return null;
        }
    }

    function getBearerToken() {
        $headers = getallheaders();
        
        // Check for Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } 

        // Fallback to $_SERVER
        elseif (isset($_SERVER['Authorization'])) {
            $authHeader = $_SERVER['Authorization'];
        }
        else {
            return null;
        }
        
        // Extract the token
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
?>
