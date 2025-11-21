<?php
// Iniciamos sesión si no está iniciada para poder usar $_SESSION en el header
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';

// Recibimos el ID por URL
$id_prestamo = $_GET['id'] ?? 0;
?>

<div id="app">
    
    <!-- ENCABEZADO Y NAVEGACIÓN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0" style="color: #8B1538;">Gestionar Devolución</h3>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="historial.php" class="text-decoration-none text-muted">Historial</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold">Préstamo #<?php echo $id_prestamo; ?></li>
                </ol>
            </nav>
        </div>
        <a href="historial.php" class="btn btn-light border text-muted shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Volver al listado
        </a>
    </div>

    <div class="row g-4" v-if="detalles.length > 0">
        
        <!-- COLUMNA IZQUIERDA: INFORMACIÓN DEL PRÉSTAMO -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header text-white py-3 border-0" style="background-color: #8B1538;">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Datos del Préstamo</h6>
                </div>
                <div class="card-body">
                    
                    <!-- Datos del Usuario -->
                    <div class="mb-4">
                        <label class="text-muted small fw-bold text-uppercase">Solicitante</label>
                        <div class="fw-bold text-dark fs-5">{{ cabecera.nombre }}</div>
                        <div class="text-muted small">{{ cabecera.tipo }} - {{ cabecera.dni }}</div>
                    </div>

                    <!-- Ubicación y Tiempo -->
                    <div class="bg-light p-3 rounded border mb-3">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <span class="small text-muted fw-bold text-uppercase">Ubicación / Destino</span>
                            </div>
                            <div class="fw-bold text-dark ps-4">{{ cabecera.ubicacion_final }}</div>
                        </div>
                        
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar-event-fill text-primary me-2"></i>
                                <span class="small text-muted fw-bold text-uppercase">Devolución Pactada</span>
                            </div>
                            <div class="fw-bold text-dark ps-4">
                                {{ cabecera.fecha_fin }}
                                <span v-if="cabecera.hora_limite" class="badge bg-warning text-dark ms-2 border border-warning">
                                    <i class="bi bi-clock me-1"></i>{{ cabecera.hora_limite }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Estado General -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="text-muted small">Estado Actual:</span>
                        <span class="badge bg-warning text-dark border border-warning" v-if="pendientes > 0">En Proceso</span>
                        <span class="badge bg-success" v-else>Finalizado</span>
                    </div>

                    <!-- ALERTA DE OTRAS DEUDAS -->
                    <div v-if="otros_pendientes.length > 0" class="mt-4 pt-3 border-top">
                        <div class="alert alert-danger border-0 d-flex align-items-start shadow-sm mb-0">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">¡Atención!</h6>
                                <p class="small mb-2">Este usuario tiene <strong>{{ otros_pendientes.length }} préstamos más</strong> pendientes de devolución.</p>
                                <ul class="list-unstyled small mb-0 ps-2 border-start border-danger border-2">
                                    <li v-for="deuda in otros_pendientes" :key="deuda.id_otro_prestamo" class="mb-1">
                                        <a :href="'detalle_prestamo.php?id=' + deuda.id_otro_prestamo" class="text-danger text-decoration-none fw-bold">
                                            • {{ deuda.titulo }} ({{ deuda.cantidad }})
                                        </a>
                                        <span class="text-muted ms-1">- {{ deuda.fecha }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div v-else class="mt-4 pt-3 border-top text-center text-success small">
                        <i class="bi bi-check-circle-fill me-1"></i> Sin otras deudas pendientes.
                    </div>

                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: LISTA DE LIBROS (ACCIONES) -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Items a Devolver</h6>
                    <span class="badge bg-light text-dark border">{{ totalLibros }} libros en total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4 py-3">Material Bibliográfico</th>
                                    <th class="text-center">Cant.</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in detalles" :key="d.id_detalle" class="animate-fade">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 rounded bg-light text-secondary d-flex align-items-center justify-content-center fs-4 fw-bold border" style="width: 40px; height: 50px;">
                                                <i class="bi bi-book"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ d.titulo }}</div>
                                                <small class="text-muted">Código: LIB-{{ d.id_libro }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fs-6">{{ d.cantidad }}</span>
                                    </td>
                                    
                                    <!-- Estado -->
                                    <td>
                                        <span v-if="d.estado_devolucion == 'PENDIENTE'" class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3 rounded-pill">
                                            Pendiente
                                        </span>
                                        <span v-else-if="d.estado_devolucion == 'BUENO'" class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> Devuelto OK
                                        </span>
                                        <span v-else-if="d.estado_devolucion == 'DAÑADO'" class="badge bg-warning text-dark px-3 rounded-pill">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Dañado
                                        </span>
                                        <span v-else class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill">
                                            <i class="bi bi-x-circle me-1"></i> Perdido
                                        </span>
                                    </td>

                                    <!-- Botones de Acción -->
                                    <td class="text-end pe-4">
                                        <div v-if="d.estado_devolucion == 'PENDIENTE'">
                                            <div class="btn-group shadow-sm" role="group">
                                                <button @click="procesar(d, 'BUENO')" class="btn btn-sm btn-success text-white fw-bold px-3" title="Devolver en buen estado">
                                                    <i class="bi bi-check-lg me-1"></i> OK
                                                </button>
                                                <button @click="procesar(d, 'DAÑADO')" class="btn btn-sm btn-warning text-dark fw-bold px-3" title="Reportar daño">
                                                    <i class="bi bi-bandaid"></i>
                                                </button>
                                                <button @click="procesar(d, 'PERDIDO')" class="btn btn-sm btn-danger text-white fw-bold px-3" title="Reportar pérdida">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="text-muted small fst-italic">
                                            Procesado <i class="bi bi-lock-fill ms-1"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Footer si ya todo está devuelto -->
                <div class="card-footer bg-white text-center py-4" v-if="pendientes === 0">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle-fill display-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">¡Préstamo Finalizado!</h5>
                    <p class="text-muted small">Todos los libros han sido procesados correctamente.</p>
                    <a href="historial.php" class="btn btn-outline-success rounded-pill px-4">Volver al Historial</a>
                </div>
            </div>
        </div>

    </div>

    <!-- Estado de Carga -->
    <div v-else class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Cargando detalles...</p>
    </div>

</div>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                idPrestamo: <?php echo $id_prestamo; ?>,
                detalles: [],
                cabecera: {}, // Objeto para guardar los datos del encabezado
                otros_pendientes: [] // Array para el historial de deudas
            }
        },
        computed: {
            pendientes() {
                return this.detalles.filter(d => d.estado_devolucion === 'PENDIENTE').length;
            },
            totalLibros() {
                return this.detalles.reduce((acc, item) => acc + parseInt(item.cantidad), 0);
            }
        },
        mounted() {
            this.cargarDetalles();
        },
        methods: {
            async cargarDetalles() {
                try {
                    const res = await fetch(`../api/obtener_detalle.php?id=${this.idPrestamo}`);
                    const data = await res.json();
                    
                    this.detalles = data.detalles;
                    // Aseguramos que cabecera tenga valores por defecto para evitar errores de renderizado
                    this.cabecera = data.cabecera || { 
                        nombre: 'Desconocido', 
                        dni: '-', 
                        tipo: '-', 
                        ubicacion_final: 'No especificada', 
                        fecha_fin: '-',
                        hora_limite: null 
                    };
                    this.otros_pendientes = data.otros_pendientes || [];
                    
                } catch(e) { console.error("Error:", e); }
            },
            async procesar(item, estado) {
                let mensaje = `¿Confirmar devolución de "${item.titulo}"?`;
                if (estado === 'DAÑADO') mensaje = `¿Reportar "${item.titulo}" como DAÑADO?\n(Esto no sumará al stock disponible)`;
                if (estado === 'PERDIDO') mensaje = `¿Reportar "${item.titulo}" como PERDIDO?\n(El libro se dará de baja)`;

                if (!confirm(mensaje)) return;

                const datos = {
                    id_detalle: item.id_detalle,
                    id_libro: item.id_libro,
                    cantidad: item.cantidad,
                    estado: estado
                };

                try {
                    const res = await fetch('../api/procesar_devolucion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(datos)
                    });
                    const r = await res.json();
                    
                    if (r.exito) {
                        // Actualizar localmente para feedback instantáneo
                        item.estado_devolucion = estado;
                        // Recargar para asegurar consistencia y actualizar la cabecera si es necesario
                        this.cargarDetalles();
                    } else {
                        alert("Error: " + r.mensaje);
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