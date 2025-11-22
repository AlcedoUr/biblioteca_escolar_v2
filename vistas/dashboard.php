<?php include 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Tablero de Control</h3>
            <p class="text-muted mb-0 small">Visión general del estado de la biblioteca</p>
        </div>
        <div class="d-none d-md-block text-end">
            <small class="text-muted fw-bold text-uppercase">Última actualización</small>
            <div class="fw-bold text-dark"><?php echo date('d/m/Y H:i'); ?></div>
        </div>
    </div>

    <div class="mb-5 animate-fade">
        <h6 class="fw-bold text-secondary text-uppercase small mb-3 border-bottom pb-2">
            <i class="bi bi-speedometer2 me-2"></i>Estado Operativo
        </h6>
        
        <div class="row g-3">
            
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100 hover-scale">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <i class="bi bi-collection text-primary bg-primary bg-opacity-10 p-2 rounded"></i>
                        </div>
                        <h4 class="fw-bold mb-0">{{ stats.total_libros }}</h4>
                        <small class="text-muted" style="font-size: 0.7rem;">Libros en inventario</small>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100 hover-scale">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <i class="bi bi-journal-check text-success bg-success bg-opacity-10 p-2 rounded"></i>
                        </div>
                        <h4 class="fw-bold mb-0">{{ stats.disponibles }}</h4>
                        <small class="text-muted" style="font-size: 0.7rem;">En estantería</small>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100 hover-scale">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <i class="bi bi-people text-info bg-info bg-opacity-10 p-2 rounded"></i>
                        </div>
                        <h4 class="fw-bold mb-0">{{ stats.estudiantes }}</h4>
                        <small class="text-muted" style="font-size: 0.7rem;">Lectores activos</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h6 class="text-muted small fw-bold mb-0">Tasa de Uso</h6>
                            <span class="badge rounded-pill" :class="claseSemaforo(stats.tasa_uso)">
                                {{ descripcionSemaforo(stats.tasa_uso) }}
                            </span>
                        </div>
                        <h3 class="fw-bold mb-1" :class="colorTextoTasa(stats.tasa_uso)">{{ stats.tasa_uso }}%</h3>
                        <small class="text-muted lh-1" style="font-size: 0.75rem;">
                            de los libros del inventario están actualmente prestados.
                        </small>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar" :class="colorBarraTasa(stats.tasa_uso)" :style="{ width: stats.tasa_uso + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-3" v-if="stats.vencidos > 0">
                <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-10 border-start border-danger border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3 class="fw-bold text-danger mb-0">{{ stats.vencidos }}</h3>
                                <div class="text-danger fw-bold small">Préstamos Vencidos</div>
                            </div>
                            <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                        </div>
                        <a href="historial.php?estado=VENCIDO" class="btn btn-sm btn-danger text-white fw-bold w-100 mt-2 shadow-sm">
                            Ver ahora <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-3" v-else-if="stats.extraviados > 0">
                 <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10 border-start border-warning border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3 class="fw-bold text-dark mb-0">{{ stats.extraviados }}</h3>
                                <div class="text-dark fw-bold small">Libros Extraviados</div>
                            </div>
                            <i class="bi bi-question-circle-fill text-warning fs-4"></i>
                        </div>
                        <a href="reportes.php?tipo=extraviados" class="btn btn-sm btn-warning text-dark fw-bold w-100 mt-2 shadow-sm">
                            Ir a seguimiento
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3" v-if="stats.vencidos === 0">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="card-body p-3 d-flex align-items-center justify-content-center text-center flex-column">
                        <div class="bg-white rounded-circle p-2 mb-2 shadow-sm text-success">
                            <i class="bi bi-check-lg fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-success mb-1">¡Todo en orden!</h6>
                        <small class="text-success opacity-75" style="font-size: 0.75rem;">
                            No hay préstamos vencidos<br>ni casos pendientes de revisión.
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row g-4 animate-fade" style="animation-delay: 0.1s;">
        
        <div class="col-lg-8">
            <h6 class="fw-bold text-secondary text-uppercase small mb-3 border-bottom pb-2">
                <i class="bi bi-graph-up-arrow me-2"></i>Análisis y Tendencias
            </h6>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">Flujo de Préstamos (Últimos 6 meses)</h6>
                        <span class="badge bg-light text-muted border">Mensual</span>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="chartPrestamos"></canvas>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">Libros Más Solicitados</h6>
                        <small class="text-muted fst-italic"><i class="bi bi-hand-index-thumb me-1"></i>Clic en la barra para ver detalle</small>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="chartTopLibros"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <h6 class="fw-bold text-secondary text-uppercase small mb-3 border-bottom pb-2">
                <i class="bi bi-clock-history me-2"></i>Bitácora
            </h6>

            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Actividad Reciente</h6>
                </div>
                <div class="card-body p-0">
                    <div v-if="stats.actividades.length === 0" class="text-center py-5 text-muted opacity-50">
                        <i class="bi bi-pause-circle display-4"></i>
                        <p class="mt-2 small">Sin movimientos recientes.</p>
                    </div>

                    <div class="list-group list-group-flush">
                        <a v-for="(act, index) in stats.actividades" 
                           :key="index" 
                           :href="'detalle_prestamo.php?id=' + act.id_prestamo" 
                           class="list-group-item list-group-item-action py-3 px-4 border-bottom-0 border-top d-flex gap-3 align-items-start">
                            
                            <div class="mt-1">
                                <div v-if="act.estado_devolucion === 'PENDIENTE'" 
                                     class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;"
                                     title="Préstamo Activo">
                                    <i class="bi bi-arrow-up-right"></i>
                                </div>
                                <div v-else 
                                     class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" 
                                     style="width: 32px; height: 32px;"
                                     title="Devolución / Finalizado">
                                    <i class="bi bi-arrow-down-left"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span v-if="act.estado_devolucion === 'PENDIENTE'" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.6rem;">PRÉSTAMO</span>
                                    <span v-else class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.6rem;">DEVOLUCIÓN</span>
                                    
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ act.fecha_corta }}</small>
                                </div>
                                
                                <h6 class="fw-bold text-dark mb-0 line-clamp-1" style="font-size: 0.9rem;">{{ act.titulo }}</h6>
                                <div class="small text-muted">{{ act.nombres }} {{ act.apellidos }}</div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-white text-center py-3">
                    <a href="historial.php" class="btn btn-light btn-sm text-muted fw-bold w-100">Ver todo el historial</a>
                </div>
            </div>
        </div>

    </div>

</div> </div> <style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .animate-fade { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                stats: {
                    total_libros: 0, prestados: 0, extraviados: 0, vencidos: 0, 
                    estudiantes: 0, disponibles: 0, tasa_uso: 0, actividades: []
                }
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const res = await fetch('../api/dashboard_info.php');
                    const data = await res.json();
                    
                    // Formatear fechas de actividades para UX
                    if(data.actividades) {
                        data.actividades.forEach(a => {
                            // Extraer ID Prestamo si no viene (simulado con la estructura actual)
                            // Necesitamos asegurar que el API devuelva id_prestamo
                            // Para el formato de fecha corto: "21/11 19:26"
                            const f = new Date(a.fecha_prestamo);
                            const dia = String(f.getDate()).padStart(2, '0');
                            const mes = String(f.getMonth()+1).padStart(2, '0');
                            const hora = f.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            a.fecha_corta = `${dia}/${mes} ${hora}`;
                        });
                    }
                    
                    this.stats = data;
                    this.iniciarGraficos();
                } catch(e) { console.error(e); }
            },
            
            // Lógica de Semáforo Visual
            claseSemaforo(valor) {
                if (valor < 20) return 'bg-success bg-opacity-10 text-success border border-success'; // Baja circulación (Inventario sano/quieto) - OJO: El prompt dice <20 baja
                if (valor < 60) return 'bg-warning bg-opacity-10 text-dark border border-warning'; // Media
                return 'bg-danger bg-opacity-10 text-danger border border-danger'; // Alta circulación (Puede faltar stock)
            },
            descripcionSemaforo(valor) {
                if (valor < 20) return 'Baja Circulación';
                if (valor < 60) return 'Circulación Media';
                return 'Alta Demanda';
            },
            colorTextoTasa(valor) {
                if (valor < 20) return 'text-success';
                if (valor < 60) return 'text-dark';
                return 'text-danger';
            },
            colorBarraTasa(valor) {
                if (valor < 20) return 'bg-success';
                if (valor < 60) return 'bg-warning';
                return 'bg-danger';
            },

            iniciarGraficos() {
                // GRÁFICO 1: TENDENCIA (Con Tooltips mejorados)
                const ctx1 = document.getElementById('chartPrestamos');
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: ['Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov'],
                        datasets: [{
                            label: 'Préstamos',
                            data: [12, 19, 3, 5, 2, 3], // Datos simulados (idealmente vendrían del API)
                            borderColor: '#8B1538',
                            backgroundColor: 'rgba(139, 21, 56, 0.05)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                padding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.raw + ' préstamos realizados';
                                    }
                                }
                            }
                        },
                        scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } } }
                    }
                });

                // GRÁFICO 2: BARRAS CLICABLES
                const ctx2 = document.getElementById('chartTopLibros');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: ['Matemática', 'Historia', 'Biología', 'Lenguaje', 'Cívica'],
                        datasets: [{
                            label: 'Solicitudes',
                            data: [12, 19, 3, 5, 2],
                            backgroundColor: '#DcbE58',
                            borderRadius: 4,
                            barPercentage: 0.6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        // INTERACTIVIDAD: CLIC EN BARRA
                        onClick: (e, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const label = e.chart.data.labels[index];
                                // Redirigir al catálogo buscando ese libro
                                window.location.href = `libros.php?q=${encodeURIComponent(label)}`;
                            }
                        },
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        }
                    }
                });
            }
        }
    }).mount('#app')
</script>
</body>
</html>