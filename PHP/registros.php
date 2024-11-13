<?php
include '../conexion.php';

// Establecer la zona horaria correcta para México
date_default_timezone_set('America/Tijuana');

// Inicialización de variables para la primera tabla
$turno = isset($_GET['turno']) ? $_GET['turno'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
$singleDate = isset($_GET['single_date']) ? $_GET['single_date'] : date('Y-m-d');
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-1 month'));
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Inicialización de variables para la segunda tabla
$stats_turno = isset($_GET['stats_turno']) ? $_GET['stats_turno'] : '';
$stats_filter_type = isset($_GET['stats_filter_type']) ? $_GET['stats_filter_type'] : 'all';
$stats_date = isset($_GET['stats_date']) ? $_GET['stats_date'] : date('Y-m-d');
$stats_fecha_inicio = isset($_GET['stats_fecha_inicio']) ? $_GET['stats_fecha_inicio'] : date('Y-m-d', strtotime('-1 month'));
$stats_fecha_fin = isset($_GET['stats_fecha_fin']) ? $_GET['stats_fecha_fin'] : date('Y-m-d');

// Función para registrar faltas automáticamente
function registrarFaltasAutomaticas($conexion) {
    $fecha_ayer = date('Y-m-d', strtotime('-1 day'));
    
    // Obtener todos los empleados activos que no registraron asistencia ayer
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
}

// Verificar y registrar faltas cada vez que se accede a la página
registrarFaltasAutomaticas($conexion);

// Construir la condición de fecha según el tipo de filtro
$fechaCondition = "";
switch ($filterType) {
    case 'day':
        $fechaCondition = "a.fecha = '$singleDate'";
        break;
    case 'month':
        $fechaCondition = "MONTH(a.fecha) = MONTH('$singleDate') AND YEAR(a.fecha) = YEAR('$singleDate')";
        break;
    case 'year':
        $fechaCondition = "YEAR(a.fecha) = YEAR('$singleDate')";
        break;
    case 'range':
        $fechaCondition = "a.fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
        break;
    case 'all':
        $fechaCondition = "1=1"; // Sin filtro de fecha
        break;
}

// Consulta de estadísticas mejorada
$stats_query = "
    SELECT 
        e.id,
        e.nombre,
        COUNT(CASE WHEN a.tipo = 'consumio' THEN 1 END) as consumio,
        COUNT(CASE WHEN a.tipo = 'no_consumio' THEN 1 END) as no_consumio
    FROM empleado e
    LEFT JOIN asistencias a ON e.id = a.empleado_id 
    WHERE e.eliminado = 0
    AND ($fechaCondition)
    " . ($turno ? "AND e.turno = '$turno'" : "") . "
    GROUP BY e.id, e.nombre
    ORDER BY e.nombre
";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Empleados</title>
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
            <h2>Registros de Empleados</h2>
            
            <!-- Primera tabla: Filtros -->
            <div class="filtros-container">
                <form method="GET" action="" class="filtros-form">
                    <!-- Grupo de Turno -->
                    <div class="filtro-grupo">
                        <label for="turno">Turno:</label>
                        <select name="turno" id="turno">
                            <option value="">Todos</option>
                            <option value="Matutino" <?php echo $turno == 'Matutino' ? 'selected' : ''; ?>>Matutino</option>
                            <option value="Vespertino" <?php echo $turno == 'Vespertino' ? 'selected' : ''; ?>>Vespertino</option>
                            <option value="Nocturno" <?php echo $turno == 'Nocturno' ? 'selected' : ''; ?>>Nocturno</option>
                        </select>
                    </div>

                    <!-- Grupo de Tipo de Filtro -->
                    <div class="filtro-grupo">
                        <label for="filter_type">Filtrar por:</label>
                        <select name="filter_type" id="filter_type" onchange="toggleDateInputs()">
                            <option value="all" <?php echo $filterType == 'all' ? 'selected' : ''; ?>>Todos</option>
                            <option value="day" <?php echo $filterType == 'day' ? 'selected' : ''; ?>>Día</option>
                            <option value="month" <?php echo $filterType == 'month' ? 'selected' : ''; ?>>Mes</option>
                            <option value="year" <?php echo $filterType == 'year' ? 'selected' : ''; ?>>Año</option>
                            <option value="range" <?php echo $filterType == 'range' ? 'selected' : ''; ?>>Periodo</option>
                        </select>
                    </div>

                    <!-- Grupo de Fecha Única -->
                    <div id="single-date" class="filtro-grupo" style="display: none;">
                        <label for="single_date">Fecha:</label>
                        <input type="date" name="single_date" id="single_date" value="<?php echo $singleDate; ?>">
                    </div>

                    <!-- Grupo de Rango de Fechas -->
                    <div id="date-range" class="filtro-grupo" style="display: none;">
                        <div class="filtro-grupo">
                            <label for="fecha_inicio">Desde:</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $fechaInicio; ?>">
                        </div>
                        <div class="filtro-grupo">
                            <label for="fecha_fin">Hasta:</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $fechaFin; ?>">
                        </div>
                    </div>

                    <!-- Campos ocultos para mantener los valores del segundo formulario -->
                    <input type="hidden" name="stats_turno" value="<?php echo htmlspecialchars($stats_turno); ?>">
                    <input type="hidden" name="stats_filter_type" value="<?php echo htmlspecialchars($stats_filter_type); ?>">
                    <input type="hidden" name="stats_date" value="<?php echo htmlspecialchars($stats_date); ?>">
                    <input type="hidden" name="stats_fecha_inicio" value="<?php echo htmlspecialchars($stats_fecha_inicio); ?>">
                    <input type="hidden" name="stats_fecha_fin" value="<?php echo htmlspecialchars($stats_fecha_fin); ?>">

                    <button type="submit" class="btn-filtrar">Filtrar</button>
                </form>
            </div>

            <h3><?php echo $singleDate; ?></h3>

            <!-- Primera tabla: Registros detallados -->
            <div class="tabla-container">
                <h3>Registros Detallados</h3>
                <div class="scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Trabajador</th>
                                <th>Nombre</th>
                                <th>Turno</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta para la primera tabla
                            $registros_query = "
                                SELECT 
                                    e.no_trabajador,
                                    e.nombre,
                                    e.turno,
                                    a.fecha,
                                    a.hora,
                                    a.tipo as estado
                                FROM empleado e
                                LEFT JOIN asistencias a ON e.id = a.empleado_id
                                WHERE e.eliminado = 0
                            ";

                            // Aplicar filtros específicos para la primera tabla
                            if ($turno) {
                                $registros_query .= " AND e.turno = '$turno'";
                            }

                            if ($filterType !== 'all') {
                                switch ($filterType) {
                                    case 'day':
                                        $registros_query .= " AND a.fecha = '$singleDate'";
                                        break;
                                    case 'month':
                                        $registros_query .= " AND MONTH(a.fecha) = MONTH('$singleDate') 
                                                            AND YEAR(a.fecha) = YEAR('$singleDate')";
                                        break;
                                    case 'year':
                                        $registros_query .= " AND YEAR(a.fecha) = YEAR('$singleDate')";
                                        break;
                                    case 'range':
                                        $registros_query .= " AND a.fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
                                        break;
                                }
                            }

                            $registros_query .= " ORDER BY a.fecha DESC, a.hora DESC";
                            
                            $resultado = $conexion->query($registros_query);

                            if ($resultado && $resultado->num_rows > 0) {
                                while ($fila = $resultado->fetch_assoc()) {
                                    $estadoClass = '';
                                    switch ($fila['estado']) {
                                        case 'consumio':
                                            $estadoClass = 'estado-consumio';
                                            break;
                                        case 'no_consumio':
                                            $estadoClass = 'estado-no-consumio';
                                            break;
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila['no_trabajador']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['turno']); ?></td>
                                        <td><?php echo $fila['fecha']; ?></td>
                                        <td><?php echo $fila['hora']; ?></td>
                                        <td class="<?php echo $estadoClass; ?>"><?php echo ucfirst($fila['estado']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">No hay registros para mostrar</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Segunda tabla: Filtros -->
            <div class="filtros-container">
                <form method="GET" action="" class="filtros-form">
                    <div class="filtro-grupo">
                        <label for="stats_turno">Turno:</label>
                        <select name="stats_turno" id="stats_turno">
                            <option value="">Todos</option>
                            <option value="Matutino" <?php echo $stats_turno == 'Matutino' ? 'selected' : ''; ?>>Matutino</option>
                            <option value="Vespertino" <?php echo $stats_turno == 'Vespertino' ? 'selected' : ''; ?>>Vespertino</option>
                            <option value="Nocturno" <?php echo $stats_turno == 'Nocturno' ? 'selected' : ''; ?>>Nocturno</option>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label for="stats_filter_type">Filtrar por:</label>
                        <select name="stats_filter_type" id="stats_filter_type" onchange="toggleStatsDateInputs()">
                            <option value="all" <?php echo $stats_filter_type == 'all' ? 'selected' : ''; ?>>Todos</option>
                            <option value="day" <?php echo $stats_filter_type == 'day' ? 'selected' : ''; ?>>Día</option>
                            <option value="month" <?php echo $stats_filter_type == 'month' ? 'selected' : ''; ?>>Mes</option>
                            <option value="year" <?php echo $stats_filter_type == 'year' ? 'selected' : ''; ?>>Año</option>
                            <option value="range" <?php echo $stats_filter_type == 'range' ? 'selected' : ''; ?>>Periodo</option>
                        </select>
                    </div>

                    <div id="stats_single_date_div" class="filtro-grupo" style="display: none;">
                        <label for="stats_date">Fecha:</label>
                        <input type="date" name="stats_date" id="stats_date" value="<?php echo $stats_date; ?>">
                    </div>

                    <div id="stats_date_range_div" class="filtro-grupo" style="display: none;">
                        <label for="stats_fecha_inicio">Desde:</label>
                        <input type="date" name="stats_fecha_inicio" id="stats_fecha_inicio" value="<?php echo $stats_fecha_inicio; ?>">
                        <label for="stats_fecha_fin">Hasta:</label>
                        <input type="date" name="stats_fecha_fin" id="stats_fecha_fin" value="<?php echo $stats_fecha_fin; ?>">
                    </div>

                    <!-- Campos ocultos para mantener los valores del primer formulario -->
                    <input type="hidden" name="turno" value="<?php echo htmlspecialchars($turno); ?>">
                    <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filterType); ?>">
                    <input type="hidden" name="single_date" value="<?php echo htmlspecialchars($singleDate); ?>">
                    <input type="hidden" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                    <input type="hidden" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>">

                    <button type="submit" class="btn-filtrar">Filtrar</button>
                </form>
            </div>

            <!-- Segunda tabla: Resumen de uso -->
            <div class="estadisticas-container">
                <h3>Resumen de uso</h3>
                <div class="scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Consumió</th>
                                <th>No Consumió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Procesar filtros específicos para la segunda tabla
                            $stats_turno = isset($_GET['stats_turno']) ? $_GET['stats_turno'] : '';
                            $stats_filter_type = isset($_GET['stats_filter_type']) ? $_GET['stats_filter_type'] : 'all';
                            $stats_date = isset($_GET['stats_date']) ? $_GET['stats_date'] : date('Y-m-d');
                            $stats_fecha_inicio = isset($_GET['stats_fecha_inicio']) ? $_GET['stats_fecha_inicio'] : date('Y-m-d', strtotime('-1 month'));
                            $stats_fecha_fin = isset($_GET['stats_fecha_fin']) ? $_GET['stats_fecha_fin'] : date('Y-m-d');

                            // Construir la consulta para las estadísticas
                            $stats_query = "
                                SELECT 
                                    e.nombre,
                                    SUM(CASE WHEN a.tipo = 'consumio' THEN 1 ELSE 0 END) as consumio,
                                    SUM(CASE WHEN a.tipo = 'no_consumio' THEN 1 ELSE 0 END) as no_consumio
                                FROM empleado e
                                LEFT JOIN asistencias a ON e.id = a.empleado_id 
                                WHERE e.eliminado = 0
                            ";

                            // Aplicar filtros específicos para las estadísticas
                            if ($stats_turno) {
                                $stats_query .= " AND e.turno = '$stats_turno'";
                            }

                            if ($stats_filter_type !== 'all') {
                                switch ($stats_filter_type) {
                                    case 'day':
                                        $stats_query .= " AND a.fecha = '$stats_date'";
                                        break;
                                    case 'month':
                                        $stats_query .= " AND MONTH(a.fecha) = MONTH('$stats_date') 
                                                        AND YEAR(a.fecha) = YEAR('$stats_date')";
                                        break;
                                    case 'year':
                                        $stats_query .= " AND YEAR(a.fecha) = YEAR('$stats_date')";
                                        break;
                                    case 'range':
                                        $stats_query .= " AND a.fecha BETWEEN '$stats_fecha_inicio' AND '$stats_fecha_fin'";
                                        break;
                                }
                            }

                            $stats_query .= " GROUP BY e.id, e.nombre ORDER BY e.nombre";

                            $stats_result = $conexion->query($stats_query);
                            
                            if ($stats_result && $stats_result->num_rows > 0) {
                                while ($stat = $stats_result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['nombre']); ?></td>
                                        <td><?php echo $stat['consumio']; ?></td>
                                        <td><?php echo $stat['no_consumio']; ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3">No hay registros para el período seleccionado</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <?php
                            // Calcular totales
                            $totals_query = str_replace("e.nombre,", "'TOTAL' as nombre,", $stats_query);
                            $totals_query = preg_replace("/GROUP BY.*$/", "", $totals_query);
                            
                            $totals_result = $conexion->query($totals_query);
                            if ($totals_result && $row = $totals_result->fetch_assoc()) {
                                ?>
                                <tr class="totales">
                                    <td><strong>TOTAL</strong></td>
                                    <td><strong><?php echo $row['consumio']; ?></strong></td>
                                    <td><strong><?php echo $row['no_consumio']; ?></strong></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
           </div>
        </div>
        <div class="vaciar-bd-container">
            <a href="../index.html" class="button-back">Regresar</a>
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
                <p><a href="agregar.php">Empleados</a></p>
                <p><a href="estadisticas.php">Estadísticas</a></p>
                <p><a href="check_in_simple.php">Check in</a></p>
            </div>
            <div class="footer-section">
                <h4>Contacto</h4>
                <p>Email: soporte@comedor.com</p>
                <p>Tel: (123) 456-7890</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Sistema de Comedor. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html> 
