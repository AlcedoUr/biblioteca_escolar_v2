<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div class="d-flex align-items-center gap-2">
            <a href="reportes.php" class="text-decoration-none text-muted hover-dark"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h3 class="fw-bold mb-0" style="color: #8B1538;">Reporte de Uso</h3>
                <p class="text-muted small mb-0">Análisis detallado de circulación y comportamiento</p>
            </div>
        </div>
        <div>
            <button @click="imprimir" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
                <i class="bi bi-printer me-2"></i>Exportar PDF
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-light d-print-none">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Desde</label>
                    <input type="date" v-model="filtros.inicio" class="form-control border-0">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">Hasta</label>
                    <input type="date" v-model="filtros.fin" class="form-control border-0">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted">Categoría</label>
                    <select v-model="filtros.categoria" class="form-select border-0">
                        <option value="">Todas</option>
                        <option v-for="cat in listaCategorias" :value="cat">{{ cat }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted">Usuario</label>
                    <select v-model="filtros.rol" class="form-select border-0">
                        <option value="">Todos</option>
                        <option value="ESTUDIANTE">Estudiantes</option>
                        <option value="DOCENTE">Docentes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button @click="cargarDatos" class="btn btn-dark w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filtrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none d-print-block text-center mb-4">
        <h3 class="fw-bold text-uppercase mb-0">I.E. Virgen de las Mercedes</h3>
        <p class="mb-0">Informe de Gestión Bibliotecaria</p>
        <small class="text-muted">Periodo Analizado: {{ filtros.inicio }} al {{ filtros.fin }}</small>
        <div class="border-bottom border-dark w-50 mx-auto mt-2"></div>
    </div>

    <div class="alert alert-light border mb-4 shadow-sm">
        <h6 class="fw-bold text-dark"><i class="bi bi-file-earmark-text me-2"></i>Resumen Ejecutivo</h6>
        <p class="mb-0 small text-muted">
            Durante el periodo seleccionado, se registraron un total de <strong>{{ stats.kpis.total }} préstamos</strong>. 
            La tasa de devolución se sitúa en un <strong>{{ stats.kpis.tasa }}%</strong>, con un promedio de 
            <strong>{{ stats.kpis.promedio }} libros</strong> solicitados por usuario activo.
            <span v-if="analisis.topCategoria">La categoría más demandada fue <strong>{{ analisis.topCategoria }}</strong>.</span>
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-danger">
                <div class="text-muted small text-uppercase fw-bold">Total Préstamos</div>
                <h3 class="fw-bold mb-0 text-dark">{{ stats.kpis.total }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="text-muted small text-uppercase fw-bold">Activos Ahora</div>
                <h3 class="fw-bold mb-0 text-dark">{{ stats.kpis.activos }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-success">
                <div class="text-muted small text-uppercase fw-bold">Tasa Devolución</div>
                <h3 class="fw-bold mb-0 text-dark">{{ stats.kpis.tasa }}%</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="text-muted small text-uppercase fw-bold">Promedio/Usuario</div>
                <h3 class="fw-bold mb-0 text-dark">{{ stats.kpis.promedio }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 avoid-break">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-dark">Tendencia Mensual de Préstamos</h6>
                    <div style="height: 300px; position: relative;">
                        <canvas id="chartTendencia"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-dark">Distribución por Categoría</h6>
                    <div style="height: 250px; display: flex; justify-content: center;">
                        <canvas id="chartCategorias"></canvas>
                    </div>
                    <div class="mt-3 small overflow-auto" style="max-height: 100px;">
                        <div v-for="(cat, i) in stats.categorias" :key="i" class="d-flex justify-content-between border-bottom py-1">
                            <span><i class="bi bi-circle-fill me-2" :style="{color: coloresChart[i % coloresChart.length]}"></i>{{ cat.categoria }}</span>
                            <span class="fw-bold">{{ cat.cantidad }} ({{ calcularPorcentaje(cat.cantidad) }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 avoid-break">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center pt-3">
                    <h6 class="fw-bold mb-0 text-dark">Top 5 Libros Más Solicitados</h6>
                    <button @click="exportarTopCSV" class="btn btn-sm btn-outline-success d-print-none" title="Exportar CSV">
                        <i class="bi bi-filetype-csv"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="chartTop"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-bold text-dark"><i class="bi bi-lightbulb me-2 text-warning"></i>Análisis y Recomendaciones</h6>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        
                        <li class="list-group-item bg-transparent d-flex align-items-start px-0">
                            <i v-if="stats.kpis.tasa > 80" class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                            <i v-else-if="stats.kpis.tasa > 50" class="bi bi-exclamation-circle-fill text-warning me-2 mt-1"></i>
                            <i v-else class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                            <div>
                                <strong>Cumplimiento de Devoluciones:</strong>
                                <p class="mb-0 small text-muted">
                                    {{ stats.kpis.tasa > 80 ? 'Excelente nivel de responsabilidad de los usuarios.' : 'Se recomienda reforzar las políticas de devolución y enviar recordatorios.' }}
                                </p>
                            </div>
                        </li>

                        <li class="list-group-item bg-transparent d-flex align-items-start px-0" v-if="analisis.topCategoria">
                            <i class="bi bi-graph-up-arrow text-primary me-2 mt-1"></i>
                            <div>
                                <strong>Tendencia Temática:</strong>
                                <p class="mb-0 small text-muted">
                                    La categoría <b>{{ analisis.topCategoria }}</b> lidera el interés. Considerar adquirir más títulos relacionados.
                                </p>
                            </div>
                        </li>

                        <li class="list-group-item bg-transparent d-flex align-items-start px-0" v-if="stats.kpis.activos > 20">
                            <i class="bi bi-arrow-repeat text-info me-2 mt-1"></i>
                            <div>
                                <strong>Alta Rotación:</strong>
                                <p class="mb-0 small text-muted">
                                    Hay {{ stats.kpis.activos }} libros fuera actualmente. Verificar estado físico al retorno.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none d-print-block mt-5 pt-5">
        <div class="row text-center">
            <div class="col-6">
                <div class="border-top border-dark mx-5 pt-2">Firma del Responsable</div>
            </div>
            <div class="col-6">
                <div class="border-top border-dark mx-5 pt-2">V°B° Dirección</div>
            </div>
        </div>
    </div>

</div> <style>
    @media print {
        @page { size: A4; margin: 1cm; }
        body { background-color: white; -webkit-print-color-adjust: exact; }
        .d-print-none { display: none !important; }
        .card { border: 1px solid #ddd !important; break-inside: avoid; }
        .avoid-break { page-break-inside: avoid; }
        #sidebar, .topbar { display: none !important; }
        #content { margin: 0 !important; width: 100% !important; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                filtros: {
                    inicio: new Date(new Date().setMonth(new Date().getMonth() - 5)).toISOString().slice(0,10), // Hace 6 meses
                    fin: new Date().toISOString().slice(0,10),
                    categoria: '',
                    rol: ''
                },
                listaCategorias: [],
                
                stats: {
                    kpis: { total: 0, activos: 0, tasa: 0, promedio: 0 },
                    tendencia: { labels: [], prestamos: [], devueltos: [], vencidos: [] },
                    top_libros: [],
                    categorias: []
                },
                
                charts: {}, // Para guardar instancias y destruir
                coloresChart: ['#8B1538', '#D4AF37', '#2E7D32', '#1565C0', '#FF6F00', '#6A1B9A', '#00838F']
            }
        },
        computed: {
            analisis() {
                if(!this.stats.categorias.length) return {};
                // Encontrar categoría top
                const top = [...this.stats.categorias].sort((a,b) => b.cantidad - a.cantidad)[0];
                return { topCategoria: top ? top.categoria : '' };
            }
        },
        mounted() {
            this.cargarCategorias();
            this.cargarDatos();
        },
        methods: {
            async cargarCategorias() {
                const res = await fetch('../api/libros.php?get_categorias=true');
                this.listaCategorias = await res.json();
            },
            
            async cargarDatos() {
                try {
                    const params = new URLSearchParams({
                        tipo: 'uso',
                        fecha_inicio: this.filtros.inicio,
                        fecha_fin: this.filtros.fin,
                        categoria: this.filtros.categoria,
                        rol: this.filtros.rol
                    });
                    
                    const res = await fetch(`../api/reportes.php?${params}`);
                    const data = await res.json();
                    this.stats = data;
                    this.renderCharts();
                } catch(e) { console.error(e); }
            },

            calcularPorcentaje(val) {
                const total = this.stats.categorias.reduce((sum, c) => sum + parseInt(c.cantidad), 0);
                return total > 0 ? Math.round((val / total) * 100) : 0;
            },

            imprimir() { window.print(); },
            
            exportarTopCSV() {
                let csv = "Titulo,Categoria,Cantidad Prestamos\n";
                this.stats.top_libros.forEach(l => {
                    csv += `"${l.titulo}","${l.categoria}",${l.cantidad}\n`;
                });
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "top_libros.csv";
                link.click();
            },

            renderCharts() {
                // Destruir anteriores si existen para actualizar
                if(this.charts.tendencia) this.charts.tendencia.destroy();
                if(this.charts.categorias) this.charts.categorias.destroy();
                if(this.charts.top) this.charts.top.destroy();

                // 1. TENDENCIA (Con desglose en Tooltip)
                const ctx1 = document.getElementById('chartTendencia');
                this.charts.tendencia = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: this.stats.tendencia.labels,
                        datasets: [
                            {
                                label: 'Total Préstamos',
                                data: this.stats.tendencia.prestamos,
                                borderColor: '#8B1538',
                                backgroundColor: 'rgba(139, 21, 56, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    afterBody: (context) => {
                                        const idx = context[0].dataIndex;
                                        const dev = this.stats.tendencia.devueltos[idx];
                                        const ven = this.stats.tendencia.vencidos[idx];
                                        return `Devueltos: ${dev}\nVencidos: ${ven}`;
                                    }
                                }
                            }
                        }
                    }
                });

                // 2. CATEGORÍAS (Pie)
                const ctx2 = document.getElementById('chartCategorias');
                this.charts.categorias = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: this.stats.categorias.map(c => c.categoria),
                        datasets: [{
                            data: this.stats.categorias.map(c => c.cantidad),
                            backgroundColor: this.coloresChart
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } } // Usamos leyenda HTML personalizada
                    }
                });

                // 3. TOP LIBROS (Barras coloreadas por categoría)
                // Generar colores basados en categorías
                const catColors = {};
                this.stats.categorias.forEach((c, i) => catColors[c.categoria] = this.coloresChart[i % this.coloresChart.length]);
                
                const ctx3 = document.getElementById('chartTop');
                this.charts.top = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: this.stats.top_libros.map(l => l.titulo_corto),
                        datasets: [{
                            label: 'Préstamos',
                            data: this.stats.top_libros.map(l => l.cantidad),
                            backgroundColor: this.stats.top_libros.map(l => catColors[l.categoria] || '#ccc')
                        }]
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }
    }).mount('#app')
</script>