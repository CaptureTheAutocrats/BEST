<?php
  $db_hostname = "localhost";
  $db_username = "catchmei_best";
  $db_password = "catchmei_best";
  $db_name     = "catchmei_best";

  $conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

  if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
  }
  
  function generateBearerToken($length = 32) {
      // Check if random_bytes is available (PHP 7+)
      if (function_exists('random_bytes')) {
        $bytes = random_bytes(ceil($length / 2));
      } 
      
      // Fallback to openssl if random_bytes not available
      elseif (function_exists('openssl_random_pseudo_bytes')) {
          $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
      } 
      
      // Final fallback (less secure)
      else {
          throw new Exception('No cryptographically secure random function available');
      }
      
      return substr(bin2hex($bytes), 0, $length);
    }
?>
