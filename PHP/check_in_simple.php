<?php
include '../conexion.php';

// Establecer zona horaria
date_default_timezone_set('America/Tijuana');

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['numero_empleado'])) {
    $no_trabajador = $_POST['numero_empleado'];
    
    // Verificar si el empleado existe
    $stmt = $conexion->prepare("SELECT id, nombre, turno FROM empleado WHERE no_trabajador = ? AND eliminado = 0");
    $stmt->bind_param("i", $no_trabajador);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $empleado = $resultado->fetch_assoc();
        $empleado_id = $empleado['id'];
        
        // Verificar último check-in
        $stmt = $conexion->prepare("SELECT fecha, hora FROM asistencias WHERE empleado_id = ? ORDER BY fecha DESC, hora DESC LIMIT 1");
        $stmt->bind_param("i", $empleado_id);
        $stmt->execute();
        $ultimo_checkin = $stmt->get_result()->fetch_assoc();
        
        $puede_registrar = true;
        $mensaje_tiempo = "";
        
        if ($ultimo_checkin) {
            $ultima_fecha_hora = strtotime($ultimo_checkin['fecha'] . ' ' . $ultimo_checkin['hora']);
            $ahora = time();
            $diferencia_horas = ($ahora - $ultima_fecha_hora) / 3600;
            
            if ($diferencia_horas < 12) {
                $puede_registrar = false;
                $horas_restantes = 12 - $diferencia_horas;
                $mensaje = "Debe esperar " . round($horas_restantes, 1) . " horas para volver a registrarse.";
                $tipo_mensaje = "error";
            }
        }
        
        if ($puede_registrar) {
            $fecha_actual = date('Y-m-d');
            $hora_actual = date('H:i:s');
            
            $stmt = $conexion->prepare("INSERT INTO asistencias (empleado_id, fecha, hora, tipo) VALUES (?, ?, ?, 'consumio')");
            $stmt->bind_param("iss", $empleado_id, $fecha_actual, $hora_actual);
            
            if ($stmt->execute()) {
                $mensaje = "Check-in registrado exitosamente para " . $empleado['nombre'];
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al registrar el check-in";
                $tipo_mensaje = "error";
            }
        }
    } else {
        $mensaje = "Empleado no encontrado. Por favor, contacte a recursos humanos.";
        $tipo_mensaje = "error";
    }
}

// Consultar últimos registros
$query_registros = "
    SELECT e.no_trabajador, e.nombre, a.fecha, a.hora 
    FROM asistencias a 
    JOIN empleado e ON a.empleado_id = e.id 
    WHERE e.eliminado = 0 
    AND a.tipo = 'consumio'
    ORDER BY a.fecha DESC, a.hora DESC 
    LIMIT 100";
$registros = $conexion->query($query_registros);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Comedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/forms.css">
    <link rel="stylesheet" href="../CSS/tables.css">
    <link rel="stylesheet" href="../CSS/footer.css">
</head>
<body>
    <main>
        <div class="containerphp">
            <h1>Check-in Comedor</h1>
            
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="check-in-form">
                <div class="form-group">
                    <label for="numero_empleado">Número de Empleado:</label>
                    <input type="number" id="numero_empleado" name="numero_empleado" required autofocus>
                </div>
                <button type="submit" class="btn-save">Registrar Check-in</button>
            </form>

            <div class="tabla-container">
                <h3>Últimos Registros</h3>
                <div class="scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Trabajador</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($registro = $registros->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registro['no_trabajador']); ?></td>
                                <td><?php echo htmlspecialchars($registro['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($registro['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($registro['hora']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="acciones-masivas">
                <a href="exportar_checkin.php" class="btn-excel">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
            </div>

            <div class="vaciar-bd-container">
                <a href="../index.html" class="button-back">Regresar</a>
            </div>
        </div>
    </main>
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
                <p><a href="agregar.php">Empleados</a></p>
                <p><a href="eliminados.php">Eliminados</a></p>
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