<?php
include '../conexion.php';
session_start();

try {
    // Verifica si hay registros para eliminar en cualquiera de las tablas
    $tablas = ['asistencias', 'escaneos', 'empleado'];
    $hayRegistros = false;
    
    foreach ($tablas as $tabla) {
        $consulta = "SELECT COUNT(*) as total FROM $tabla";
        $resultado = $conexion->query($consulta);
        if ($resultado->fetch_assoc()['total'] > 0) {
            $hayRegistros = true;
            break;
        }
    }

    if ($hayRegistros) {
        // Inicia una transacción
        $conexion->begin_transaction();

        // Elimina registros en orden debido a las claves foráneas
        $consulta = "DELETE FROM asistencias";
        if (!$conexion->query($consulta)) {
            throw new Exception("Error al eliminar registros de asistencias");
        }

        $consulta = "DELETE FROM escaneos";
        if (!$conexion->query($consulta)) {
            throw new Exception("Error al eliminar registros de escaneos");
        }

        $consulta = "DELETE FROM empleado";
        if (!$conexion->query($consulta)) {
            throw new Exception("Error al eliminar registros de empleado");
        }

        // Reinicia los auto_increment de todas las tablas
        foreach ($tablas as $tabla) {
            $consulta = "ALTER TABLE $tabla AUTO_INCREMENT = 1";
            if (!$conexion->query($consulta)) {
                throw new Exception("Error al reiniciar el auto_increment de $tabla");
            }
        }

        // Confirma los cambios
        $conexion->commit();
        $_SESSION['message'] = "Se han eliminado todos los registros y reiniciado los contadores correctamente";
        $_SESSION['message_type'] = "success";

    } else {
        $_SESSION['message'] = "Las tablas ya están vacías";
        $_SESSION['message_type'] = "info";
    }

} catch (Exception $e) {
    // Revierte los cambios si hay error
    if ($conexion->connect_errno == 0) {
        $conexion->rollback();
    }
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Redirecciona
header("Location: eliminados.php");
exit();
?>
