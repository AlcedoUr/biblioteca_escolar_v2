<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="app">
    
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="reportes.php" class="text-decoration-none text-muted hover-dark"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h3 class="fw-bold mb-0" style="color: #8B1538;">Reporte de Uso de Biblioteca</h3>
                <p class="text-muted small mb-0">Estadísticas y tendencias de uso</p>
            </div>
        </div>
        <button @click="window.print()" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
            <i class="bi bi-printer me-2"></i>Imprimir Informe
        </button>
    </div>

    <!-- KPIS SUPERIORES -->
    <div class="row g-4 mb-4">
        <!-- Total Préstamos -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Total Préstamos</span>
                    <i class="bi bi-journal-bookmark text-danger"></i>
                </div>
                <h3 class="fw-bold mb-1 text-danger">{{ stats.kpis.total }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Histórico completo</small>
            </div>
        </div>
        <!-- Activos -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Préstamos Activos</span>
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1 text-primary">{{ stats.kpis.activos }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">En circulación</small>
            </div>
        </div>
        <!-- Tasa Devolución -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Tasa de Devolución</span>
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <h3 class="fw-bold mb-1 text-success">{{ stats.kpis.tasa }}%</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Libros devueltos</small>
            </div>
        </div>
        <!-- Promedio -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Promedio por Usuario</span>
                    <i class="bi bi-people text-warning"></i>
                </div>
                <h3 class="fw-bold mb-1 text-warning">{{ stats.kpis.promedio }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Préstamos por estudiante</small>
            </div>
        </div>
    </div>

    <!-- GRÁFICOS PRINCIPALES (FILA 1) -->
    <div class="row g-4 mb-4">
        <!-- Línea de Tendencia -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 text-dark">Tendencia de Préstamos</h6>
                    <div style="height: 300px;">
                        <canvas id="chartTendencia"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pastel de Categorías -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 text-dark">Distribución por Categoría</h6>
                    <div style="height: 300px; display: flex; justify-content: center;">
                        <canvas id="chartCategorias"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO BARRAS Y RECOMENDACIONES (FILA 2) -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 text-dark">Top 5 Libros Más Prestados</h6>
                    <div style="height: 250px;">
                        <canvas id="chartTop"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Análisis Automático -->
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-header bg-white border-0 pt-4 ps-4">
                    <h6 class="fw-bold">Análisis y Recomendaciones</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border d-flex align-items-center mb-2">
                        <i class="bi bi-graph-up text-primary me-3 fs-4"></i>
                        <div>
                            La biblioteca tiene una tasa de devolución del <strong>{{ stats.kpis.tasa }}%</strong>.
                            <span v-if="stats.kpis.tasa > 80">Excelente nivel de cumplimiento.</span>
                            <span v-else>Se recomienda enviar recordatorios a los alumnos.</span>
                        </div>
                    </div>
                    <div class="alert alert-light border d-flex align-items-center mb-2">
                        <i class="bi bi-book text-success me-3 fs-4"></i>
                        <div>
                            Hay <strong>{{ stats.kpis.activos }}</strong> préstamos activos actualmente que requieren seguimiento.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- Fin App -->
</div> <!-- Fin Wrapper -->

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                stats: {
                    kpis: { total: 0, activos: 0, tasa: 0, promedio: 0 },
                    tendencia: { labels: [], data: [] },
                    top_libros: { labels: [], data: [] },
                    categorias: []
                }
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const res = await fetch('../api/reportes.php?tipo=uso');
                    const data = await res.json();
                    this.stats = data;
                    this.renderCharts();
                } catch(e) { console.error(e); }
            },
            renderCharts() {
                // 1. GRÁFICO DE TENDENCIA (LÍNEA CURVA)
                new Chart(document.getElementById('chartTendencia'), {
                    type: 'line',
                    data: {
                        labels: this.stats.tendencia.labels,
                        datasets: [{
                            label: 'Préstamos Mensuales',
                            data: this.stats.tendencia.data,
                            borderColor: '#8B1538',
                            backgroundColor: 'rgba(139, 21, 56, 0.05)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#8B1538',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                // 2. GRÁFICO DE CATEGORÍAS (PASTEL)
                new Chart(document.getElementById('chartCategorias'), {
                    type: 'pie',
                    data: {
                        labels: ['Literatura', 'Matemáticas', 'Historia', 'Ciencias', 'Infantil'],
                        datasets: [{
                            data: this.stats.categorias,
                            backgroundColor: ['#8B1538', '#D4AF37', '#4CAF50', '#2196F3', '#FF9800'],
                            borderWidth: 0
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });

                // 3. GRÁFICO DE BARRAS (TOP LIBROS)
                new Chart(document.getElementById('chartTop'), {
                    type: 'bar',
                    data: {
                        labels: this.stats.top_libros.labels,
                        datasets: [{
                            label: 'Veces Prestado',
                            data: this.stats.top_libros.data,
                            backgroundColor: '#DcbE58',
                            borderRadius: 4,
                            barThickness: 30
                        }]
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
        }
    }).mount('#app')
</script>
</body>
</html>