<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportesController {
    private $db;
    private $model;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo ReportesModel.
     */
    public function __construct($db) {
        $this->db = $db;
        require_once 'app/models/ReportesModel.php';
        $this->model = new ReportesModel($db);
    }

    /**
     * Mostrar vista principal de reportes
     * Verifica que el usuario sea administrador. Recupera datos necesarios
     * para gráficas y métricas y carga la vista de generación de reportes.
     * 
     * /Recibe: $_SESSION para verificar rol
     * /Devuelve: Incluye la vista 'app/views/generar_reportes.php'
     */
    public function mostrarReportes() {
        // Verificar que sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login&error=4");
            exit();
        }
        $objetosPorCategoria = $this->model->obtenerObjetosPorCategoria();
        $lugaresReunion = $this->model->obtenerLugaresReunion();
        $estadoReuniones = $this->model->obtenerEstadoReuniones();
        $totalesGenerales = $this->model->obtenerTotalesGenerales();

        include 'app/views/generar_reportes.php';
    }

    /**
     * Ver PDF en el navegador
     * Genera un PDF usando Dompdf y lo muestra en el navegador (Attachment => false).
     * Verifica permisos de administrador y delega la generación del HTML según tipo.
     * 
     * /Recibe: GET[tipo] (tipo de reporte)
     * /Devuelve: Stream del PDF al navegador 
     */
    public function verPDF() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login&error=4");
            exit();
        }
        $tipoReporte = $_GET['tipo'] ?? '';
        if (empty($tipoReporte)) {
            exit("Error: Tipo de reporte no especificado.");
        }
        require_once 'vendor/autoload.php';
        
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);


        $html = $this->generarHTMLReporte($tipoReporte);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $fileName = "Reporte_" . ucfirst($tipoReporte) . "_" . date('Ymd_His') . ".pdf";
        $dompdf->stream($fileName, ["Attachment" => false]);
    }

    /**
     * Generar PDF de reporte específico
     * Similar a verPDF() pero fuerza la descarga del archivo (Attachment => true).
     * 
     * /Recibe: GET[tipo]
     * /Devuelve: Descarga del PDF 
     */
    public function generarPDF() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login&error=4");
            exit();
        }
        $tipoReporte = $_GET['tipo'] ?? '';
        if (empty($tipoReporte)) {
            exit("Error: Tipo de reporte no especificado.");
        }
        require_once 'vendor/autoload.php';
        
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
     
        $html = $this->generarHTMLReporte($tipoReporte);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = "Reporte_" . ucfirst($tipoReporte) . "_" . date('Ymd_His') . ".pdf";
        $dompdf->stream($fileName, ["Attachment" => true]);
    }

    /**
     * Generar HTML según tipo de reporte
     * Construye el HTML del reporte según el tipo solicitado y lo envuelve
     * en la plantilla principal para PDF.
     * 
     * /Recibe: $tipo (string)
     * /Devuelve: String HTML completo listo para renderizar en PDF
     */
    private function generarHTMLReporte($tipo) {
        $html = '';
        $fecha = date('d/m/Y H:i:s');

        switch ($tipo) {
            case 'objetos_categoria':
                $data = $this->model->obtenerObjetosPorCategoria();
                $detalles = $this->model->obtenerDetalleObjetos(30);
                $html = $this->generarReporteObjetosCategoria($data, $detalles, $fecha);
                break;

            case 'lugares_reunion':
                $data = $this->model->obtenerLugaresReunion();
                $detalles = $this->model->obtenerDetalleReuniones(30);
                $html = $this->generarReporteLugaresReunion($data, $detalles, $fecha);
                break;

            case 'estado_reuniones':
                $data = $this->model->obtenerEstadoReuniones();
                $detalles = $this->model->obtenerDetalleReuniones(30);
                $html = $this->generarReporteEstadoReuniones($data, $detalles, $fecha);
                break;

            default:
                $html = '<h1>Error: Tipo de reporte no reconocido</h1>';
        }
        return $this->wrapHTMLTemplate($html);
    }

    /**
     * Reporte 1: Objetos por Categoría
     * Construye HTML con métricas, gráfico de barras simple y tabla de detalle.
     * 
     * /Recibe: $data (array resumen por categoría), $detalles (array detalles), $fecha (string)
     * /Devuelve: String HTML parcial del reporte
     */
    private function generarReporteObjetosCategoria($data, $detalles, $fecha) {
        if (empty($data)) {
            return '<h1>No hay datos disponibles</h1>';
        }
        $totalObjetos = array_sum(array_column($data, 'total'));
        $categorias = count($data);

        $barrasHTML = '<div class="bar-chart-container">';
        foreach ($data as $item) {
            $porcentaje = ($totalObjetos > 0) ? number_format(($item['total'] / $totalObjetos) * 100, 1) : 0;
            $color = $this->getColorForCategory($item['categoria']);
            $barrasHTML .= "
                <div class='chart-bar-item'>
                    <span class='bar-label'>{$item['categoria']} ({$item['total']} objetos)</span>
                    <div class='bar-wrap'>
                        <div class='bar' style='width: {$porcentaje}%; background-color: {$color};'>{$porcentaje}%</div>
                    </div>
                </div>";
        }
        $barrasHTML .= '</div>';

        $tablaHTML = '<table class="report-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">Activos</th>
                    <th style="text-align:center;">Eliminados</th>
                    <th style="text-align:center;">% del Total</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($data as $index => $item) {
            $porcentaje = ($totalObjetos > 0) ? number_format(($item['total'] / $totalObjetos) * 100, 1) : 0;
            $tablaHTML .= "
                <tr>
                    <td>" . ($index + 1) . "</td>
                    <td class='col-titulo'>" . htmlspecialchars($item['categoria']) . "</td>
                    <td style='text-align:center; font-weight:bold;'>{$item['total']}</td>
                    <td style='text-align:center; color: #38b2ac;'>{$item['activos']}</td>
                    <td style='text-align:center; color: #e53e3e;'>{$item['eliminados']}</td>
                    <td style='text-align:center;'>{$porcentaje}%</td>
                </tr>";
        }
        $tablaHTML .= '</tbody></table>';
        return "
            <h1>REPORTE: OBJETOS POR CATEGORÍA</h1>
            <p class='fecha-generacion'>Fecha de Generación: {$fecha}</p>

            <div class='total-box'>
                Total de Objetos Registrados: 
                <div class='total-value'>" . number_format($totalObjetos, 0, '', '.') . "</div>
            </div>

            <div class='total-box'>
                Categorías Disponibles: 
                <div class='total-value'>{$categorias}</div>
            </div>

            <h2>Distribución por Categoría</h2>
            {$barrasHTML}

            <h2 style='margin-top: 40px;'>Detalle por Categoría</h2>
            {$tablaHTML}

            <p class='conclusion'><strong>Conclusión:</strong> La distribución muestra las categorías más populares entre los usuarios de la plataforma de trueques.</p>
        ";
    }

    /**
     * Reporte 2: Lugares de Reunión
     *  Construye HTML con métricas por lugar y tabla de detalle.
     * 
     * /Recibe: $data (array resumen por lugar), $detalles (array detalles), $fecha (string)
     * /Devuelve: String HTML parcial del reporte
     */
    private function generarReporteLugaresReunion($data, $detalles, $fecha) {
        if (empty($data)) {
            return '<h1>No hay datos disponibles</h1>';
        }
        $totalReuniones = array_sum(array_column($data, 'total_reuniones'));
        $lugares = count($data);

        $barrasHTML = '<div class="bar-chart-container">';
        foreach ($data as $item) {
            $porcentaje = ($totalReuniones > 0) ? number_format(($item['total_reuniones'] / $totalReuniones) * 100, 1) : 0;
            $color = '#4299e1';            
            $barrasHTML .= "
                <div class='chart-bar-item'>
                    <span class='bar-label'>{$item['lugar']} ({$item['total_reuniones']} reuniones)</span>
                    <div class='bar-wrap'>
                        <div class='bar' style='width: {$porcentaje}%; background-color: {$color};'>{$porcentaje}%</div>
                    </div>
                </div>";
        }
        $barrasHTML .= '</div>';

        $tablaHTML = '<table class="report-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lugar</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">Confirmadas</th>
                    <th style="text-align:center;">Canceladas</th>
                    <th style="text-align:center;">Pendientes</th>
                    <th style="text-align:center;">Completadas</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($data as $index => $item) {
            $tablaHTML .= "
                <tr>
                    <td>" . ($index + 1) . "</td>
                    <td class='col-titulo'>" . htmlspecialchars($item['lugar']) . "</td>
                    <td style='text-align:center; font-weight:bold;'>{$item['total_reuniones']}</td>
                    <td style='text-align:center; color: #38b2ac;'>{$item['confirmadas']}</td>
                    <td style='text-align:center; color: #e53e3e;'>{$item['canceladas']}</td>
                    <td style='text-align:center; color: #ecc94b;'>{$item['pendientes']}</td>
                    <td style='text-align:center; color: #805ad5;'>{$item['completadas']}</td>
                </tr>";
        }
        $tablaHTML .= '</tbody></table>';

        return "
            <h1>REPORTE: LUGARES DE REUNIÓN</h1>
            <p class='fecha-generacion'>Fecha de Generación: {$fecha}</p>

            <div class='total-box'>
                Total de Reuniones Registradas: 
                <div class='total-value'>" . number_format($totalReuniones, 0, '', '.') . "</div>
            </div>

            <div class='total-box'>
                Lugares Utilizados: 
                <div class='total-value'>{$lugares}</div>
            </div>

            <h2>Distribución por Lugar</h2>
            {$barrasHTML}

            <h2 style='margin-top: 40px;'>Detalle por Lugar</h2>
            {$tablaHTML}

            <p class='conclusion'><strong>Conclusión:</strong> Este reporte muestra los lugares más utilizados para las reuniones de trueque en la universidad.</p>
        ";
    }

    /**
     * Reporte 3: Estado de Reuniones
     * Construye HTML con distribución por estado (confirmada, pendiente, cancelada, completada).
     * 
     * /Recibe: $data (array resumen por estado), $detalles (array detalles), $fecha (string)
     * /Devuelve: String HTML parcial del reporte
     */
    private function generarReporteEstadoReuniones($data, $detalles, $fecha) {
        if (empty($data)) {
            return '<h1>No hay datos disponibles</h1>';
        }
        $totalReuniones = array_sum(array_column($data, 'total'));

        $confirmadas = $pendientes = $canceladas = $completadas = 0;
        foreach ($data as $item) {
            switch ($item['estado_general']) {
                case 'confirmada':
                    $confirmadas = $item['total'];
                    break;
                case 'pendiente':
                    $pendientes = $item['total'];
                    break;
                case 'cancelada':
                    $canceladas = $item['total'];
                    break;
                case 'completada':
                    $completadas = $item['total'];
                    break;
            }
        }
        $percConfirmadas = ($totalReuniones > 0) ? number_format(($confirmadas / $totalReuniones) * 100, 1) : 0;
        $percPendientes = ($totalReuniones > 0) ? number_format(($pendientes / $totalReuniones) * 100, 1) : 0;
        $percCanceladas = ($totalReuniones > 0) ? number_format(($canceladas / $totalReuniones) * 100, 1) : 0;
        $percCompletadas = ($totalReuniones > 0) ? number_format(($completadas / $totalReuniones) * 100, 1) : 0;

        // Generar gráfico de barras
        $barrasHTML = "
        <div class='bar-chart-container'>
            <div class='chart-bar-item'>
                <span class='bar-label'>Confirmadas ({$confirmadas})</span>
                <div class='bar-wrap'>
                    <div class='bar' style='width: {$percConfirmadas}%; background-color: #38b2ac;'>{$percConfirmadas}%</div>
                </div>
            </div>
            <div class='chart-bar-item'>
                <span class='bar-label'>Pendientes ({$pendientes})</span>
                <div class='bar-wrap'>
                    <div class='bar' style='width: {$percPendientes}%; background-color: #ecc94b;'>{$percPendientes}%</div>
                </div>
            </div>
            <div class='chart-bar-item'>
                <span class='bar-label'>Canceladas ({$canceladas})</span>
                <div class='bar-wrap'>
                    <div class='bar' style='width: {$percCanceladas}%; background-color: #e53e3e;'>{$percCanceladas}%</div>
                </div>
            </div>
            <div class='chart-bar-item'>
                <span class='bar-label'>Completadas ({$completadas})</span>
                <div class='bar-wrap'>
                    <div class='bar' style='width: {$percCompletadas}%; background-color: #805ad5;'>{$percCompletadas}%</div>
                </div>
            </div>
        </div>";

        // Tabla resumen
        $tablaHTML = '<table class="report-data-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th style="text-align:center;">Cantidad</th>
                    <th style="text-align:center;">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-titulo">Confirmadas</td>
                    <td style="text-align:center; color: #38b2ac; font-weight:bold;">' . $confirmadas . '</td>
                    <td style="text-align:center;">' . $percConfirmadas . '%</td>
                </tr>
                <tr>
                    <td class="col-titulo">Pendientes</td>
                    <td style="text-align:center; color: #ecc94b; font-weight:bold;">' . $pendientes . '</td>
                    <td style="text-align:center;">' . $percPendientes . '%</td>
                </tr>
                <tr>
                    <td class="col-titulo">Canceladas</td>
                    <td style="text-align:center; color: #e53e3e; font-weight:bold;">' . $canceladas . '</td>
                    <td style="text-align:center;">' . $percCanceladas . '%</td>
                </tr>
                <tr>
                    <td class="col-titulo">Completadas</td>
                    <td style="text-align:center; color: #805ad5; font-weight:bold;">' . $completadas . '</td>
                    <td style="text-align:center;">' . $percCompletadas . '%</td>
                </tr>
            </tbody>
        </table>';

        return "
            <h1>REPORTE: ESTADO DE REUNIONES</h1>
            <p class='fecha-generacion'>Fecha de Generación: {$fecha}</p>

            <div class='total-box'>
                Total de Reuniones: 
                <div class='total-value'>" . number_format($totalReuniones, 0, '', '.') . "</div>
            </div>

            <h2>Distribución por Estado</h2>
            {$barrasHTML}

            <h2 style='margin-top: 40px;'>Resumen de Estados</h2>
            {$tablaHTML}

            <p class='conclusion'><strong>Conclusión:</strong> Este reporte muestra la efectividad del sistema de reuniones, donde podemos ver el porcentaje de éxito en las confirmaciones versus las cancelaciones.</p>
        ";
    }

    /**
     * Envolver HTML con template completo
     * Añade estilos y estructura básica para la generación de PDF.
     * 
     * /Recibe: $contenido (string HTML parcial)
     * /Devuelve: String HTML completo listo para Dompdf
     */
    private function wrapHTMLTemplate($contenido) {
        return '
        <html><head>
            <meta charset="UTF-8">
            <style>
                :root {
                    --bg-primary: #080f1e;
                    --bg-secondary: #0f192b;
                    --text-light: #e0e7ff;
                    --text-muted: #aab8d0;
                    --accent-green: #38b2ac;
                    --accent-blue: #4299e1;
                    --accent-purple: #805ad5;
                    --accent-red: #e53e3e;
                    --accent-yellow: #ecc94b;
                }

                body { 
                    font-family: DejaVu Sans, sans-serif; 
                    margin: 40px; 
                    color: #34495e;
                }

                h1 { 
                    color: #0d162a;
                    border-bottom: 4px solid var(--accent-yellow); 
                    padding-bottom: 15px; 
                    margin-bottom: 25px;
                    font-weight: 800;
                    text-transform: uppercase;
                }

                h2 { 
                    color: var(--accent-blue); 
                    margin-top: 25px; 
                    border-bottom: 1px dashed #bdc3c7; 
                    padding-bottom: 5px;
                    font-weight: 600;
                }

                .fecha-generacion {
                    color: var(--accent-green); 
                    font-weight: bold;
                    margin-top: -15px;
                    margin-bottom: 15px;
                }

                .conclusion {
                    margin-top: 25px;
                    padding: 10px;
                    background: #f7f9fa;
                    border-left: 5px solid var(--accent-green);
                }

                .total-box {
                    background-color: #ecf0f1; 
                    border-left: 5px solid var(--accent-yellow); 
                    padding: 15px;
                    margin-bottom: 15px;
                    font-size: 1.1em;
                    font-weight: 500;
                }

                .total-value {
                    font-size: 1.5em;
                    font-weight: 700;
                    color: var(--accent-blue);
                    margin-top: 5px;
                }

                .report-data-table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 15px; 
                    font-size: 0.9em;
                }

                .report-data-table th, .report-data-table td { 
                    padding: 12px 10px; 
                    border: 1px solid #ecf0f1; 
                    text-align: left; 
                }

                .report-data-table th { 
                    background-color: #0d162a;
                    color: white; 
                    font-weight: 600;
                    text-transform: uppercase;
                }

                .report-data-table .col-titulo { 
                    color: #2980b9; 
                    font-weight: 600;
                }

                .bar-chart-container {
                    width: 100%; 
                    margin: 10px 0;
                    padding: 10px;
                    background: #fcfcfc;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }

                .chart-bar-item {
                    margin-bottom: 15px; 
                    font-size: 0.9em;
                    padding-left: 10px;
                }

                .bar-label {
                    display: block; 
                    font-weight: 600;
                    color: #0d162a; 
                    margin-bottom: 5px;
                }

                .bar-wrap {
                    width: 100%; 
                    height: 18px; 
                    background: #ecf0f1;
                    border-radius: 4px;
                    overflow: hidden;
                }

                .bar { 
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    padding-right: 5px;
                    color: white; 
                    font-size: 0.7em;
                    font-weight: 700;
                }
            </style>
        </head>
        <body>' . $contenido . '</body></html>';
    }

    /**
     * Obtener color para categoría
     * Devuelve un color específico para cada categoría de objeto.
     * /Recibe: $categoria (string)
     * /Devuelve: String color en formato hexadecimal
     */
    private function getColorForCategory($categoria) {
        $colores = [
            'Electronica' => '#4299e1',
            'Libros' => '#38b2ac',
            'Ropa' => '#ecc94b',
            'Calculadoras' => '#e53e3e',
            'Utiles' => '#805ad5',
            'Otro' => '#00e0ff'
        ];

        return $colores[$categoria] ?? '#ffc470';
    }
}
?>