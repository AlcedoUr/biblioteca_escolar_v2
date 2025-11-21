<?php include 'includes/header.php'; ?>

<div id="app">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Directorio de Usuarios</h3>
            <p class="text-muted">Administre alumnos y docentes de la institución</p>
        </div>
        <div>
            <!-- Botón Importar -->
            <button class="btn btn-outline-success me-2 shadow-sm" @click="abrirModalImportar">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar Excel
            </button>
            <!-- Botón Nuevo Usuario -->
            <button @click="abrirModal()" class="btn text-white px-4 fw-bold shadow-sm btn-hover-gold" style="background-color: #8B1538;">
                <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN (KPIs) -->
    <div class="row g-4 mb-4">
        <!-- Total Estudiantes -->
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
        <!-- Total Docentes -->
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

    <!-- PESTAÑAS DE NAVEGACIÓN (TABS) -->
    <ul class="nav nav-pills mb-3 bg-white p-2 rounded shadow-sm d-inline-flex">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 cursor-pointer" 
               :class="pestanaActiva === 'ESTUDIANTE' ? 'active bg-primary' : 'text-muted'"
               @click="pestanaActiva = 'ESTUDIANTE'">
               <i class="bi bi-backpack me-2"></i>Estudiantes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 cursor-pointer" 
               :class="pestanaActiva === 'DOCENTE' ? 'active bg-success' : 'text-muted'"
               @click="pestanaActiva = 'DOCENTE'">
               <i class="bi bi-briefcase me-2"></i>Docentes
            </a>
        </li>
    </ul>

    <!-- TABLA DE USUARIOS -->
    <div class="card border-0 shadow-sm">
        <!-- Buscador Integrado -->
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="input-group border rounded bg-light">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                <input type="text" v-model="busqueda" class="form-control border-0 bg-transparent" :placeholder="'Buscar ' + pestanaActiva.toLowerCase() + ' por nombre o DNI...'">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4">Nombre Completo</th>
                            <th>DNI</th>
                            <th>Rol</th>
                            
                            <!-- COLUMNAS DINÁMICAS -->
                            <th v-if="pestanaActiva === 'ESTUDIANTE'">Grado / Sección</th>
                            <th v-if="pestanaActiva === 'DOCENTE'">Especialidad</th>
                            <th v-if="pestanaActiva === 'DOCENTE'">Teléfono</th>
                            
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in personasFiltradas" :key="p.id">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ p.apellidos }}, {{ p.nombres }}</div>
                            </td>
                            <td class="text-muted font-monospace">{{ p.dni }}</td>
                            
                            <!-- Badge de Rol -->
                            <td>
                                <span v-if="p.tipo == 'ESTUDIANTE'" class="badge bg-light text-primary border border-primary border-opacity-25">Estudiante</span>
                                <span v-else class="badge bg-light text-success border border-success border-opacity-25">Docente</span>
                            </td>
                            
                            <!-- Datos Estudiante -->
                            <td v-if="pestanaActiva === 'ESTUDIANTE'" class="text-muted small">
                                <span v-if="p.grado" class="fw-bold text-dark">{{ p.grado }} "{{ p.seccion }}"</span>
                                <span v-else class="text-muted">-</span>
                            </td>

                            <!-- Datos Docente -->
                            <td v-if="pestanaActiva === 'DOCENTE'" class="text-dark fw-semibold">
                                {{ p.especialidad || 'General' }}
                            </td>
                            <td v-if="pestanaActiva === 'DOCENTE'" class="text-muted small">
                                {{ p.telefono || '-' }}
                            </td>

                            <!-- Estado -->
                            <td>
                                <span v-if="p.estado_biblioteca == 'ACTIVO'" class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activo</span>
                                <span v-else class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Bloqueado</span>
                            </td>
                            
                            <!-- Acciones -->
                            <td class="text-end pe-4">
                                <button @click="editar(p)" class="btn btn-sm btn-light border shadow-sm text-secondary me-2" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button @click="eliminar(p)" class="btn btn-sm btn-light border shadow-sm text-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="personasFiltradas.length === 0">
                            <td :colspan="pestanaActiva === 'DOCENTE' ? 7 : 6" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-2 opacity-25"></i>
                                No se encontraron registros en esta categoría.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer simple -->
        <div class="card-footer bg-white border-top-0 py-3">
            <small class="text-muted">Mostrando {{ personasFiltradas.length }} registros</small>
        </div>
    </div>

    <!-- MODAL NUEVO/EDITAR MEJORADO -->
    <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                
                <!-- CABECERA VINO -->
                <div class="modal-header text-white border-0" style="background-color: #8B1538;">
                    <h5 class="modal-title fw-bold">{{ usuario.id ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal()"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <!-- FILA 1: DNI y ROL (Solo lectura) -->
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">DNI <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.dni" class="form-control" maxlength="15" placeholder="Documento ID" :disabled="usuario.id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Rol</label>
                            <input type="text" class="form-control bg-light fw-bold text-uppercase" :value="usuario.tipo" disabled>
                        </div>

                        <!-- FILA 2: NOMBRES y APELLIDOS -->
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.nombres" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" v-model="usuario.apellidos" class="form-control">
                        </div>

                        <!-- FILA 3: CAMPOS DINÁMICOS -->
                        <div class="col-12"><hr class="text-muted opacity-25 my-1"></div>

                        <!-- Si es ESTUDIANTE -->
                        <template v-if="usuario.tipo == 'ESTUDIANTE'">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Grado</label>
                                <select v-model="usuario.grado" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Sección</label>
                                <input type="text" v-model="usuario.seccion" class="form-control" placeholder="Ej. A, B...">
                            </div>
                        </template>

                        <!-- Si es DOCENTE -->
                        <template v-if="usuario.tipo == 'DOCENTE'">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Especialidad / Curso</label>
                                <input type="text" v-model="usuario.especialidad" class="form-control" placeholder="Ej. Matemáticas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Teléfono</label>
                                <input type="text" v-model="usuario.telefono" class="form-control" placeholder="Ej. 999...">
                            </div>
                        </template>

                        <!-- FILA 4: ESTADO (Solo al editar) -->
                        <div class="col-12" v-if="usuario.id">
                            <div class="bg-light p-3 rounded border d-flex align-items-center justify-content-between">
                                <label class="form-label small text-muted fw-bold mb-0">Estado en Biblioteca:</label>
                                <div>
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
                </div>
                
                <!-- FOOTER CON BOTÓN DORADO -->
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button class="btn btn-light text-secondary fw-bold me-2" @click="cerrarModal()">Cancelar</button>
                    <button class="btn text-white fw-bold shadow-sm btn-gold px-4" @click="guardar()">
                        <i class="bi bi-save-fill me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORTAR EXCEL -->
    <div v-if="modalImportar" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Importación Masiva</h5>
                    <button type="button" class="btn-close" @click="modalImportar = false"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-4"><i class="bi bi-file-earmark-spreadsheet display-1 text-success opacity-50"></i></div>
                    <p class="text-muted small mb-3">
                        Sube un archivo <b>CSV</b> con el siguiente orden:<br>
                        <span v-if="pestanaActiva == 'ESTUDIANTE'" class="badge bg-primary bg-opacity-10 text-primary mt-2">DNI, Nombres, Apellidos, Tipo(ESTUDIANTE), Grado, Sección</span>
                        <span v-if="pestanaActiva == 'DOCENTE'" class="badge bg-success bg-opacity-10 text-success mt-2">DNI, Nombres, Apellidos, Tipo(DOCENTE), Especialidad, Teléfono</span>
                    </p>
                    <input type="file" ref="fileInput" class="form-control mb-3" accept=".csv">
                    <button class="btn btn-success w-100" @click="subirArchivo">Procesar Archivo</button>
                </div>
            </div>
        </div>
    </div>

</div> <!-- Fin App -->
</div> <!-- Fin Wrapper -->

<style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
    .btn-gold { background-color: #D4AF37; border: none; transition: all 0.2s; }
    .btn-gold:hover { background-color: #c4a030; box-shadow: 0 4px 6px rgba(212, 175, 55, 0.3); transform: translateY(-1px); }
    .cursor-pointer { cursor: pointer; }
    .nav-pills .nav-link { transition: all 0.2s ease; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                personas: [],
                busqueda: '',
                pestanaActiva: 'ESTUDIANTE', // Pestaña por defecto
                mostrarModal: false,
                modalImportar: false,
                usuario: { id: null, nombres: '', apellidos: '', dni: '', tipo: 'ESTUDIANTE', grado: '', seccion: '', especialidad: '', telefono: '', estado_biblioteca: 'ACTIVO' }
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
                    
                    return coincideTipo && coincideTexto;
                });
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
                } catch (e) { console.error("Error cargando personas:", e); }
            },
            abrirModal() {
                this.usuario = { 
                    id: null, 
                    nombres: '', apellidos: '', dni: '', 
                    tipo: this.pestanaActiva, // Hereda el tipo de la pestaña actual
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
                if(!this.usuario.nombres || !this.usuario.dni || !this.usuario.apellidos) {
                    alert("Por favor complete Nombre, Apellidos y DNI");
                    return;
                }
                
                try {
                    const res = await fetch('../api/personas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.usuario)
                    });
                    const data = await res.json();
                    
                    if(data.exito) {
                        this.cerrarModal();
                        this.cargarPersonas();
                        alert("✅ Guardado correctamente");
                    } else {
                        alert("❌ Error: " + data.mensaje);
                    }
                } catch (e) {
                    alert("Error de conexión");
                }
            },
            async eliminar(persona) {
                if(!confirm(`¿Está seguro de eliminar a ${persona.nombres}?`)) return;
                
                try {
                    const res = await fetch(`../api/personas.php?id=${persona.id}`, { method: 'DELETE' });
                    const data = await res.json();
                    
                    if(data.exito) {
                        this.cargarPersonas();
                    } else {
                        alert("❌ " + data.mensaje);
                    }
                } catch(e) {
                    alert("Error de conexión");
                }
            },
            abrirModalImportar() { this.modalImportar = true; },
            async subirArchivo() {
                const file = this.$refs.fileInput.files[0];
                if(!file) return alert("Seleccione CSV");
                const formData = new FormData();
                formData.append('archivo_csv', file);
                const res = await fetch('../api/personas.php', { method: 'POST', body: formData });
                const data = await res.json();
                alert(data.mensaje);
                this.modalImportar = false;
                this.cargarPersonas();
            }
        }
    }).mount('#app')
</script>
</body>
</html>