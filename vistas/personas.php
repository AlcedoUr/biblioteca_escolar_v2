<?php include 'includes/header.php'; ?>

<div id="app">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Directorio de Usuarios</h3>
            <p class="text-muted">Administre alumnos y docentes de la institución</p>
        </div>
        <button @click="abrirModal()" class="btn text-white px-4 py-2 fw-bold shadow-sm btn-hover-gold" style="background-color: #8B1538;">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
        </button>
    </div>

    <!-- TARJETAS DE RESUMEN -->
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

    <!-- TABLA DE USUARIOS -->
    <div class="card border-0 shadow-sm">
        <!-- Buscador Integrado -->
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <div class="input-group border rounded bg-light">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                <input type="text" v-model="busqueda" class="form-control border-0 bg-transparent" placeholder="Buscar por nombre, apellido o DNI..." style="height: 45px;">
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
                            <th>Grado / Secc</th>
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
                            <td>
                                <span v-if="p.tipo == 'ESTUDIANTE'" class="badge bg-light text-primary border border-primary border-opacity-25">Estudiante</span>
                                <span v-else class="badge bg-light text-success border border-success border-opacity-25">Docente</span>
                            </td>
                            <td class="text-muted small">
                                <span v-if="p.grado">{{ p.grado }} - "{{ p.seccion }}"</span>
                                <span v-else>-</span>
                            </td>
                            <td>
                                <span v-if="p.estado_biblioteca == 'ACTIVO'" class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activo</span>
                                <span v-else class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Bloqueado</span>
                            </td>
                            <td class="text-end pe-4">
                                <button @click="editar(p)" class="btn btn-sm btn-light border shadow-sm text-secondary me-2" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="personasFiltradas.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">No se encontraron usuarios con esa búsqueda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL FLOTANTE (Para Crear/Editar) -->
    <!-- Fondo Oscuro -->
    <div v-if="mostrarModal" class="modal-backdrop fade show"></div>
    
    <!-- El Modal -->
    <div v-if="mostrarModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #8B1538;">
                    <h5 class="modal-title fw-bold">{{ usuario.id ? 'Editar Usuario' : 'Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrarModal()"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">DNI</label>
                            <input type="text" v-model="usuario.dni" class="form-control" maxlength="15" placeholder="Documento ID">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Tipo</label>
                            <select v-model="usuario.tipo" class="form-select">
                                <option value="ESTUDIANTE">Estudiante</option>
                                <option value="DOCENTE">Docente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Nombres</label>
                            <input type="text" v-model="usuario.nombres" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Apellidos</label>
                            <input type="text" v-model="usuario.apellidos" class="form-control">
                        </div>

                        <!-- Campos que solo aparecen si es ESTUDIANTE -->
                        <div class="col-md-6" v-if="usuario.tipo == 'ESTUDIANTE'">
                            <label class="form-label small text-muted">Grado</label>
                            <select v-model="usuario.grado" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <option>1ro</option><option>2do</option><option>3ro</option><option>4to</option><option>5to</option><option>6to</option>
                            </select>
                        </div>
                        <div class="col-md-6" v-if="usuario.tipo == 'ESTUDIANTE'">
                            <label class="form-label small text-muted">Sección</label>
                            <input type="text" v-model="usuario.seccion" class="form-control" placeholder="Ej. A, B, Única...">
                        </div>

                        <!-- Estado (Solo visible en edición) -->
                        <div class="col-12 bg-light p-3 rounded border" v-if="usuario.id">
                            <label class="form-label small text-muted fw-bold">Estado en Biblioteca</label>
                            <select v-model="usuario.estado_biblioteca" class="form-select">
                                <option value="ACTIVO">✅ Activo (Puede realizar préstamos)</option>
                                <option value="BLOQUEADO">🚫 Bloqueado (Sancionado/Moroso)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" @click="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn text-white fw-bold shadow-sm" style="background-color: #D4AF37;" @click="guardar()">
                        <i class="bi bi-save me-2"></i>Guardar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Cierre Wrapper -->
</div> 

<style>
    .btn-hover-gold:hover { background-color: #c4a030 !important; transform: translateY(-1px); }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                personas: [],
                busqueda: '',
                mostrarModal: false,
                // Objeto vacío para el formulario
                usuario: { id: null, nombres: '', apellidos: '', dni: '', tipo: 'ESTUDIANTE', grado: '', seccion: '', estado_biblioteca: 'ACTIVO' }
            }
        },
        computed: {
            personasFiltradas() {
                if (!this.personas) return [];
                return this.personas.filter(p => 
                    p.nombres.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                    p.apellidos.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                    p.dni.includes(this.busqueda)
                );
            },
            totalEstudiantes() { return this.personas ? this.personas.filter(p => p.tipo == 'ESTUDIANTE').length : 0; },
            totalDocentes() { return this.personas ? this.personas.filter(p => p.tipo == 'DOCENTE').length : 0; }
        },
        mounted() {
            this.cargarPersonas();
        },
        methods: {
            async cargarPersonas() {
                try {
                    const res = await fetch('../api/personas.php');
                    this.personas = await res.json();
                } catch (e) {
                    console.error("Error cargando personas:", e);
                }
            },
            abrirModal() {
                // Limpiamos el formulario para uno nuevo
                this.usuario = { id: null, nombres: '', apellidos: '', dni: '', tipo: 'ESTUDIANTE', grado: '', seccion: '', estado_biblioteca: 'ACTIVO' };
                this.mostrarModal = true;
            },
            cerrarModal() {
                this.mostrarModal = false;
            },
            editar(persona) {
                // Copiamos los datos de la persona a editar al formulario
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
                        this.cargarPersonas(); // Recargamos la lista
                        alert("✅ Guardado correctamente");
                    } else {
                        alert("❌ Error: " + data.mensaje);
                    }
                } catch (e) {
                    alert("Error de conexión");
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>