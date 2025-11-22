<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-none d-print-block">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold text-uppercase mb-0" style="font-family: serif; font-size: 16pt; color: black;">I.E. Virgen de las Mercedes</h3>
            <p class="mb-0 small text-muted">Biblioteca Escolar - Reporte de Situación de Deudas</p>
            <div class="mt-2 border-top border-dark w-50 mx-auto"></div>
        </div>

        <div class="row small mb-3" style="font-family: Arial, sans-serif; color: black;">
            <div class="col-6">
                <strong>FECHA EMISIÓN:</strong> <?php echo date('d/m/Y H:i'); ?><br>
                <strong>FILTRO APLICADO:</strong> {{ filtroTipoUsuario === 'TODOS' ? 'Todos los usuarios' : filtroTipoUsuario }}
            </div>
            <div class="col-6 text-end">
                <strong>TOTAL CASOS:</strong> {{ deudoresFiltrados.length }}<br>
                <strong>LIBROS PENDIENTES:</strong> {{ stats.total_pendientes }}
            </div>
        </div>

        <table class="table table-sm table-bordered border-dark w-100" style="font-size: 9pt; font-family: Arial, sans-serif; color: black;">
            <thead>
                <tr style="background-color: #e9ecef;">
                    <th style="width: 10%;">DNI</th>
                    <th style="width: 25%;">APELLIDOS Y NOMBRES</th>
                    <th style="width: 12%;">GRADO</th>
                    <th style="width: 8%;" class="text-center">CANT.</th>
                    <th style="width: 15%;">TIPO DEUDA</th>
                    <th style="width: 15%;">CAUSANTE</th> <th style="width: 15%;">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="d in deudoresFiltrados" :key="d.id">
                    <td class="align-middle">{{ d.dni }}</td>
                    <td class="align-middle fw-bold text-uppercase">{{ d.nombre }}</td>
                    <td class="align-middle">
                        <span v-if="d.tipo == 'ESTUDIANTE'">{{ d.grado }} "{{ d.seccion }}"</span>
                        <span v-else>DOCENTE</span>
                    </td>
                    <td class="align-middle text-center fw-bold">{{ d.total_deuda }}</td>
                    <td class="align-middle">{{ d.tipo_deuda }}</td>
                    
                    <td class="align-middle">
                        <span v-if="d.causantes_externos" class="fw-bold">{{ d.causantes_externos }}</span>
                        <span v-else class="text-muted small" style="font-style: italic;">-</span>
                    </td>
                    
                    <td class="align-middle small">{{ d.observacion_texto }}</td>
                </tr>
            </tbody>
        </table>

        <div class="row text-center" style="margin-top: 80px; color: black;">
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-4">Bibliotecario Responsable</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-4">V°B° Dirección</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-4">Fecha de Revisión</div>
            </div>
        </div>
    </div>

    <div class="d-print-none">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0" style="color: #8B1538;">Reporte de Deudores</h3>
                <p class="text-muted mb-0 small">Gestión de préstamos vencidos e incidencias pendientes.</p>
            </div>
            
            <div class="d-flex gap-2">
                <button @click="imprimirPDF" class="btn text-white fw-bold shadow-sm" style="background-color: #8B1538;">
                    <i class="bi bi-printer-fill me-2"></i>Imprimir PDF
                </button>
                <button @click="exportarCSV" class="btn btn-success fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Excel
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group bg-white rounded border">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar solicitante...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filtroTipoUsuario" class="form-select border-0 shadow-none bg-white cursor-pointer">
                            <option value="TODOS">Todos los usuarios</option>
                            <option value="ESTUDIANTE">Estudiantes</option>
                            <option value="DOCENTE">Docentes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr class="text-muted small fw-bold text-uppercase">
                                <th class="ps-4 py-3">Usuario</th>
                                <th>Tipo / Ubicación</th>
                                
                                <th class="text-center text-warning bg-warning bg-opacity-10 border-start">Vencidos</th>
                                <th class="text-center text-danger bg-danger bg-opacity-10">Extraviados</th>
                                <th class="text-center text-dark bg-secondary bg-opacity-10 border-end">Dañados</th>
                                
                                <th class="text-center fw-bold">Total</th>
                                <th>Antigüedad</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in deudoresFiltrados" :key="d.id">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ d.nombre }}</div>
                                    <div class="small text-muted font-monospace">{{ d.dni }}</div>
                                </td>
                                <td class="small">
                                    <span v-if="d.tipo == 'ESTUDIANTE'" class="text-primary"><i class="bi bi-backpack me-1"></i>{{ d.grado }} "{{ d.seccion }}"</span>
                                    <span v-else class="text-success"><i class="bi bi-person-badge me-1"></i>Docente</span>
                                </td>

                                <td class="text-center bg-warning bg-opacity-10 border-start fw-bold text-warning">
                                    {{ d.cant_vencidos > 0 ? d.cant_vencidos : '-' }}
                                </td>
                                <td class="text-center bg-danger bg-opacity-10 fw-bold text-danger">
                                    {{ d.cant_extravios > 0 ? d.cant_extravios : '-' }}
                                </td>
                                <td class="text-center bg-secondary bg-opacity-10 border-end fw-bold text-dark">
                                    {{ d.cant_danios > 0 ? d.cant_danios : '-' }}
                                </td>

                                <td class="text-center">
                                    <span class="badge rounded-pill px-3" :class="d.total_deuda >= 3 ? 'bg-danger' : 'bg-secondary'">{{ d.total_deuda }}</span>
                                </td>

                                <td>
                                    <div v-if="d.dias_retraso > 7" class="text-danger fw-bold small"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ d.dias_retraso }} días</div>
                                    <div v-else class="text-muted small">{{ d.dias_retraso }} días</div>
                                </td>

                                <td class="text-end pe-4">
                                    <button @click="verDetalle(d)" 
                                            class="btn btn-sm btn-light border text-primary shadow-sm" 
                                            title="Ver detalle de libros">
                                        <i class="bi bi-eye-fill me-1"></i>Detalles
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="deudoresFiltrados.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">No hay deudores con estos filtros.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold text-danger">Detalle de Deuda</h5>
                        <p class="mb-0 text-muted small">Usuario: <strong>{{ modal.usuario.nombre }}</strong></p>
                    </div>
                    <button type="button" class="btn-close" @click="modal.visible = false"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light small text-muted">
                                <tr>
                                    <th class="ps-4">Libro</th>
                                    <th>Estado</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Responsable Real</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in modal.items" :key="i">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ item.titulo }}</div>
                                        <small class="text-muted font-monospace">{{ item.isbn }}</small>
                                    </td>
                                    <td>
                                        <span v-if="item.motivo == 'Vencido'" class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Vencido</span>
                                        <span v-else-if="item.motivo == 'Perdido'" class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Extraviado</span>
                                        <span v-else class="badge bg-secondary"><i class="bi bi-bandaid me-1"></i> Dañado</span>
                                    </td>
                                    <td class="small text-muted">{{ item.fecha_vence }}</td>
                                    
                                    <td>
                                        <div v-if="item.responsable_real !== 'El Solicitante'" class="d-flex align-items-center text-danger fw-bold bg-danger bg-opacity-10 p-1 rounded border border-danger border-opacity-25" style="width: fit-content;">
                                            <i class="bi bi-person-exclamation me-2"></i> {{ item.responsable_real }}
                                        </div>
                                        <div v-else class="text-muted small fst-italic">
                                            <i class="bi bi-person me-1"></i> El mismo usuario
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button class="btn btn-secondary btn-sm" @click="modal.visible = false">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Estilos exclusivos para impresión */
    @media print {
        @page { size: A4; margin: 1cm; }
        body { background: white; font-family: 'Arial', sans-serif; color: black !important; }
        
        /* Ocultar elementos web */
        #sidebar, .topbar, .d-print-none { display: none !important; }
        
        /* Resetear layout */
        #content { margin: 0; padding: 0; width: 100%; }
        .modal { display: none !important; }
        
        /* Asegurar que las tablas de impresión se vean bien */
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
        
        /* Forzar fondo blanco */
        * { background: transparent !important; box-shadow: none !important; text-shadow: none !important; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                deudores: [],
                busqueda: '',
                filtroTipoUsuario: 'TODOS',
                modal: { visible: false, usuario: {}, items: [] }
            }
        },
        computed: {
            deudoresFiltrados() {
                return this.deudores.filter(d => {
                    const textoMatch = d.nombre.toLowerCase().includes(this.busqueda.toLowerCase()) || d.dni.includes(this.busqueda);
                    let usuarioMatch = true;
                    if(this.filtroTipoUsuario !== 'TODOS') usuarioMatch = d.tipo === this.filtroTipoUsuario;
                    return textoMatch && usuarioMatch;
                });
            },
            stats() {
                return {
                    total_pendientes: this.deudoresFiltrados.reduce((s, d) => s + parseInt(d.total_deuda), 0)
                }
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const res = await fetch('../api/reportes.php?tipo=deudores');
                    const data = await res.json();
                    this.deudores = data;
                } catch(e) { console.error(e); }
            },
            
            async verDetalle(usuario) {
                this.modal.usuario = usuario;
                this.modal.items = [];
                this.modal.visible = true;
                
                try {
                    const res = await fetch(`../api/reportes.php?tipo=detalles_usuario&id=${usuario.id}`);
                    const data = await res.json();
                    this.modal.items = data;
                } catch(e) {
                    alert("Error al cargar detalles");
                }
            },

            imprimirPDF() { window.print(); },
            
            exportarCSV() {
                let csv = "DNI,Nombre,Rol,Pendientes,TipoDeuda,Causante,Observacion\n";
                this.deudoresFiltrados.forEach(d => {
                    csv += `"${d.dni}","${d.nombre}","${d.tipo}",${d.total_deuda},"${d.tipo_deuda}","${d.causantes_externos || '-'}","${d.observacion_texto}"\n`;
                });
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "reporte_morosos.csv";
                link.click();
            }
        }
    }).mount('#app')
</script>