<?php

    require_once 'db.php';
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $method = $_SERVER['REQUEST_METHOD'];
    $input  = json_decode(file_get_contents("php://input"), true);

    switch ($method) {

        case 'POST':
            handleLogin($conn,$input);
            break;

        default:
            echo json_encode(['message' => 'Invalid request method']);
            break;
    }


        
    function handleLogin($conn,$input) {

        $email          = $input['email'];
        $password       = $input['password'];
        $password_hash  = password_hash($password, PASSWORD_DEFAULT);

        try{

            $check_user_sql = "SELECT * FROM Users WHERE email = '$email'";
            $query = mysqli_query($conn, $check_user_sql);

            if ( mysqli_num_rows($query) <= 0 ){
                http_response_code(401);
                return;
            }

            $user                   = mysqli_fetch_assoc($query);
            $user_id                = $user['user_id'];
            $stored_password_hash   = $user['password_hash'];

            // Verify the password using password_verify()
            if (!password_verify($password, $stored_password_hash)) {
                http_response_code(401);
                return;
            }

            $token      = generateBearerToken();
            $expires_at = strtotime('+7 days');

            $insert_session_token_sql = "INSERT INTO Sessions (session_id, user_id, expires_at) VALUES ('$token', '$user_id', '$expires_at')";
            if ( !mysqli_query($conn, $insert_session_token_sql) ) {
                http_response_code(500);
                return;
            }

            http_response_code(200);
            echo json_encode(['message'=>'Success', 'token' => $token , 'tokenExpiresAt' => (int) $expires_at ]);
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
        }
    }
?>
