<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- CABECERA IMPRESIÓN (Fantasma) -->
    <div class="d-none d-print-block text-center mb-4">
        <h3 class="fw-bold text-uppercase text-dark mb-0">I.E. Virgen de las Mercedes</h3>
        <p class="text-muted small">Coordinación de Biblioteca - Control de Material</p>
        <hr>
        <h4 class="fw-bold mt-3 text-danger">LISTA DE USUARIOS CON LIBROS PENDIENTES</h4>
        <small class="text-muted">Fecha de corte: <?php echo date('d/m/Y H:i'); ?></small>
    </div>

    <!-- ENCABEZADO WEB -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="reportes.php" class="text-decoration-none text-muted hover-dark"><i class="bi bi-arrow-left"></i></a>
                <h3 class="fw-bold mb-0" style="color: #8B1538;">Reporte de Morosos</h3>
            </div>
            <p class="text-muted ms-4 mb-0">{{ deudoresFiltrados.length }} usuarios tienen deudas activas</p>
        </div>
        
        <div>
            <button @click="imprimirPDF" class="btn btn-outline-danger px-4 py-2 fw-bold shadow-sm me-2">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Imprimir Lista
            </button>
            <button @click="exportarCSV" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
                <i class="bi bi-filetype-csv me-2"></i>Exportar CSV
            </button>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm mb-4 bg-light d-print-none">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group border-0 bg-white rounded px-2 py-1">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar alumno o DNI...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select v-model="filtroTipo" class="form-select border-0 bg-white py-2 shadow-none text-muted" style="cursor: pointer;">
                        <option value="TODOS">Todos los tipos</option>
                        <option value="ESTUDIANTE">Solo Estudiantes</option>
                        <option value="DOCENTE">Solo Docentes</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS KPI DE DEUDA -->
    <div class="row g-4 mb-4 d-print-none">
        <!-- Total Deudores -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-x-fill text-danger fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Total Deudores</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.total_personas }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Libros por Recuperar -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-book-half text-warning fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Libros por Recuperar</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.total_libros }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mayor Deuda -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-dark bg-opacity-10 p-3 me-3">
                        <i class="bi bi-graph-up-arrow text-dark fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Mayor Deuda (Usuario)</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ stats.max_deuda }} <span class="fs-6 text-muted">libros</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE MOROSOS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-print">
                    <thead class="bg-light border-bottom">
                        <tr class="text-muted small fw-bold">
                            <th class="ps-4 py-3">DNI</th>
                            <th>Apellidos y Nombres</th>
                            <th>Tipo</th>
                            <th>Grado / Sección</th>
                            <th class="text-center text-danger">Deuda (Libros)</th>
                            <th class="text-center d-none d-print-table-cell">Firma / Obs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in deudoresFiltrados" :key="d.dni">
                            <td class="ps-4 fw-bold text-dark font-monospace">{{ d.dni }}</td>
                            <td class="fw-semibold text-dark">{{ d.nombre }}</td>
                            <td>
                                <span v-if="d.tipo == 'ESTUDIANTE'" class="badge bg-light text-primary border border-primary border-opacity-25">Estudiante</span>
                                <span v-else class="badge bg-light text-success border border-success border-opacity-25">Docente</span>
                            </td>
                            <td class="text-muted small">
                                <span v-if="d.grado">{{ d.grado }} - "{{ d.seccion }}"</span>
                                <span v-else>-</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6">{{ d.libros_pendientes }}</span>
                            </td>
                            <!-- Celda vacía para firma en papel -->
                            <td class="d-none d-print-table-cell border-start" style="height: 40px; width: 200px;"></td>
                        </tr>
                        <tr v-if="deudoresFiltrados.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                ¡Excelente! No hay usuarios morosos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Firma al pie (Solo impresión) -->
    <div class="d-none d-print-block mt-5 pt-5">
        <div class="row text-center">
            <div class="col-4 offset-1 border-top border-dark pt-2">Firma del Bibliotecario</div>
            <div class="col-4 offset-2 border-top border-dark pt-2">V°B° Dirección</div>
        </div>
    </div>

</div> <!-- Cierre App -->
</div> <!-- Cierre Wrapper -->

<style>
    .hover-dark:hover { color: #333 !important; }
    
    @media print {
        #sidebar, .topbar, .d-print-none { display: none !important; }
        #content { margin: 0; padding: 0; width: 100%; }
        body { background-color: white; font-size: 12px; }
        .table-print th, .table-print td { padding: 8px !important; border: 1px solid #000 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; background: none !important; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                deudores: [],
                busqueda: '',
                filtroTipo: 'TODOS',
                stats: { total_personas: 0, total_libros: 0, max_deuda: 0 }
            }
        },
        computed: {
            deudoresFiltrados() {
                return this.deudores.filter(d => {
                    const textoMatch = 
                        d.nombre.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                        d.dni.includes(this.busqueda);
                    
                    let tipoMatch = true;
                    if(this.filtroTipo !== 'TODOS') tipoMatch = d.tipo === this.filtroTipo;
                    
                    return textoMatch && tipoMatch;
                });
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                const res = await fetch('../api/reportes.php?tipo=deudores');
                const data = await res.json();
                this.deudores = data;
                this.calcularKPIs();
            },
            calcularKPIs() {
                this.stats.total_personas = this.deudores.length;
                this.stats.total_libros = this.deudores.reduce((sum, d) => sum + parseInt(d.libros_pendientes), 0);
                this.stats.max_deuda = Math.max(...this.deudores.map(d => parseInt(d.libros_pendientes)), 0);
            },
            exportarCSV() {
                let csv = "DNI,Nombre,Tipo,Grado,Seccion,Libros_Pendientes\n";
                this.deudoresFiltrados.forEach(d => {
                    csv += `"${d.dni}","${d.nombre}","${d.tipo}","${d.grado || ''}","${d.seccion || ''}",${d.libros_pendientes}\n`;
                });
                
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "reporte_morosos.csv";
                link.click();
            },
            imprimirPDF() {
                window.print();
            }
        }
    }).mount('#app')
</script>
</body>
</html>