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
            if (mysqli_query($conn, $register_sql)) {
                http_response_code(201);
            }

        } catch (mysqli_sql_exception $e) {
            
            $error = $e->getMessage();
            if (strpos($error, 'Duplicate entry') !== false) {
                http_response_code(409); // Conflict, email already exists
            } else {
                http_response_code(400); // Bad request, some other error occurred
            }
        }
    }

?>