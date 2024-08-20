<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 300px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h2>Buscar Usuario por Documento</h2>

    <form method="POST" action="">
        <input type="text" name="documento" placeholder="Ingrese número de documento" required>
        <input type="submit" value="Buscar">
    </form>

    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documento = $_POST['documento'];

    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '270898Dc.', 'mi_base_de_datos');

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar y ejecutar la consulta
    $sql = "SELECT * FROM personas WHERE DOCUMENTO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $documento);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mostrar los resultados
    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Tipo Documento</th>
                    <th>Documento</th>
                    <th>Nombre Completo</th>
                    <th>EPS</th>
                    <th>Servicios</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
                $nombreCompleto = $row["NOMBRE_1"] . " " . $row["NOMBRE_2"] . " " . $row["APELLIDO_1"] . " " . $row["APELLIDO_2"];
                echo "<tr>
                        <td>" . htmlspecialchars($row["TIPO_DOCUMENTO"]) . "</td>
                        <td>" . htmlspecialchars($row["DOCUMENTO"]) . "</td>
                        <td>" . htmlspecialchars($nombreCompleto) . "</td>
                        <td>" . htmlspecialchars($row["EPS"]) . "</td>
                        <td>" . htmlspecialchars($row["SERVICIOS"]) . "</td>
                    </tr>";
    
        }
        echo "</table>";
    } else {
        echo "No se encontraron resultados para el documento: " . htmlspecialchars($documento);
    }

    $stmt->close();
    $conn->close();
}
?>