<?php
include '../conexion.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Establecer zona horaria
date_default_timezone_set('America/Tijuana');

// Crear nuevo spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Consultar solo registros de tipo 'consumio'
$query = "
    SELECT 
        e.no_trabajador,
        e.nombre,
        e.turno,
        a.fecha,
        a.hora,
        a.tipo
    FROM asistencias a 
    JOIN empleado e ON a.empleado_id = e.id 
    WHERE e.eliminado = 0 
    AND a.tipo = 'consumio'
    ORDER BY a.fecha DESC, a.hora DESC";

$resultado = $conexion->query($query);

// Establecer encabezados
$sheet->setCellValue('A1', 'No. Trabajador');
$sheet->setCellValue('B1', 'Nombre');
$sheet->setCellValue('C1', 'Turno');
$sheet->setCellValue('D1', 'Fecha');
$sheet->setCellValue('E1', 'Hora');
$sheet->setCellValue('F1', 'Estado');

// Dar formato al encabezado
$sheet->getStyle('A1:F1')->getFont()->setBold(true);
$sheet->getStyle('A1:F1')->getFont()->setSize(12);
$sheet->getStyle('A1:F1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FF004085');
$sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');

// Llenar datos
$row = 2;
while ($registro = $resultado->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $registro['no_trabajador']);
    $sheet->setCellValue('B' . $row, $registro['nombre']);
    $sheet->setCellValue('C' . $row, $registro['turno']);
    $sheet->setCellValue('D' . $row, $registro['fecha']);
    $sheet->setCellValue('E' . $row, $registro['hora']);
    $sheet->setCellValue('F' . $row, ucfirst($registro['tipo'])); // Capitaliza la primera letra
    
    // Dar formato a las celdas de datos
    $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    
    // Color verde para las filas de "consumio"
    $sheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF28a745');
    
    $row++;
}

// Autoajustar columnas
foreach(range('A','F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Agregar bordes a la tabla
$lastRow = $row - 1;
$sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Alinear al centro todas las columnas
$sheet->getStyle('A1:F' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Limpiar cualquier salida previa
ob_end_clean();

// Configurar headers para la descarga
$filename = 'Registros_Comedor_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Crear archivo Excel y enviarlo al navegador
$writer = new Xlsx($spreadsheet);
ob_start();
$writer->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();

echo $xlsData;
exit; 