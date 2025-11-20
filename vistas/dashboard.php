<?php include 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="app">
    
    <div class="mb-4">
        <h3 class="fw-bold" style="color: #8B1538;">Dashboard</h3>
        <p class="text-muted">Resumen general de la biblioteca</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Total de Libros</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary p-2"><i class="bi bi-book"></i></span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ stats.total_libros }}</h3>
                    <small class="text-muted" style="font-size: 0.8rem;">En el inventario</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Libros Prestados</span>
                        <span class="badge bg-success bg-opacity-10 text-success p-2"><i class="bi bi-graph-up-arrow"></i></span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ stats.prestados }}</h3>
                    <small class="text-muted" style="font-size: 0.8rem;">Actualmente en préstamo</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Libros Extraviados</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning p-2"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ stats.extraviados }}</h3>
                    <small class="text-muted" style="font-size: 0.8rem;">Casos pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Préstamos Vencidos</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger p-2"><i class="bi bi-clock-history"></i></span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ stats.vencidos }}</h3>
                    <small class="text-muted" style="font-size: 0.8rem;">Requieren atención</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4">Préstamos por Mes</h6>
                    <div style="height: 250px;">
                        <canvas id="chartPrestamos"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4">Libros Más Prestados</h6>
                    <div style="height: 250px;">
                        <canvas id="chartTopLibros"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 ps-4">
            <h6 class="fw-bold"><i class="bi bi-calendar-event me-2"></i>Actividad Reciente</h6>
            <p class="text-muted small mb-0">Últimas transacciones registradas</p>
        </div>
        <div class="card-body">
            <div v-if="stats.actividades.length === 0" class="text-center text-muted py-3">
                No hay actividad reciente.
            </div>
            <div v-else class="list-group list-group-flush">
                <div v-for="(act, index) in stats.actividades" :key="index" class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-journal-bookmark text-secondary"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Préstamo: {{ act.titulo }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ act.nombres }} {{ act.apellidos }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted" style="font-size: 0.75rem;">{{ act.fecha_prestamo }}</div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" style="font-size: 0.7rem;">
                            {{ act.estado_devolucion == 'PENDIENTE' ? 'Activo' : 'Devuelto' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-dark mb-4">Usuarios Activos</h6>
                            <h3 class="fw-bold text-danger mb-1">{{ stats.estudiantes }}</h3>
                            <small class="text-muted">Estudiantes registrados</small>
                        </div>
                        <div class="text-warning display-6">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-dark mb-4">Libros Disponibles</h6>
                            <h3 class="fw-bold text-danger mb-1">{{ stats.disponibles }}</h3>
                            <small class="text-muted">Listos para préstamo</small>
                        </div>
                        <div class="text-success display-6">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-dark mb-4">Tasa de Uso</h6>
                            <h3 class="fw-bold text-danger mb-1">{{ stats.tasa_uso }}%</h3>
                            <small class="text-muted">Libros en circulación</small>
                        </div>
                        <div class="text-primary display-6">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> 
    </div>

</div>

</div> 

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                stats: {
                    total_libros: 0, 
            prestados: 0, 
            extraviados: 0, 
            vencidos: 0, 
            actividades: [],
            // AGREGA ESTAS 3:
            estudiantes: 0,
            disponibles: 0,
            tasa_uso: 0
                }
            }
        },
        mounted() {
            this.cargarDatos();
            this.iniciarGraficos();
        },
        methods: {
            async cargarDatos() {
                const res = await fetch('../api/dashboard_info.php');
                this.stats = await res.json();
            },
            iniciarGraficos() {
                // GRÁFICO 1: LÍNEA CURVA (Préstamos por mes)
                const ctx1 = document.getElementById('chartPrestamos');
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: ['Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre'],
                        datasets: [{
                            label: 'Préstamos',
                            data: [12, 15, 25, 32, 18, 28], // Datos simulados para visualización
                            borderColor: '#8B1538', // COLOR VINO
                            backgroundColor: 'rgba(139, 21, 56, 0.1)',
                            tension: 0.4, // Esto hace la curva suave
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                // GRÁFICO 2: BARRAS HORIZONTALES (Top Libros)
                const ctx2 = document.getElementById('chartTopLibros');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: ['Matemática', 'El Principito', 'Cien años...', 'Historia', 'Biología'],
                        datasets: [{
                            label: 'Veces prestado',
                            data: [45, 30, 20, 15, 10],
                            backgroundColor: '#DcbE58', // COLOR DORADO
                            borderRadius: 5
                        }]
                    },
                    options: { 
                        indexAxis: 'y', // Esto lo hace horizontal
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }
        
    }).mount('#app')
    
</script>
</body>
</html>