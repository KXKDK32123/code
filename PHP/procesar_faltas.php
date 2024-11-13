<?php
include '../conexion.php';

// Este script debe ejecutarse diariamente mediante un cron job
$fecha_ayer = date('Y-m-d', strtotime('-1 day'));

// Obtener empleados sin registro de ayer
$query = "
    INSERT INTO asistencias (empleado_id, fecha, hora, tipo)
    SELECT 
        e.id, 
        ?, 
        e.hora_entrada, 
        'falta'
    FROM empleado e
    LEFT JOIN asistencias a ON e.id = a.empleado_id 
        AND DATE(a.fecha) = ?
    WHERE a.id IS NULL 
        AND e.eliminado = 0
";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ss", $fecha_ayer, $fecha_ayer);
$stmt->execute();
?>
