<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Directorio de Usuarios</h3>
            <p class="text-muted">Administre alumnos y docentes de la institución</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success shadow-sm fw-bold" @click="abrirModalImportar">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar
            </button>
            <button @click="abrirModal()" class="btn text-white fw-bold shadow-sm btn-hover-gold" style="background-color: #8B1538;">
                <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-backpack text-primary fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase">Total Estudiantes</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ totalEstudiantes }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-video3 text-success fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase">Total Docentes</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ totalDocentes }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            
            <ul class="nav nav-pills mb-3 p-1 bg-light rounded d-inline-flex">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 cursor-pointer fw-bold small" 
                       :class="pestanaActiva === 'ESTUDIANTE' ? 'active bg-white text-primary shadow-sm' : 'text-muted'"
                       @click="pestanaActiva = 'ESTUDIANTE'">Estudiantes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 cursor-pointer fw-bold small" 
                       :class="pestanaActiva === 'DOCENTE' ? 'active bg-white text-success shadow-sm' : 'text-muted'"
                       @click="pestanaActiva = 'DOCENTE'">Docentes</a>
                </li>
            </ul>

            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group border rounded bg-white">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar usuario...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select v-model="filtroEstado" class="form-select border-0 bg-light text-muted cursor-pointer">
                        <option value="TODOS">Todos los estados</option>
                        <option value="ACTIVO">Activos</option>
                        <option value="BLOQUEADO">Bloqueados</option>
                    </select>
                </div>
                
                <div class="col-md-5 text-end">
                    <span class="text-muted small me-2">Mostrar:</span>
                    <select v-model="itemsPorPagina" @change="paginaActual = 1" class="form-select d-inline-block w-auto border-0 bg-light text-muted cursor-pointer form-select-sm">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3">Usuario</th>
                            <th>DNI</th>
                            <th>Rol</th>
                            <th v-if="pestanaActiva === 'ESTUDIANTE'">Grado / Sección</th>
                            <th v-if="pestanaActiva === 'DOCENTE'">Especialidad</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in personasPaginadas" :key="p.id">
                            
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-secondary fw-bold" style="width: 35px; height: 35px;">
                                        {{ p.nombres.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark text-capitalize">{{ p.apellidos }}, {{ p.nombres }}</div>
                                        <div class="small text-muted" v-if="pestanaActiva === 'DOCENTE' && p.telefono"><i class="bi bi-phone"></i> {{ p.telefono }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="font-monospace text-muted cursor-pointer" @click="toggleDni(p)" title="Clic para ver">
                                <span v-if="p.verDni">{{ p.dni }}</span>
                                <span v-else>******** <i class="bi bi-eye-slash ms-1 small"></i></span>
                            </td>

                            <td>
                                <span v-if="p.tipo == 'ESTUDIANTE'" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 rounded-pill">Estudiante</span>
                                <span v-else class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 rounded-pill">Docente</span>
                            </td>
                            
                            <td v-if="pestanaActiva === 'ESTUDIANTE'" class="text-dark">
                                <span v-if="p.grado" class="fw-bold">{{ p.grado }} "{{ p.seccion.toUpperCase() }}"</span>
                                <span v-else class="text-muted small">-</span>
                            </td>

                            <td v-if="pestanaActiva === 'DOCENTE'" class="text-dark text-capitalize">
                                {{ p.especialidad || 'General' }}
                            </td>

                            <td>
                                <span v-if="p.estado_biblioteca == 'ACTIVO'" class="badge bg-success text-white px-3 rounded-pill shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Activo
                                </span>
                                <span v-else class="badge bg-danger text-white px-3 rounded-pill shadow-sm">
                                    <i class="bi bi-ban me-1"></i> Bloqueado
                                </span>
                            </td>
                            
                            <td class="text-end pe-4">
                                <button @click="editar(p)" class="btn btn-sm btn-light border shadow-sm text-primary me-2 hover-scale" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button @click="eliminar(p)" class="btn btn-sm btn-light border shadow-sm text-danger hover-scale" title="Eliminar">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="personasFiltradas.length === 0">
                            <td :colspan="pestanaActiva === 'DOCENTE' ? 6 : 6" class="text-center py-5 text-muted">
                                <i class="bi bi-search display-4 d-block mb-2 opacity-25"></i>
                                No se encontraron resultados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted fw-bold">Página {{ paginaActual }} de {{ totalPaginas }}</small>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-1 fw-bold" :disabled="paginaActual <= 1" @click="paginaActual--">
                    <i class="bi bi-chevron-left"></i> Anterior
                </button>
                <button class="btn btn-sm btn-outline-secondary fw-bold" :disabled="paginaActual >= totalPaginas" @click="paginaActual++">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header text-white border-0" style="background-color: #8B1538;">
                    <h5 class="modal-title fw-bold">{{ usuario.id ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal()"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">DNI <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.dni" class="form-control" maxlength="15" :disabled="usuario.id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Rol</label>
                            <input type="text" class="form-control bg-light fw-bold text-uppercase" :value="usuario.tipo" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.nombres" class="form-control text-capitalize">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.apellidos" class="form-control text-capitalize">
                        </div>
                        
                        <div class="col-12"><hr class="text-muted opacity-25 my-1"></div>

                        <template v-if="usuario.tipo == 'ESTUDIANTE'">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Grado</label>
                                <select v-model="usuario.grado" class="form-select">
                                    <option value="">--</option>
                                    <option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Sección</label>
                                <input type="text" v-model="usuario.seccion" class="form-control text-uppercase" placeholder="A, B...">
                            </div>
                        </template>

                        <template v-if="usuario.tipo == 'DOCENTE'">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Especialidad</label>
                                <input type="text" v-model="usuario.especialidad" class="form-control text-capitalize">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Teléfono</label>
                                <input type="text" v-model="usuario.telefono" class="form-control">
                            </div>
                        </template>

                        <div class="col-12 mt-3" v-if="usuario.id">
                            <div class="bg-light p-3 rounded border">
                                <label class="form-label small text-muted fw-bold d-block">Estado:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" v-model="usuario.estado_biblioteca" value="ACTIVO" id="stActivo">
                                    <label class="form-check-label text-success fw-bold" for="stActivo">Activo</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" v-model="usuario.estado_biblioteca" value="BLOQUEADO" id="stBlock">
                                    <label class="form-check-label text-danger fw-bold" for="stBlock">Bloqueado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button class="btn btn-light text-secondary fw-bold me-2" @click="cerrarModal()">Cancelar</button>
                    <button class="btn text-white fw-bold shadow-sm btn-gold px-4" @click="guardar()">
                        <i class="bi bi-save-fill me-2"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="modalImportar" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Importación Masiva</h5>
                    <button type="button" class="btn-close" @click="modalImportar = false"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">
                        Suba un archivo CSV con los datos de los usuarios.<br>
                        (DNI, Nombres, Apellidos, Tipo, Grado/Esp...)
                    </p>
                    <input type="file" ref="fileInput" class="form-control mb-3" accept=".csv">
                    <button class="btn btn-success w-100 fw-bold" @click="subirArchivo">Procesar Archivo</button>
                </div>
            </div>
        </div>
    </div>
    
</div> <style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .btn-gold { background-color: #D4AF37; border: none; transition: all 0.2s; }
    .btn-gold:hover { background-color: #c4a030; box-shadow: 0 4px 6px rgba(212, 175, 55, 0.3); transform: translateY(-1px); }
    .cursor-pointer { cursor: pointer; }
    .nav-pills .nav-link { transition: all 0.2s ease; border: 1px solid transparent; }
    .nav-pills .nav-link:hover:not(.active) { background-color: #e9ecef; }
    .hover-scale:hover { transform: scale(1.1); }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                personas: [],
                busqueda: '',
                filtroEstado: 'TODOS',
                pestanaActiva: 'ESTUDIANTE',
                mostrarModal: false,
                modalImportar: false,
                usuario: { id: null, nombres: '', apellidos: '', dni: '', tipo: 'ESTUDIANTE', grado: '', seccion: '', especialidad: '', telefono: '', estado_biblioteca: 'ACTIVO' },
                
                // Paginación
                paginaActual: 1,
                itemsPorPagina: 10
            }
        },
        computed: {
            personasFiltradas() {
                return this.personas.filter(p => {
                    const coincideTipo = p.tipo === this.pestanaActiva;
                    const texto = this.busqueda.toLowerCase();
                    const coincideTexto = 
                        p.nombres.toLowerCase().includes(texto) ||
                        p.apellidos.toLowerCase().includes(texto) ||
                        p.dni.includes(texto);
                    
                    let coincideEstado = true;
                    if(this.filtroEstado === 'ACTIVO') coincideEstado = p.estado_biblioteca === 'ACTIVO';
                    if(this.filtroEstado === 'BLOQUEADO') coincideEstado = p.estado_biblioteca !== 'ACTIVO';

                    return coincideTipo && coincideTexto && coincideEstado;
                });
            },
            personasPaginadas() {
                const inicio = (this.paginaActual - 1) * this.itemsPorPagina;
                const fin = inicio + this.itemsPorPagina;
                return this.personasFiltradas.slice(inicio, fin);
            },
            totalPaginas() {
                return Math.ceil(this.personasFiltradas.length / this.itemsPorPagina) || 1;
            },
            totalEstudiantes() { return this.personas.filter(p => p.tipo == 'ESTUDIANTE').length; },
            totalDocentes() { return this.personas.filter(p => p.tipo == 'DOCENTE').length; }
        },
        mounted() {
            this.cargarPersonas();
        },
        methods: {
            async cargarPersonas() {
                try {
                    const res = await fetch('../api/personas.php');
                    this.personas = await res.json();
                    this.personas.forEach(p => p.verDni = false);
                } catch (e) { console.error("Error:", e); }
            },
            toggleDni(persona) {
                persona.verDni = !persona.verDni;
            },
            abrirModal() {
                this.usuario = { 
                    id: null, 
                    nombres: '', apellidos: '', dni: '', 
                    tipo: this.pestanaActiva, 
                    grado: '', seccion: '', especialidad: '', telefono: '', 
                    estado_biblioteca: 'ACTIVO' 
                };
                this.mostrarModal = true;
            },
            cerrarModal() { this.mostrarModal = false; },
            editar(persona) {
                this.usuario = { ...persona };
                this.mostrarModal = true;
            },
            async guardar() {
                if(!this.usuario.nombres || !this.usuario.dni) {
                    Swal.fire('Atención', 'Complete nombre y DNI obligatorios', 'warning');
                    return;
                }
                
                const res = await fetch('../api/personas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.usuario)
                });
                const data = await res.json();
                
                if(data.exito) {
                    this.cerrarModal();
                    this.cargarPersonas();
                    Swal.fire({ icon: 'success', title: 'Éxito', text: 'Usuario guardado correctamente', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire('Error', data.mensaje, 'error');
                }
            },
            async eliminar(persona) {
                const result = await Swal.fire({
                    title: '¿Eliminar usuario?',
                    text: `Se eliminará a ${persona.nombres}. Esta acción no se puede deshacer si tiene préstamos.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                });

                if (result.isConfirmed) {
                    const res = await fetch(`../api/personas.php?id=${persona.id}`, { method: 'DELETE' });
                    const data = await res.json();
                    if(data.exito) {
                        this.cargarPersonas();
                        Swal.fire('Eliminado', 'El usuario ha sido eliminado.', 'success');
                    } else {
                        Swal.fire('No se pudo eliminar', data.mensaje, 'error');
                    }
                }
            },
            abrirModalImportar() { this.modalImportar = true; },
            async subirArchivo() {
                const file = this.$refs.fileInput.files[0];
                if(!file) return Swal.fire('Atención', 'Seleccione un archivo CSV', 'info');
                
                const formData = new FormData();
                formData.append('archivo_csv', file);
                
                // Nota: Se asume que el endpoint api/personas.php maneja la importación si se envía archivo_csv
                // Si no, deberás implementar esa lógica en el backend.
                const res = await fetch('../api/personas.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if(data.exito) {
                    Swal.fire('Importación', data.mensaje || 'Proceso completado', 'success');
                    this.modalImportar = false;
                    this.cargarPersonas();
                } else {
                    Swal.fire('Error', data.mensaje || 'Error en importación', 'error');
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>