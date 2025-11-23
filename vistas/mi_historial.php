<?php include 'includes/header.php'; ?>

<!-- Incluimos la librería Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div id="app" class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Mi Historial y Reputación</h3>
            <p class="text-muted">Aquí puedes ver tus estadísticas de uso y tu historial de préstamos.</p>
        </div>
    </div>

    <!-- Sección de Estadísticas y Score -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted small text-uppercase">Score de Reputación</h6>
                    <h1 class="display-4 fw-bold" :style="{ color: scoreColor }">{{ estadisticas.score_reputacion }}%</h1>
                    <p class="small text-muted mb-0">Tu fiabilidad como lector. ¡Sigue así!</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-7">
                             <h6 class="text-muted small text-uppercase mb-3">Comportamiento</h6>
                            <canvas id="comportamientoChart"></canvas>
                        </div>
                        <div class="col-md-5 border-start">
                            <h6 class="text-muted small text-uppercase mb-3">Estadísticas Clave</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-book-fill text-primary me-2"></i> <strong>Libro Favorito:</strong><br><span class="text-dark">{{ estadisticas.libro_favorito }}</span></li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>A tiempo:</strong> {{ estadisticas.a_tiempo }}</li>
                                <li class="mb-2"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> <strong>Vencidos:</strong> {{ estadisticas.vencidos }}</li>
                                <li class="mb-2"><i class="bi bi-x-circle-fill text-warning me-2"></i> <strong>Reservas Canceladas:</strong> {{ estadisticas.reservas_canceladas }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Historial de Préstamos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="m-0 fw-bold small text-uppercase text-muted">Historial de Préstamos ({{ historial.length }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Libro</th>
                            <th>Autor</th>
                            <th>Fecha Préstamo</th>
                            <th>Fecha Devolución Estimada</th>
                            <th>Fecha Devolución Real</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in historial" :key="item.id">
                            <td><span class="fw-bold">{{ item.titulo }}</span></td>
                            <td>{{ item.autor }}</td>
                            <td class="font-monospace">{{ formatDate(item.fecha_prestamo) }}</td>
                            <td class="font-monospace">{{ formatDate(item.fecha_devolucion_estimada) }}</td>
                            <td class="font-monospace">{{ formatDate(item.fecha_devolucion_real) || '-' }}</td>
                            <td class="text-center">
                                <span v-if="item.estado_prestamo == 'DEVUELTO'" class="badge rounded-pill bg-success bg-opacity-25 text-success border border-success border-opacity-25">Devuelto</span>
                                <span v-else-if="item.estado_prestamo == 'PRESTADO' && isVencido(item.fecha_devolucion_estimada)" class="badge rounded-pill bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25">Vencido</span>
                                <span v-else class="badge rounded-pill bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">En Préstamo</span>
                            </td>
                        </tr>
                         <tr v-if="historial.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history display-4 d-block mb-2 opacity-25"></i>
                                Aún no tienes historial de préstamos.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const { createApp, ref, onMounted, computed } = Vue

    createApp({
        setup() {
            const historial = ref([]);
            const estadisticas = ref({
                libro_favorito: '',
                total_prestamos: 0,
                vencidos: 0,
                a_tiempo: 0,
                reservas_canceladas: 0,
                score_reputacion: 100
            });
            let chart = null;

            onMounted(async () => {
                try {
                    const res = await fetch('../api/mi_historial.php');
                    const data = await res.json();
                    if(data.exito) {
                        historial.value = data.historial;
                        estadisticas.value = data.estadisticas;
                        renderChart();
                    } else {
                         Swal.fire('Error', data.mensaje, 'error');
                    }
                } catch (e) {
                    console.error("Error al cargar datos:", e);
                    Swal.fire('Error de Conexión', 'No se pudo conectar con el servidor para obtener tu historial.', 'error');
                }
            });

            const scoreColor = computed(() => {
                const score = estadisticas.value.score_reputacion;
                if (score >= 80) return '#198754'; // Verde
                if (score >= 50) return '#ffc107'; // Amarillo
                return '#dc3545'; // Rojo
            });
            
            const formatDate = (dateString) => {
                if (!dateString) return null;
                const date = new Date(dateString);
                // Ajustar por la zona horaria para evitar desfase de un día
                const userTimezoneOffset = date.getTimezoneOffset() * 60000;
                const adjustedDate = new Date(date.getTime() + userTimezoneOffset);
                return adjustedDate.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
            };

            const isVencido = (fechaEstimada) => {
                return new Date() > new Date(fechaEstimada);
            };

            const renderChart = () => {
                const ctx = document.getElementById('comportamientoChart').getContext('2d');
                if(chart) {
                    chart.destroy();
                }
                chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['A tiempo', 'Vencidos', 'Cancelados'],
                        datasets: [{
                            data: [
                                estadisticas.value.a_tiempo,
                                estadisticas.value.vencidos,
                                estadisticas.value.reservas_canceladas
                            ],
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.7)',  // Verde
                                'rgba(220, 53, 69, 0.7)', // Rojo
                                'rgba(255, 193, 7, 0.7)'  // Amarillo
                            ],
                            borderColor: [
                                'rgba(25, 135, 84, 1)',
                                'rgba(220, 53, 69, 1)',
                                'rgba(255, 193, 7, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                 callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.raw;
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            };

            return {
                historial,
                estadisticas,
                scoreColor,
                formatDate,
                isVencido
            };
        }
    }).mount('#app');
</script>
