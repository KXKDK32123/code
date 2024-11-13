<?php
session_start(); // Inicia la sesión PHP para manejar variables de sesión
include '../conexion.php'; // Incluye el archivo de conexión a la base de datos
require '../vendor/autoload.php'; // Carga las dependencias (PhpSpreadsheet)
use PhpOffice\PhpSpreadsheet\IOFactory; // Para manejar archivos Excel

// Variables iniciales
$error = '';              // Almacena mensajes de error
$successMessage = '';     // Almacena mensajes de éxito
$no_trabajador = '';      // Número de trabajador
$nombre = '';             // Nombre del empleado
$turno = '';             // Turno del empleado
$fileUploaded = false;    // Control de subida de archivos

// Manejo de agregar empleado individual
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitEmpleado'])) {
    $no_trabajador = floor(floatval($_POST['no_trabajador'])); // Convierte a entero eliminando decimales
    $nombre = $_POST['nombre'];
    $turno = $_POST['turno'];

    // Validaciones de entrada
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
        $error = "El nombre solo debe contener letras.";
    } elseif (!is_numeric($no_trabajador) || $no_trabajador <= 0) {
        $error = "El número de trabajador debe ser un número positivo.";
    } else {

        // Verifica si el número de trabajador ya existe
        $consulta = "SELECT COUNT(*) AS count FROM empleado WHERE no_trabajador = ?";
        $stmt = $conexion->prepare($consulta);
        $stmt->bind_param("i", $no_trabajador);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila['count'] > 0) {
            $error = "El número de trabajador ya existe. Intente con otro.";
        } else {
            // Inserta el nuevo empleado
            $no_credencial = '000' . $no_trabajador . 'B';
            $consulta = "INSERT INTO empleado (no_trabajador, nombre, turno, no_credencial) VALUES (?, ?, ?, ?)";
            $stmt = $conexion->prepare($consulta);
            $stmt->bind_param("isss", $no_trabajador, $nombre, $turno, $no_credencial);
            $stmt->execute();
            $successMessage = "Empleado agregado correctamente.";
            // Limpia los campos después de la inserción
            $no_trabajador = '';
            $nombre = '';
            $turno = '';
        }
    }
}

// Manejo de carga de archivo Excel
if (isset($_FILES['excelFile'])) {
    if ($_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
        try {
            $inputFileName = $_FILES['excelFile']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION));

            // Configuración para diferentes tipos de archivo
            switch ($extension) {
                case 'csv':
                    $reader = IOFactory::createReader('Csv');
                    $reader->setDelimiter(',');
                    $reader->setInputEncoding('UTF-8');
                    break;
                case 'xlsx':
                    $reader = IOFactory::createReader('Xlsx');
                    break;
                case 'xls':
                    $reader = IOFactory::createReader('Xls');
                    break;
                case 'ods':
                    $reader = IOFactory::createReader('Ods');
                    break;
                default:
                    // Intenta detectar automáticamente el tipo
                    try {
                        $reader = IOFactory::createReaderForFile($inputFileName);
                    } catch (Exception $e) {
                        throw new Exception('Formato de archivo no soportado');
                    }
            }

            // Configuraciones generales del reader
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            // Carga el archivo
            $spreadsheet = $reader->load($inputFileName);
            
            // Obtiene la primera hoja
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Determina el rango real de datos
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            
            // Procesa cada fila del Excel
            for ($row = 2; $row <= $highestRow; $row++) { // Empieza en 2 para saltar encabezados
                $rowData = $worksheet->rangeToArray(
                    'A' . $row . ':' . $highestColumn . $row,
                    null,
                    true,
                    true
                )[0];

                // Limpia y valida los datos
                $no_trabajador = floor(floatval(trim($rowData[0]))); // Limpia y convierte a entero
                $nombre = trim($rowData[1] ?? '');
                $turno = trim($rowData[2] ?? '');

                // Salta filas vacías
                if (empty($no_trabajador) || empty($nombre) || empty($turno)) {
                    continue;
                }

                // Normaliza el turno
                $turno = ucfirst(strtolower($turno));
                if (!in_array($turno, ['Matutino', 'Vespertino'])) {
                    $turno = 'Matutino'; // Valor por defecto
                }

                // Validaciones para cada fila
                if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre)) {
                    throw new Exception("El nombre '$nombre' solo debe contener letras.");
                } elseif (!is_numeric($no_trabajador) || $no_trabajador <= 0) {
                    throw new Exception("El número de trabajador '$no_trabajador' debe ser un número positivo.");
                }

                // Verifica duplicados
                $consulta = "SELECT COUNT(*) AS count FROM empleado WHERE no_trabajador = ?";
                $stmt = $conexion->prepare($consulta);
                $stmt->bind_param("i", $no_trabajador);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $fila = $resultado->fetch_assoc();

                if ($fila['count'] > 0) {
                    throw new Exception("El número de trabajador '$no_trabajador' ya existe.");
                }

                // Inserta el empleado
                $no_credencial = '000' . $no_trabajador . 'B';
                $consulta = "INSERT INTO empleado (no_trabajador, nombre, turno, no_credencial) VALUES (?, ?, ?, ?)";
                $stmt = $conexion->prepare($consulta);
                $stmt->bind_param("isss", $no_trabajador, $nombre, $turno, $no_credencial);
                $stmt->execute();
            }

            $fileUploaded = true;
            $successMessage = "El archivo se ha procesado correctamente.";
            
        } catch (Exception $e) {
            $error = "Error al procesar el archivo: " . $e->getMessage();
        }
    } elseif ($_FILES['excelFile']['error'] != UPLOAD_ERR_NO_FILE) {
        $error = "Hubo un problema al subir el archivo. Por favor, inténtelo de nuevo.";
    }
}

// Obtiene la lista de empleados activos
$consulta = "SELECT * FROM empleado WHERE eliminado = 0";
$resultado = $conexion->query($consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Empleados</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/forms.css">
    <link rel="stylesheet" href="../CSS/tables.css">
    <link rel="stylesheet" href="../CSS/footer.css">
</head>
<body>
    <main>
        <div class="containerphp">
            <h1>Administrar Empleados</h1>
            <!-- Formulario para agregar empleado individual -->
            <form action="" method="POST" onsubmit="return validarFormulario()">
                <h2>Agregar Empleado</h2>
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($successMessage): ?>
                    <div class="success"><?php echo $successMessage; ?></div>
                <?php endif; ?>
                <label for="no_trabajador">Número de Trabajador:</label>
                <input type="number" name="no_trabajador" min="1" step="1" value="<?php echo htmlspecialchars($no_trabajador); ?>" required>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                <label for="turno">Turno:</label>
                <select name="turno" required>
                    <option value="Matutino" <?php echo $turno === 'Matutino' ? 'selected' : ''; ?>>Matutino</option>
                    <option value="Vespertino" <?php echo $turno === 'Vespertino' ? 'selected' : ''; ?>>Vespertino</option>
                </select>
                <input type="submit" name="submitEmpleado" value="Agregar Empleado" class="btn-save">
            </form>

            <!-- Formulario para subir archivo Excel -->
            <h2>Subir Archivo Excel</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="excelFile">Archivo Excel:</label>
                <label class="btn-excel">
                    <input type="file" name="excelFile" accept=".xlsx, .xls, .csv, .ods" required style="display: none;">
                    <i class="fas fa-file-excel"></i> Seleccionar archivo Excel
                </label>
                <input type="submit" value="Cargar Excel" class="btn-save">
            </form>

            <!-- Lista de empleados -->
            <h3>Lista de Empleados</h3>
            <div class="scroll">
                <form method="POST" action="agregar.php">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Trabajador</th>
                                <th>Nombre</th>
                                <th>Turno</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $fila['no_trabajador']; ?></td>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['turno']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="vaciar-bd-container">
                    <a href="../index.html" class="button-back">Regresar</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Mismo footer que en los otros archivos -->
       <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Sobre Nosotros</h4>
                <p>Sistema de gestión de comedor empresarial, diseñado para optimizar y controlar el servicio de alimentación.</p>
            </div>
            <div class="footer-section">
                <h4>Enlaces Rápidos</h4>
                <p><a href="../index.html">Inicio</a></p>
                <p><a href="registros.php">Registros</a></p>
                <p><a href="eliminados.php">Eliminidos</a></p>
                <p><a href="check_in_simple.php">Check in</a></p>
            </div>
            <div class="footer-section">
                <h4>Contacto</h4>
                <p>Email: soporte@comedor.com</p>
                <p>Tel: (123) 456-7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Sistema de Comedor. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
  