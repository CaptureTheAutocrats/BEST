<?php
    require_once 'db.php';
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $method = $_SERVER['REQUEST_METHOD'];
    $input  = json_decode(file_get_contents("php://input"), true);

    switch ($method) {
        case 'POST':
            handleRegistration($conn, $input);
            break;

        default:
            echo json_encode(['message' => 'Invalid request method']);
            break;
    }

    function handleRegistration($conn, $input){
        $name           = $input['name'];
        $email          = $input['email'];
        $password       = $input['password'];
        $password_hash  = password_hash($password, PASSWORD_DEFAULT);
        $student_id     = $input['student_id'];

        try {
            $register_sql = "INSERT INTO Users (name, email, password_hash, student_id) VALUES ('$name', '$email', '$password_hash', '$student_id')";
            $query = mysqli_query($conn, $register_sql); // <-- fixed semicolon

            if (!$query) {
                http_response_code(500);
                echo json_encode(['message' => 'User registration failed']);
                return;
            }

            $user_id = mysqli_insert_id($conn);

            $token      = generateBearerToken();
            $expires_at = strtotime('+7 days');

            $insert_session_token_sql = "INSERT INTO Sessions (session_id, user_id, expires_at) VALUES ('$token', '$user_id', '$expires_at')";
            if ( !mysqli_query($conn, $insert_session_token_sql) ) {
                http_response_code(500);
                echo json_encode(['message' => 'Session creation failed']);
                return;
            }

            http_response_code(200);
            echo json_encode(['message'=>'Success', 'token' => $token, 'tokenExpiresAt' => (int) $expires_at ]);
        } catch (mysqli_sql_exception $e) {
            $error = $e->getMessage();
            if (strpos($error, 'Duplicate entry') !== false) {
                http_response_code(409);
            } else {
                http_response_code(400);
            }
        }
    }
?>
