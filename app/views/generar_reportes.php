<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header("Location: login.php?error=4"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reportes - Administrador</title>
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
        }

        .report-card.collapsed {
            padding: 20px;
        }

        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .report-header-left {
            display: flex;
            align-items: center;
        }

        .report-icon {
            font-size: 3rem;
            margin-right: 20px;
            opacity: 0.9;
        }

        .report-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .report-description {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .toggle-icon {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        .chart-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .chart-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            height: 400px;
        }

        .chart-container canvas {
            max-height: 350px !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .btn-download-pdf {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-download-pdf:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            color: #667eea;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .totales-generales {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .total-card {
            background: linear-gradient(135deg, #4299e1 0%, #38b2ac 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .total-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .total-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .total-card .label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .total-card.objetos {
            background: linear-gradient(135deg, #4299e1 0%, #38b2ac 100%);
        }

        .total-card.reuniones {
            background: linear-gradient(135deg, #805ad5 0%, #e53e3e 100%);
        }

        .total-card.alumnos {
            background: linear-gradient(135deg, #38b2ac 0%, #ecc94b 100%);
        }

        .total-card.lugares {
            background: linear-gradient(135deg, #ecc94b 0%, #ffc470 100%);
        }

        .report-card-2 {
            background: linear-gradient(135deg, #38b2ac 0%, #4299e1 100%);
        }

        .report-card-3 {
            background: linear-gradient(135deg, #805ad5 0%, #e53e3e 100%);
        }
    </style>
</head>
<body class="dashboard-body">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaAdmin"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=mostrarRegistro"><i class="fas fa-user-plus me-2"></i> Crear cuenta</a>
            <a href="index.php?action=user_consultar"><i class="fas fa-users me-2"></i> Consultar Alumnos</a>
            <a href="index.php?action=admin_consultar"><i class="fas fa-user-shield me-2"></i> Consultar Administradores</a>
            <a href="index.php?action=coord_consultar"><i class="fas fa-user-tie me-2"></i> Consultar Coordinadores</a>
            <a href="index.php?action=generar_reportes" class="active"><i class="fas fa-file-alt me-2"></i> Generar Reportes</a>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="dashboard-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; color: white;">
                        <i class="fas fa-chart-line me-2"></i>Panel de Reportes Estadísticos
                    </h1>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">Sistema de Trueques UPEMOR</p>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($nombre_usuario); ?></span><br>
                    <small style="opacity: 0.8;">Administrador del Sistema</small>
                </div>
            </div>
        </header>

        <main class="dashboard-content">
            <!-- TOTALES GENERALES -->
            <div class="totales-generales">
                <div class="total-card objetos">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <div class="value"><?php echo number_format($totalesGenerales['total_objetos'] ?? 0, 0, '', '.'); ?></div>
                    <div class="label">Objetos Registrados</div>
                </div>
                <div class="total-card reuniones">
                    <div class="icon"><i class="fas fa-handshake"></i></div>
                    <div class="value"><?php echo number_format($totalesGenerales['total_reuniones'] ?? 0, 0, '', '.'); ?></div>
                    <div class="label">Reuniones Agendadas</div>
                </div>
                <div class="total-card alumnos">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="value"><?php echo number_format($totalesGenerales['total_alumnos'] ?? 0, 0, '', '.'); ?></div>
                    <div class="label">Alumnos Registrados</div>
                </div>
                <div class="total-card lugares">
                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="value"><?php echo number_format($totalesGenerales['total_lugares'] ?? 0, 0, '', '.'); ?></div>
                    <div class="label">Lugares de Reunión</div>
                </div>
            </div>

            <div class="report-card" id="report-1" onclick="toggleReport('report-1')">
                <div class="report-header">
                    <div class="report-header-left">
                        <div class="report-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div>
                            <h2 class="report-title">Objetos por Categoría</h2>
                            <p class="report-description">Distribución de objetos publicados según su categoría</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>

                <div class="chart-content" id="content-1">
                    <div class="stats-grid">
                        <?php 
                        $totalObjetos = array_sum(array_column($objetosPorCategoria, 'total'));
                        foreach ($objetosPorCategoria as $cat): 
                            $porcentaje = ($totalObjetos > 0) ? round(($cat['total'] / $totalObjetos) * 100, 1) : 0;
                        ?>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo $cat['total']; ?></div>
                                <div class="stat-label"><?php echo htmlspecialchars($cat['categoria']); ?> (<?php echo $porcentaje; ?>%)</div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chart-container">
                        <canvas id="chartObjetosCategoria"></canvas>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php?action=ver_pdf&tipo=objetos_categoria" target="_blank" class="btn-download-pdf" onclick="event.stopPropagation()">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="report-card report-card-2" id="report-2" onclick="toggleReport('report-2')">
                <div class="report-header">
                    <div class="report-header-left">
                        <div class="report-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h2 class="report-title">Lugares de Reunión</h2>
                            <p class="report-description">Lugares más utilizados para realizar trueques</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>

                <div class="chart-content" id="content-2">
                    <div class="stats-grid">
                        <?php foreach ($lugaresReunion as $lugar): ?>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo $lugar['total_reuniones']; ?></div>
                                <div class="stat-label"><?php echo htmlspecialchars($lugar['lugar']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chart-container">
                        <canvas id="chartLugaresReunion"></canvas>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php?action=ver_pdf&tipo=lugares_reunion" target="_blank" class="btn-download-pdf" onclick="event.stopPropagation()">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="report-card report-card-3" id="report-3" onclick="toggleReport('report-3')">
                <div class="report-header">
                    <div class="report-header-left">
                        <div class="report-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h2 class="report-title">Estado de Reuniones</h2>
                            <p class="report-description">Estadísticas sobre confirmaciones, pendientes y cancelaciones</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>

                <div class="chart-content" id="content-3">
                    <div class="stats-grid">
                        <?php foreach ($estadoReuniones as $estado): ?>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo $estado['total']; ?></div>
                                <div class="stat-label">
                                    <?php 
                                    $estadoTexto = [
                                        'confirmada' => 'Confirmadas',
                                        'pendiente' => 'Pendientes',
                                        'cancelada' => 'Canceladas',
                                        'completada' => 'Completadas'
                                    ];
                                    echo $estadoTexto[$estado['estado_general']] ?? $estado['estado_general']; 
                                    ?> 
                                    (<?php echo $estado['porcentaje']; ?>%)
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chart-container">
                        <canvas id="chartEstadoReuniones"></canvas>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php?action=ver_pdf&tipo=estado_reuniones" target="_blank" class="btn-download-pdf" onclick="event.stopPropagation()">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    <script>
        let charts = {};

        function toggleReport(reportId) {
            const reportCard = document.getElementById(reportId);
            const content = document.getElementById('content-' + reportId.split('-')[1]);
            const toggleIcon = reportCard.querySelector('.toggle-icon');
            
            content.classList.toggle('active');
            toggleIcon.classList.toggle('rotated');
            reportCard.classList.toggle('collapsed');
            
            if (content.classList.contains('active')) {
                const chartNum = reportId.split('-')[1];
                initChart(chartNum);
            }
        }

        function initChart(num) {
            if (charts['chart' + num]) {
                return;
            }

            if (num === '1') {
                const ctx = document.getElementById('chartObjetosCategoria').getContext('2d');
                charts.chart1 = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($objetosPorCategoria, 'categoria')); ?>,
                        datasets: [{
                            label: 'Total de Objetos',
                            data: <?php echo json_encode(array_column($objetosPorCategoria, 'total')); ?>,
                            backgroundColor: [
                                'rgba(66, 153, 225, 0.8)',
                                'rgba(56, 178, 172, 0.8)',
                                'rgba(236, 201, 75, 0.8)',
                                'rgba(229, 62, 62, 0.8)',
                                'rgba(128, 90, 213, 0.8)',
                                'rgba(0, 224, 255, 0.8)'
                            ],
                            borderColor: [
                                'rgba(66, 153, 225, 1)',
                                'rgba(56, 178, 172, 1)',
                                'rgba(236, 201, 75, 1)',
                                'rgba(229, 62, 62, 1)',
                                'rgba(128, 90, 213, 1)',
                                'rgba(0, 224, 255, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Distribución de Objetos por Categoría',
                                font: { size: 16, weight: 'bold' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            } else if (num === '2') {
                const ctx = document.getElementById('chartLugaresReunion').getContext('2d');
                charts.chart2 = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($lugaresReunion, 'lugar')); ?>,
                        datasets: [{
                            label: 'Reuniones',
                            data: <?php echo json_encode(array_column($lugaresReunion, 'total_reuniones')); ?>,
                            backgroundColor: [
                                'rgba(66, 153, 225, 0.8)',
                                'rgba(56, 178, 172, 0.8)',
                                'rgba(236, 201, 75, 0.8)',
                                'rgba(229, 62, 62, 0.8)',
                                'rgba(128, 90, 213, 0.8)',
                                'rgba(255, 196, 112, 0.8)'
                            ],
                            borderWidth: 3,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Distribución de Lugares de Reunión',
                                font: { size: 16, weight: 'bold' }
                            }
                        }
                    }
                });
            } else if (num === '3') {
                const ctx = document.getElementById('chartEstadoReuniones').getContext('2d');
                
                const estadosData = <?php echo json_encode($estadoReuniones); ?>;
                const labels = [];
                const data = [];
                const colors = [];
                
                estadosData.forEach(item => {
                    const estadoTexto = {
                        'confirmada': 'Confirmadas',
                        'pendiente': 'Pendientes',
                        'cancelada': 'Canceladas',
                        'completada': 'Completadas'
                    };
                    labels.push(estadoTexto[item.estado_general] || item.estado_general);
                    data.push(item.total);
                    
                    const colorMap = {
                        'confirmada': 'rgba(56, 178, 172, 0.8)',
                        'pendiente': 'rgba(236, 201, 75, 0.8)',
                        'cancelada': 'rgba(229, 62, 62, 0.8)',
                        'completada': 'rgba(128, 90, 213, 0.8)'
                    };
                    colors.push(colorMap[item.estado_general] || 'rgba(100, 100, 100, 0.8)');
                });

                charts.chart3 = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Reuniones',
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 3,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Estado de las Reuniones',
                                font: { size: 16, weight: 'bold' }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>