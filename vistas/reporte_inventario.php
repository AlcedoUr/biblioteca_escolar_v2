<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- CABECERA DE IMPRESIÓN (Solo visible en papel/PDF) -->
    <div class="d-none d-print-block text-center mb-4">
        <h3 class="fw-bold text-uppercase text-dark mb-0">I.E. Virgen de las Mercedes</h3>
        <p class="text-muted small">Sistema de Gestión Bibliotecaria - Reporte Oficial</p>
        <hr>
        <h4 class="fw-bold mt-3">Inventario General de Libros</h4>
        <small class="text-muted">Fecha de emisión: <?php echo date('d/m/Y H:i'); ?></small>
    </div>

    <!-- ENCABEZADO Y BOTONES (Ocultos al imprimir) -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="reportes.php" class="text-decoration-none text-muted hover-dark"><i class="bi bi-arrow-left"></i></a>
                <h3 class="fw-bold mb-0" style="color: #8B1538;">Reporte de Inventario</h3>
            </div>
            <p class="text-muted ms-4 mb-0">{{ librosFiltrados.length }} libros encontrados</p>
        </div>
        
        <!-- GRUPO DE BOTONES DE EXPORTACIÓN -->
        <div>
            <button @click="imprimirPDF" class="btn btn-outline-danger px-4 py-2 fw-bold shadow-sm me-2">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Imprimir PDF
            </button>
            <button @click="exportarCSV" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
                <i class="bi bi-filetype-csv me-2"></i>Exportar CSV
            </button>
        </div>
    </div>

    <!-- FILTROS (Ocultos al imprimir) -->
    <div class="card border-0 shadow-sm mb-4 bg-light d-print-none">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group border-0 bg-white rounded px-2 py-1">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar libro...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select v-model="filtroEstado" class="form-select border-0 bg-white py-2 shadow-none text-muted" style="cursor: pointer;">
                        <option value="TODOS">Todos los estados</option>
                        <option value="DISPONIBLE">Disponible</option>
                        <option value="AGOTADO">Agotado / Prestado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select border-0 bg-white py-2 shadow-none text-muted" disabled>
                        <option>Todas las categorías</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS KPI (Ocultas al imprimir para ahorrar tinta) -->
    <div class="row g-4 mb-4 d-print-none">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-2 small">Total de Libros</h6>
                    <h3 class="fw-bold text-dark mb-0" style="color: #8B1538 !important;">{{ stats.total }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #F0FDF4; border: 1px solid #DCFCE7;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2 text-success">Disponibles</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ stats.disponibles }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2 text-primary">Prestados</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ stats.prestados }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #FEF2F2; border: 1px solid #FEE2E2;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2 text-danger">Extraviados</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ stats.extraviados }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE DETALLE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-print">
                    <thead class="bg-light border-bottom">
                        <tr class="text-muted small fw-bold">
                            <th class="ps-4 py-3">Código</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Editorial</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th class="text-center pe-4">Copias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="libro in librosFiltrados" :key="libro.id">
                            <td class="ps-4 fw-bold text-dark">LIB-{{ String(libro.id).padStart(3, '0') }}</td>
                            <td class="fw-semibold text-dark">{{ libro.titulo }}</td>
                            <td class="text-muted small">{{ libro.autor }}</td>
                            <td class="text-muted small">{{ libro.editorial || '-' }}</td>
                            <td class="text-muted small">{{ libro.ubicacion }}</td>
                            
                            <!-- Badge Estado -->
                            <td>
                                <span v-if="libro.stock_disponible > 0" class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded border border-success print-badge">
                                    Disponible
                                </span>
                                <span v-else class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded border border-primary print-badge">
                                    Prestado
                                </span>
                            </td>

                            <td class="text-center fw-bold pe-4">
                                {{ libro.stock_disponible }} / {{ libro.stock_total }}
                            </td>
                        </tr>
                        <tr v-if="librosFiltrados.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">
                                No se encontraron libros con los filtros actuales.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pie de página de firma (Solo visible al imprimir) -->
    <div class="d-none d-print-block mt-5 pt-5">
        <div class="row text-center">
            <div class="col-6 offset-3 border-top border-dark">
                <p class="mb-0 pt-2">Firma y Sello del Responsable</p>
            </div>
        </div>
    </div>

</div> <!-- Cierre App -->
</div> <!-- Cierre Wrapper -->

<style>
    .hover-dark:hover { color: #333 !important; }
    
    /* ESTILOS DE IMPRESIÓN (PDF) */
    @media print {
        /* Ocultar elementos de navegación y UI */
        #sidebar, .topbar, .d-print-none { display: none !important; }
        
        /* Ajustar el contenido al ancho del papel */
        #content { margin: 0; padding: 0; width: 100%; }
        body { background-color: white; font-size: 12px; }
        
        /* Ajustar tabla para papel */
        .table-print th, .table-print td { padding: 5px !important; border: 1px solid #ddd !important; }
        
        /* Forzar colores de fondo en badges para que salgan en el PDF */
        .print-badge { 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        
        /* Ocultar sombras y bordes extra */
        .card { border: none !important; box-shadow: none !important; }
        .table-responsive { overflow: visible !important; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                busqueda: '',
                filtroEstado: 'TODOS',
                stats: { total: 0, disponibles: 0, prestados: 0, extraviados: 0 }
            }
        },
        computed: {
            librosFiltrados() {
                return this.libros.filter(l => {
                    const textoMatch = 
                        l.titulo.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                        l.autor.toLowerCase().includes(this.busqueda.toLowerCase());
                    
                    let estadoMatch = true;
                    if(this.filtroEstado === 'DISPONIBLE') estadoMatch = l.stock_disponible > 0;
                    if(this.filtroEstado === 'AGOTADO') estadoMatch = l.stock_disponible === 0;
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                const res = await fetch('../api/reportes.php?tipo=inventario');
                const data = await res.json();
                this.libros = data;
                this.calcularKPIs();
            },
            calcularKPIs() {
                this.stats.total = this.libros.reduce((sum, l) => sum + parseInt(l.stock_total), 0);
                this.stats.disponibles = this.libros.reduce((sum, l) => sum + parseInt(l.stock_disponible), 0);
                this.stats.prestados = this.stats.total - this.stats.disponibles; 
                this.stats.extraviados = 0; 
            },
            exportarCSV() {
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Codigo,Titulo,Autor,Editorial,Ubicacion,Estado,Stock\n";
                
                this.librosFiltrados.forEach(function(row) {
                    let estado = row.stock_disponible > 0 ? 'Disponible' : 'Prestado';
                    let fila = `LIB-${row.id},"${row.titulo}","${row.autor}","${row.editorial}","${row.ubicacion}",${estado},${row.stock_disponible}/${row.stock_total}`;
                    csvContent += fila + "\n";
                });

                var encodedUri = encodeURI(csvContent);
                var link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "reporte_inventario.csv");
                document.body.appendChild(link);
                link.click();
            },
            imprimirPDF() {
                // Simplemente llamamos a imprimir, los estilos CSS @media print 
                // se encargarán de ocultar los botones y el menú.
                window.print();
            }
        }
    }).mount('#app')
</script>
</body>
</html>