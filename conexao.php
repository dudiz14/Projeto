<?php
$host = "sql305.infinityfree.com";
$user = "if0_40105534";
$pass = "m4r14dwd4";
$dbname = "if0_40105534_infoweba";

// Conexão
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
