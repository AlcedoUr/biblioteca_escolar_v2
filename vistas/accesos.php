<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Accesos Docentes</h3>
            <p class="text-muted mb-0">Administración centralizada de credenciales y permisos.</p>
        </div>
        <div>
            <div class="btn-group shadow-sm" v-if="seleccionados.length > 0">
                <button @click="ejecutarAccion('CREAR')" class="btn btn-success fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Generar Accesos
                </button>
                <button @click="ejecutarAccion('BLOQUEAR')" class="btn btn-warning text-dark fw-bold">
                    <i class="bi bi-lock-fill me-2"></i>Bloquear
                </button>
                <button @click="ejecutarAccion('DESBLOQUEAR')" class="btn btn-outline-secondary fw-bold">
                    <i class="bi bi-unlock-fill"></i>
                </button>
                <button @click="ejecutarAccion('ELIMINAR')" class="btn btn-danger fw-bold">
                    <i class="bi bi-trash-fill me-2"></i>Eliminar Accesos
                </button>
            </div>
            <div v-else class="text-muted small fst-italic p-2 bg-light rounded border">
                <i class="bi bi-info-circle me-1"></i> Seleccione docentes para ver opciones
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted fw-bold">
                        <tr>
                            <th class="ps-4 py-3" style="width: 50px;">
                                <input type="checkbox" class="form-check-input cursor-pointer" 
                                       :checked="todosSeleccionados" 
                                       @change="toggleTodos">
                            </th>
                            <th>Docente</th>
                            <th>DNI (Contraseña)</th>
                            <th>Usuario Generado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in docentes" :key="d.id_persona" :class="{'bg-light': isSelected(d.id_persona)}">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input cursor-pointer" 
                                       :value="d.id_persona" 
                                       v-model="seleccionados">
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ d.apellidos }}, {{ d.nombres }}</div>
                            </td>
                            <td class="font-monospace text-muted">{{ d.dni }}</td>
                            <td>
                                <div v-if="d.username">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill font-monospace">
                                        {{ d.username }}
                                    </span>
                                </div>
                                <div v-else>
                                    <span class="badge bg-light text-muted border rounded-pill">Sin acceso</span>
                                </div>
                            </td>
                            <td>
                                <span v-if="d.estado_biblioteca == 'ACTIVO'" class="text-success fw-bold small">
                                    <i class="bi bi-check-circle-fill"></i> Activo
                                </span>
                                <span v-else class="text-danger fw-bold small">
                                    <i class="bi bi-ban"></i> Bloqueado
                                </span>
                            </td>
                        </tr>
                        <tr v-if="docentes.length === 0">
                            <td colspan="5" class="text-center py-5">No hay docentes registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 text-muted small">
            <i class="bi bi-info-circle-fill me-1"></i> 
            <strong>Nota:</strong> El usuario se genera como: <em>1ra letra Apellido + 1er Nombre</em> (ej. Juan Perez -> pjuan). La contraseña es el DNI.
        </div>
    </div>

</div>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                docentes: [],
                seleccionados: []
            }
        },
        computed: {
            todosSeleccionados() {
                return this.docentes.length > 0 && this.seleccionados.length === this.docentes.length;
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    const res = await fetch('../api/gestion_accesos.php');
                    this.docentes = await res.json();
                } catch (e) { console.error(e); }
            },
            toggleTodos(e) {
                if (e.target.checked) {
                    this.seleccionados = this.docentes.map(d => d.id_persona);
                } else {
                    this.seleccionados = [];
                }
            },
            isSelected(id) {
                return this.seleccionados.includes(id);
            },
            async ejecutarAccion(accion) {
                let msj = '';
                if (accion === 'CREAR') msj = 'Se generarán usuarios y contraseñas para los docentes seleccionados.';
                if (accion === 'ELIMINAR') msj = 'Se eliminará el acceso al sistema de los seleccionados.';
                if (accion === 'BLOQUEAR') msj = 'Se bloqueará el ingreso temporalmente.';

                const confirm = await Swal.fire({
                    title: '¿Confirmar Acción?',
                    text: msj,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8B1538',
                    confirmButtonText: 'Sí, ejecutar'
                });

                if (confirm.isConfirmed) {
                    try {
                        const res = await fetch('../api/gestion_accesos.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                accion: accion,
                                ids: this.seleccionados
                            })
                        });
                        const data = await res.json();
                        
                        if (data.exito) {
                            Swal.fire('Éxito', data.mensaje, 'success');
                            this.cargarDatos();
                            this.seleccionados = [];
                        } else {
                            Swal.fire('Error', data.mensaje, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Fallo de conexión', 'error');
                    }
                }
            }
        }
    }).mount('#app')
</script>