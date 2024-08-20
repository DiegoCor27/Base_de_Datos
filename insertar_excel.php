<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["archivo_excel"]["tmp_name"])) {
        $inputFileName = $_FILES["archivo_excel"]["tmp_name"];
        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        $conn = new mysqli('localhost', 'root', '270898Dc.', 'mi_base_de_datos');

        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        $highestRow = $sheet->getHighestRow();

        // Definir los EPS válidos
        $valid_eps = ['CAJACOPI', 'COOSALUD', 'NUEVA EPS', 'SANITAS', 'FAMILIAR DE COLOMBIA', 'MUTUAL SER'];

        // Leer el valor de EPS de la celda correspondiente
        $eps_from_file = $sheet->getCell('G2')->getValue();

        // Verificar si la EPS es válida
        if (in_array($eps_from_file, $valid_eps)) {
            // Eliminar registros existentes para la EPS
            $delete_sql = "DELETE FROM personas WHERE EPS = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("s", $eps_from_file);
            $delete_stmt->execute();
            $delete_stmt->close();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':H' . $row, NULL, TRUE, TRUE, TRUE);
                $data = $rowData[$row];

                $tipo_documento = isset($data['A']) ? $data['A'] : '';
                $documento = isset($data['B']) ? $data['B'] : '';
                $apellido1 = isset($data['C']) ? $data['C'] : '';
                $apellido2 = isset($data['D']) ? $data['D'] : '';
                $nombre1 = isset($data['E']) ? $data['E'] : '';
                $nombre2 = isset($data['F']) ? $data['F'] : '';
                $eps = isset($data['G']) ? $data['G'] : '';
                $servicios = isset($data['H']) ? $data['H'] : '';

                // Verificar si el usuario ya existe con el mismo número de documento y tipo de documento
                $check_sql = "SELECT SERVICIOS FROM personas WHERE DOCUMENTO = ? AND TIPO_DOCUMENTO = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ss", $documento, $tipo_documento);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    // Si el usuario existe, combinar los servicios
                    $check_stmt->bind_result($existing_servicios);
                    $check_stmt->fetch();
                    // Concatenar los servicios y limpiar los duplicados
                    $servicios_array = array_unique(array_merge(explode(', ', $existing_servicios), explode(', ', $servicios)));
                    $servicios = implode(', ', $servicios_array);

                    // Actualizar el registro existente
                    $update_sql = "UPDATE personas SET SERVICIOS = ? WHERE DOCUMENTO = ? AND TIPO_DOCUMENTO = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("sss", $servicios, $documento, $tipo_documento);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Si el usuario no existe, insertar nuevo registro
                    $sql = "INSERT INTO personas (TIPO_DOCUMENTO, DOCUMENTO, APELLIDO_1, APELLIDO_2, NOMBRE_1, NOMBRE_2, EPS, SERVICIOS) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssss", $tipo_documento, $documento, $apellido1, $apellido2, $nombre1, $nombre2, $eps, $servicios);
                    $stmt->execute();
                    $stmt->close();
                }

                $check_stmt->close();
            }

            echo "Datos de la EPS " . $eps_from_file . " insertados correctamente.";
        } else {
            echo "La EPS " . $eps_from_file . " no es válida.";
        }

        $conn->close();
    } else {
        echo "No se ha subido ningún archivo.";
    }
}
