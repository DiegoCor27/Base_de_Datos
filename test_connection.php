<?php
$conn = new mysqli('localhost', 'root', '270898Dc.', 'mi_base_de_datos');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa";
}

$conn->close();
?>
