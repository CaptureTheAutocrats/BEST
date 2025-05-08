<?php

    require_once 'db.php';
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $method = $_SERVER['REQUEST_METHOD'];
    $input  = json_decode(file_get_contents("php://input"), true);

    switch ($method) {
        
        case 'POST':
            handleProductUpload($conn, $input);
            break;

        default:
            echo json_encode(['message' => 'Invalid request method']);
            break;
    }


    function handleProductUpload($conn, $input){

        $token              = getBearerToken();
        $user_id            = get_user_id($conn, $token);
        if ( $user_id == null ){
            http_response_code(401);
            return;
        }

        $name               = $input['name'];
        $description        = $input['description'];
        $price              = $input['price'];
        $product_condition  = $input['product_condition'];
        $stock              = $input['stock'];

        $product_id         = 1;
        $last_id_sql        = "SELECT MAX(product_id) AS last_id FROM Products";
        $query              = mysqli_query($conn, $last_id_sql);
        if ( mysqli_num_rows($query) > 0 ){
            $products       = mysqli_fetch_assoc($query);
            $last_id        = $products['last_id'];
            $product_id     = $last_id + 1;
        }


        $image_base64       = str_replace('data:image/png;base64,', '', $input['image']);
        $image_data         = base64_decode($image_base64);
        $image_path         = '../uploads/'.$user_id.'/'.$product_id.'/'.time().'.png';
        $directory          = dirname($image_path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                http_response_code(500);
                return;
            }
        }

        $image_path_in_db   = str_replace("../", '', $image_path);
        $add_product_sql    = "INSERT INTO Products (product_id, user_id, name, description, price, image_path, stock, product_condition)  
                            VALUES ( '$product_id', '$user_id', '$name', '$description', '$price', '$image_path_in_db', '$stock', '$product_condition')";
        if (mysqli_query($conn, $add_product_sql)) {   
            if (!file_put_contents($image_path, $image_data)) {
                http_response_code(500);
                return;
            }
            http_response_code(201);
            return;
        }
        else{
            http_response_code(500);
            return;
        }
    }

    function get_user_id($conn, $token){
        
        try{

            $check_session_sql  = "SELECT * FROM Sessions WHERE session_id = '$token'";
            $query              = mysqli_query($conn, $check_session_sql);

            if ( mysqli_num_rows($query) <= 0 ){
                return null;
            }

            $session                = mysqli_fetch_assoc($query);
            $expires_at             = (int) $session['expires_at'];
            $today                  = (int) time();
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
