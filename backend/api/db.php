<?php

  $servername = "localhost";
  $dbusername = "root";
  $dbpassword = "";
  $db         = "best";

  $conn = mysqli_connect($servername, $dbusername, $dbpassword, $db);
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }
