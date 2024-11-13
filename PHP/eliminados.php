<?php
include '../conexion.php';
// Consulta empleados eliminados
$consulta = "SELECT * FROM empleado WHERE eliminado = 0";
$resultado = $conexion->query($consulta);

if (isset($_POST['eliminar_seleccionados']) && isset($_POST['empleados'])) {
    $empleadosSeleccionados = $_POST['empleados'];
    $errorCount = 0;
    
    // Recorre cada empleado seleccionado y lo marca como eliminado
    foreach ($empleadosSeleccionados as $id) {
        $consulta = $conexion->prepare("UPDATE empleado SET eliminado = 1 WHERE id = ?");
        $consulta->bind_param("i", $id);
        if (!$consulta->execute()) {
            $errorCount++;
        }
    }
    
    // Establece mensaje de éxito o error
    $_SESSION['message'] = ($errorCount == 0) 
        ? "Empleados eliminados correctamente" 
        : "Hubo errores al eliminar {$errorCount} empleados";
        
    header("Location: eliminados.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleados Eliminados</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/forms.css">
    <link rel="stylesheet" href="../CSS/tables.css">
    <link rel="stylesheet" href="../CSS/footer.css">
</head>
<script>
        // Función para seleccionar/deseleccionar todos los checkboxes
        function seleccionarTodos(source) {
            checkboxes = document.getElementsByName('empleados[]');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
<body>
    <main>
        <div class="eliminados-container">
            <h3>Lista de Empleados</h3>
            <div class="scroll">
                <form method="POST" action="eliminados.php">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" onClick="seleccionarTodos(this)"></th>
                                <th>No. Trabajador</th>
                                <th>Nombre</th>
                                <th>Turno</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" name="empleados[]" value="<?php echo $fila['id']; ?>"></td>
                                    <td><?php echo $fila['no_trabajador']; ?></td>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['turno']; ?></td>
                                    <td>
                                        <a href="eliminar.php?id=<?php echo $fila['id']; ?>" class="btn btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este empleado?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <button type="submit" name="eliminar_seleccionados" class="btn btn-eliminar">Eliminar Seleccionados</button>
                </form>
            </div>

            <?php
                include '../conexion.php';
                // Consulta empleados eliminados
                $consulta = "SELECT * FROM empleado WHERE eliminado = 1";
                $resultado1 = $conexion->query($consulta);
             ?>

            <h2>Empleados Eliminados</h2>

            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No. Trabajador</th>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado1->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['no_trabajador']; ?></td>
                                <td><?php echo $fila['nombre']; ?></td>
                                <td><?php echo $fila['turno']; ?></td>
                                <td>
                                    <div class="btn-container">
                                        <a href="restaurar.php?id=<?php echo $fila['id']; ?>" class="btn btn-crear">
                                            <img src="../img/restaurar.svg" alt="Restaurar">
                                        </a>
                                        <a href="eliminar_permanente.php?id=<?php echo $fila['id']; ?>" 
                                           class="btn btn-eliminar" 
                                           onclick="return confirm('¿Estás seguro de que deseas eliminar permanentemente a este empleado?');">
                                            <img src="../img/eliminar.svg" alt="Eliminar">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="acciones-masivas">
                <form method="POST" action="restaurar_todos.php" style="display: inline-block;">
                    <button type="submit" 
                            name="restaurar_todos" 
                            class="btn btn-crear"
                            onclick="return confirm('¿Estás seguro de que deseas restaurar todos los empleados eliminados?');">
                        Restaurar Todos
                    </button>
                </form>
                
                <form method="POST" action="eliminar_todos_permanente.php" style="display: inline-block;">
                    <button type="submit" 
                            name="eliminar_todos_permanente" 
                            class="btn btn-eliminar"
                            onclick="return confirm('¿Estás seguro de que deseas eliminar PERMANENTEMENTE todos los empleados? Esta acción no se puede deshacer.');">
                        Eliminar Todos Permanentemente
                    </button>
                </form>
            </div>
            <div class="vaciar-bd-container">
                <form method="POST" action="vaciar_bd.php">
                    <button type="submit" 
                            name="vaciar_bd" 
                            class="btn btn-eliminar"
                            onclick="return confirm('¿Estás seguro de que deseas eliminar PERMANENTEMENTE todos los registros? Esta acción no se puede deshacer.');">
                        Vaciar Base de Datos
                    </button>
                </form>
                <a href="../index.html" class="button-back">Regresar</a>
            </div>
        </div>
    </main>

    <!-- Mismo footer que en registros.php -->
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

