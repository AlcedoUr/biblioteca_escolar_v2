<?php include 'includes/header.php'; ?>

<div id="app">

    <!-- SECCIÓN VISIBLE EN PANTALLA -->
    <div class="d-print-none">
        
        <!-- Título -->
        <div class="mb-5">
            <h3 class="fw-bold" style="color: #8B1538;">Reportes</h3>
            <p class="text-muted">Genere reportes detallados sobre la gestión de la biblioteca</p>
        </div>

        <!-- TARJETAS PRINCIPALES -->
        <div class="row g-4 mb-5">
            
            <!-- Tarjeta 1: Inventario (Azul) -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-2 hover-up">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-4">
                            <div class="rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #E3F2FD;">
                                <i class="bi bi-file-text text-primary fs-4"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Reporte de Inventario</h5>
                        <p class="text-muted small mb-4 flex-grow-1">Lista completa de libros con estado, ubicación y disponibilidad actual en el sistema.</p>
                        
                        <a href="reporte_inventario.php" class="btn text-white w-100 py-2 fw-bold shadow-sm" 
   style="background-color: #8B1538;">
    Ver Reporte
</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Morosos/Deudores (Rojo) -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-2 hover-up">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-4">
                            <div class="rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #FFEBEE;">
                                <i class="bi bi-people text-danger fs-4"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Reporte de Morosos</h5>
                        <p class="text-muted small mb-4 flex-grow-1">Usuarios con préstamos vencidos, detalles de atrasos y material pendiente de devolución.</p>
                        
                       <a href="reporte_morosos.php" class="btn text-white w-100 py-2 fw-bold shadow-sm" 
   style="background-color: #8B1538;">
    Ver Reporte
</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Extraviados/Uso (Verde) -->
            <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 p-2 hover-up">
        <div class="card-body d-flex flex-column">
            <div class="mb-4">
                <div class="rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #E8F5E9;">
                    <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                </div>
            </div>
            <h5 class="fw-bold mb-2 text-dark">Reporte de Uso</h5>
            <p class="text-muted small mb-4 flex-grow-1">Estadísticas de uso, tendencias de préstamos y análisis de rendimiento.</p>
            
            <a href="reporte_uso.php" class="btn text-white w-100 py-2 fw-bold shadow-sm" 
               style="background-color: #8B1538;">
                Ver Reporte
            </a>
        </div>
    </div>
</div>
        </div>

        <!-- SECCIÓN INFERIOR DE CARACTERÍSTICAS -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 text-dark">Características de los Reportes</h6>
                <ul class="list-unstyled text-muted small mb-0" style="line-height: 2;">
                    <li><i class="bi bi-dot fs-3 text-danger align-middle me-1"></i> Generación automática en formato PDF listo para imprimir.</li>
                    <li><i class="bi bi-dot fs-3 text-danger align-middle me-1"></i> Datos actualizados en tiempo real desde el sistema de gestión.</li>
                    <li><i class="bi bi-dot fs-3 text-danger align-middle me-1"></i> Formatos compatibles con requerimientos de UGEL y Dirección.</li>
                    <li><i class="bi bi-dot fs-3 text-danger align-middle me-1"></i> Incluye espacios para firma y sello del responsable de biblioteca.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- SECCIÓN OCULTA DE IMPRESIÓN (ESTA SE USA AL DAR CLIC) -->
    <div id="areaImpresion" class="d-none d-print-block mt-4">
        
        <!-- Cabecera del Documento Oficial -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase mb-0">I.E. Virgen de las Mercedes</h2>
            <p class="text-muted">Sistema de Gestión Bibliotecaria - N° 3054</p>
            <hr>
            <h4 class="fw-bold mt-3">{{ tituloReporte }}</h4>
            <small>Fecha de Emisión: <?php echo date('d/m/Y H:i'); ?></small>
        </div>

        <!-- Tabla Dinámica -->
        <table class="table table-bordered border-dark">
            <thead class="table-light">
                <!-- Cabeceras según tipo -->
                <tr v-if="tipoReporte == 'inventario'">
                    <th>Código</th><th>Título del Libro</th><th>Autor</th><th>Ubicación</th><th class="text-center">Stock</th>
                </tr>
                <tr v-if="tipoReporte == 'extraviados'">
                    <th>Fecha</th><th>Libro</th><th>Responsable</th><th>Estado</th>
                </tr>
                <tr v-if="tipoReporte == 'deudores'">
                    <th>DNI</th><th>Estudiante / Docente</th><th class="text-center">Libros sin devolver</th><th>Firma Conformidad</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in datosReporte">
                    <!-- Celdas Inventario -->
                    <template v-if="tipoReporte == 'inventario'">
                        <td>LIB-{{ item.id }}</td><td>{{ item.titulo }}</td><td>{{ item.autor }}</td><td>{{ item.ubicacion }}</td>
                        <td class="text-center">{{ item.stock_disponible }} / {{ item.stock_total }}</td>
                    </template>

                    <!-- Celdas Extraviados -->
                    <template v-if="tipoReporte == 'extraviados'">
                        <td>{{ item.fecha_prestamo }}</td><td>{{ item.titulo }}</td><td>{{ item.responsable }}</td>
                        <td class="fw-bold text-danger">{{ item.estado_devolucion }}</td>
                    </template>

                    <!-- Celdas Deudores -->
                    <template v-if="tipoReporte == 'deudores'">
                        <td>{{ item.dni }}</td><td>{{ item.nombre }}</td>
                        <td class="text-center fw-bold text-danger">{{ item.libros_pendientes }}</td>
                        <td>_____________</td>
                    </template>
                </tr>
            </tbody>
        </table>

        <!-- Pie de página de firma -->
        <div class="mt-5 pt-5 text-center row">
            <div class="col-6 offset-3 border-top border-dark">
                <p class="mb-0 fw-bold">Firma del Bibliotecario Responsable</p>
            </div>
        </div>
    </div>

</div>
</div> <!-- Cierre Wrapper -->

<style>
    .hover-up { transition: transform 0.2s; }
    .hover-up:hover { transform: translateY(-5px); }
    
    @media print {
        #sidebar, .topbar, .d-print-none { display: none !important; }
        #content { margin: 0; padding: 0; width: 100%; }
        body { background-color: white; }
        #areaImpresion { display: block !important; }
        /* Forzar impresión de fondos/colores si es necesario */
        * { -webkit-print-color-adjust: exact; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                tipoReporte: '',
                tituloReporte: '',
                datosReporte: []
            }
        },
        methods: {
            async generarReporte(tipo) {
                try {
                    const res = await fetch(`../api/reportes.php?tipo=${tipo}`);
                    this.datosReporte = await res.json();
                    
                    this.tipoReporte = tipo;
                    if(tipo == 'inventario') this.tituloReporte = 'Reporte General de Inventario';
                    if(tipo == 'extraviados') this.tituloReporte = 'Informe de Incidencias y Pérdidas';
                    if(tipo == 'deudores') this.tituloReporte = 'Lista de Usuarios Morosos';

                    setTimeout(() => { window.print(); }, 500);
                } catch (e) {
                    alert("Error al generar reporte");
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>