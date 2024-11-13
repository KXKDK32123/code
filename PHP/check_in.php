<?php
include 'conexion.php';

$id = $_GET['id'];
$fechaActual = date('Y-m-d');
$horaActual = date('H:i:s');

// Obtener información del empleado
$stmt = $conexion->prepare("SELECT turno, hora_entrada FROM empleado WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$empleado = $stmt->get_result()->fetch_assoc();

// Calcular diferencia de tiempo
$hora_entrada = strtotime($empleado['hora_entrada']);
$hora_actual = strtotime($horaActual);
$diferencia_minutos = ($hora_actual - $hora_entrada) / 60;

// Determinar tipo de registro
if ($diferencia_minutos <= 5) {
    $tipo = 'asistencia';
} else {
    $tipo = 'retardo';
}

// Registrar en historial de asistencias
$stmt = $conexion->prepare("INSERT INTO asistencias (empleado_id, fecha, hora, tipo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id, $fechaActual, $horaActual, $tipo);
$stmt->execute();

// Actualizar check_in actual (mantener compatibilidad)
$consulta = "UPDATE empleado SET check_in = 1, fecha_check_in = NOW() WHERE id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.html");
?>
