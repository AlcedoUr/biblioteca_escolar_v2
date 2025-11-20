<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- Encabezado y Botón Nuevo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Préstamos</h3>
            <p class="text-muted">Administre los préstamos y devoluciones</p>
        </div>
        <a href="prestamo_lote.php" class="btn text-white px-4 py-2 fw-bold shadow-sm" style="background-color: #8B1538;">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Préstamo
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-2">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="input-group border-0 bg-white rounded px-2">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar por solicitante o código...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select v-model="filtroEstado" class="form-select border-0 bg-white shadow-none" style="cursor: pointer;">
                        <option value="TODOS">Todos los estados</option>
                        <option value="PENDIENTE">Activos</option>
                        <option value="VENCIDO">Vencidos</option>
                        <option value="FINALIZADO">Finalizados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN (ESTILO FIGMA) -->
    <div class="row g-4 mb-4">
        <!-- Activos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background-color: #E3F2FD; border-left: 4px solid #2196F3 !important;">
                <div class="card-body">
                    <h6 class="text-primary fw-bold mb-1">Préstamos Activos</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ contadores.activos }}</h3>
                </div>
            </div>
        </div>
        <!-- Vencidos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background-color: #FFEBEE; border-left: 4px solid #F44336 !important;">
                <div class="card-body">
                    <h6 class="text-danger fw-bold mb-1">Préstamos Vencidos</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ contadores.vencidos }}</h3>
                </div>
            </div>
        </div>
        <!-- Devoluciones -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background-color: #E8F5E9; border-left: 4px solid #4CAF50 !important;">
                <div class="card-body">
                    <h6 class="text-success fw-bold mb-1">Devoluciones (Histórico)</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ contadores.finalizados }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE PRÉSTAMOS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase">
                        <th class="ps-4">ID / Solicitante</th>
                        <th>Libros</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución</th>
                        <th>Estado</th>
                        <th>Atraso</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in prestamosFiltrados" :key="p.id">
                        <!-- ID y Solicitante -->
                        <td class="ps-4">
                            <div class="fw-bold text-dark">Prestamo #{{ p.id }}</div>
                            <div class="small text-muted">{{ p.solicitante }}</div>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ p.tipo_solicitante }}</span>
                        </td>

                        <!-- Cantidad de Libros -->
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                    <i class="bi bi-book text-secondary"></i>
                                </span>
                                <span class="fw-bold">{{ p.total_libros }}</span>
                                <span class="text-muted small ms-1">libros</span>
                            </div>
                        </td>

                        <!-- Fechas -->
                        <td class="text-muted small">{{ p.fecha_prestamo }}</td>
                        <td class="fw-bold text-dark small">{{ p.fecha_devolucion_pactada }}</td>

                        <!-- Estado (Badge Estilo Figma) -->
                        <td>
                            <span v-if="esVencido(p)" class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                Vencido
                            </span>
                            <span v-else-if="p.estado == 'PENDIENTE'" class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                Activo
                            </span>
                            <span v-else class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                Devuelto
                            </span>
                        </td>

                        <!-- Atraso (Cálculo de días) -->
                        <td>
                            <span v-if="esVencido(p)" class="text-danger fw-bold small">
                                {{ calcularAtraso(p.fecha_devolucion_pactada) }} días
                            </span>
                            <span v-else class="text-muted small">-</span>
                        </td>

                        <!-- Acciones -->
                        <td class="text-end pe-4">
                            <a :href="'detalle_prestamo.php?id=' + p.id" 
                               class="btn btn-sm fw-bold border hover-green"
                               :class="p.estado == 'PENDIENTE' ? 'btn-outline-success' : 'btn-light text-muted disabled'">
                                <i class="bi bi-check-circle me-1"></i> Devolver
                            </a>
                        </td>
                    </tr>
                    <tr v-if="prestamosFiltrados.length === 0">
                        <td colspan="7" class="text-center py-5 text-muted">
                            No se encontraron préstamos con estos filtros.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Cierre del wrapper -->
</div> 

<style>
    .hover-green:hover {
        background-color: #e8f5e9 !important;
        color: #198754 !important;
        border-color: #198754 !important;
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                prestamos: [],
                busqueda: '',
                filtroEstado: 'TODOS'
            }
        },
        computed: {
            contadores() {
                return {
                    activos: this.prestamos.filter(p => p.estado === 'PENDIENTE' && !this.esVencido(p)).length,
                    vencidos: this.prestamos.filter(p => this.esVencido(p)).length,
                    finalizados: this.prestamos.filter(p => p.estado === 'FINALIZADO').length
                }
            },
            prestamosFiltrados() {
                return this.prestamos.filter(p => {
                    // Filtro por texto
                    const textoMatch = 
                        p.solicitante.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                        p.id.toString().includes(this.busqueda);
                    
                    // Filtro por estado dropdown
                    let estadoMatch = true;
                    if (this.filtroEstado === 'PENDIENTE') estadoMatch = p.estado === 'PENDIENTE' && !this.esVencido(p);
                    else if (this.filtroEstado === 'VENCIDO') estadoMatch = this.esVencido(p);
                    else if (this.filtroEstado === 'FINALIZADO') estadoMatch = p.estado === 'FINALIZADO';
                    
                    return textoMatch && estadoMatch;
                });
            }
        },
        mounted() {
            this.cargarHistorial();
        },
        methods: {
            async cargarHistorial() {
                const res = await fetch('../api/historial.php');
                this.prestamos = await res.json();
            },
            esVencido(prestamo) {
                if (prestamo.estado !== 'PENDIENTE') return false;
                
                // Convertir fecha d/m/Y a objeto Date para comparar
                // Formato recibido de PHP es d/m/Y (ej: 20/11/2025)
                const partes = prestamo.fecha_devolucion_pactada.split('/');
                // Date(año, mes - 1, dia)
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();
                // Ponemos las horas a 0 para comparar solo fechas
                hoy.setHours(0,0,0,0);
                
                return fechaVence < hoy;
            },
            calcularAtraso(fechaStr) {
                const partes = fechaStr.split('/');
                const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();
                
                const diferenciaTiempo = hoy - fechaVence;
                const dias = Math.ceil(diferenciaTiempo / (1000 * 60 * 60 * 24));
                return dias > 0 ? dias : 0;
            }
        }
    }).mount('#app')
</script>
</body>
</html>