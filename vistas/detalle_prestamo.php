<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';
$id_prestamo = $_GET['id'] ?? 0;
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
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
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header text-white py-3 border-0" style="background-color: #8B1538;">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Datos del Préstamo</h6>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4">
                        <label class="text-muted small fw-bold text-uppercase">Solicitante</label>
                        <div class="fw-bold text-dark fs-5">{{ cabecera.nombre }}</div>
                        <div class="text-muted small">{{ cabecera.tipo }} - {{ cabecera.dni }}</div>
                    </div>

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
                    
                    <div v-if="otros_pendientes.length > 0" class="mt-4 pt-4 border-top">
                        <div class="d-flex align-items-center mb-3 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                            <h6 class="fw-bold mb-0">Otras deudas pendientes</h6>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div v-for="prestamo in otros_pendientes" :key="prestamo.id_prestamo" class="card border-danger border-opacity-25 shadow-sm bg-danger bg-opacity-10">
                                <div class="card-body p-3">
                                    <div class="fw-bold text-danger mb-2"><i class="bi bi-collection me-1"></i> Préstamo #{{ prestamo.id_prestamo }}</div>
                                    <ul class="list-unstyled mb-3 ps-2 border-start border-danger border-2 border-opacity-25">
                                        <li v-for="(libro, idx) in prestamo.libros" :key="idx" class="mb-1 small text-dark">
                                            • {{ libro.titulo }} <span v-if="libro.cantidad > 1" class="fw-bold">({{ libro.cantidad }})</span>
                                        </li>
                                    </ul>
                                    <div class="d-grid">
                                        <a :href="'detalle_prestamo.php?id=' + prestamo.id_prestamo" class="btn btn-sm btn-danger text-white fw-bold">Gestionar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                    
                                    <td>
                                        <span v-if="d.estado_devolucion == 'PENDIENTE'" class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3 rounded-pill">Pendiente</span>
                                        <span v-else-if="d.estado_devolucion == 'BUENO'" class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill"><i class="bi bi-check-circle me-1"></i> OK</span>
                                        <span v-else-if="d.estado_devolucion == 'DAÑADO'" class="badge bg-warning text-dark px-3 rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i> Dañado</span>
                                        <span v-else class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill"><i class="bi bi-x-circle me-1"></i> Perdido</span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div v-if="d.estado_devolucion == 'PENDIENTE'">
                                            <div class="btn-group shadow-sm" role="group">
                                                <button @click="procesar(d, 'BUENO')" class="btn btn-sm btn-success text-white fw-bold px-3" title="Devolver en buen estado">
                                                    <i class="bi bi-check-lg"></i>
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
                <div class="card-footer bg-white text-center py-4 animate-fade" v-if="pendientes === 0">
                    <div class="text-success mb-2"><i class="bi bi-check-circle-fill display-4"></i></div>
                    <h5 class="fw-bold text-dark">¡Préstamo Finalizado!</h5>
                    <a href="historial.php" class="btn btn-outline-success rounded-pill px-4">Volver al Historial</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-fade { animation: fadeIn 0.4s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
    const { createApp, nextTick } = Vue

    createApp({
        data() {
            return {
                idPrestamo: <?php echo $id_prestamo; ?>,
                detalles: [],
                cabecera: {}, 
                otros_pendientes: [],
                listaPersonas: [] 
            }
        },
        computed: {
            pendientes() { return this.detalles.filter(d => d.estado_devolucion === 'PENDIENTE').length; },
            totalLibros() { return this.detalles.reduce((acc, item) => acc + parseInt(item.cantidad), 0); }
        },
        mounted() { 
            this.cargarDetalles();
            this.cargarPersonas(); 
        },
        methods: {
            async cargarDetalles() {
                try {
                    const res = await fetch(`../api/obtener_detalle.php?id=${this.idPrestamo}`);
                    const data = await res.json();
                    this.detalles = data.detalles;
                    this.cabecera = data.cabecera || { nombre: '...', dni: '-', tipo: '-', ubicacion_final: '-', fecha_fin: '-' };
                    this.otros_pendientes = data.otros_pendientes || [];
                } catch(e) { console.error("Error:", e); }
            },
            
            async cargarPersonas() {
                try {
                    const res = await fetch('../api/personas.php');
                    this.listaPersonas = await res.json();
                } catch(e) { console.error(e); }
            },
            
            async procesar(item, estado) {
                let cantidadAProcesar = item.cantidad; 
                let idCausanteSeleccionado = null;

                // CONFIGURACIÓN VISUAL
                let tituloAlerta = 'Confirmar Devolución';
                let iconoAlerta = 'question';
                let colorBtn = '#198754'; 

                if (estado === 'DAÑADO') {
                    tituloAlerta = 'Reportar Daño';
                    iconoAlerta = 'warning';
                    colorBtn = '#ffc107'; 
                } else if (estado === 'PERDIDO') {
                    tituloAlerta = 'Reportar Pérdida';
                    iconoAlerta = 'error';
                    colorBtn = '#dc3545'; 
                }

                // === LÓGICA DE INCIDENCIA CON BUSCADOR INTEGRADO ===
                if (estado === 'DAÑADO' || estado === 'PERDIDO') {
                    
                    // Creamos las opciones limpias (Solo Nombre y Apellido)
                    let opcionesHTML = this.listaPersonas
                        .map(p => `<option value="${p.id}">${p.apellidos}, ${p.nombres}</option>`)
                        .join('');

                    const { value: formValues } = await Swal.fire({
                        title: tituloAlerta,
                        icon: iconoAlerta,
                        // HTML CON BUSCADOR Y LISTA
                        html: `
                            <div class="text-start small mb-3 text-muted">
                                Se registrará como <b>${estado}</b>. Indique cantidad y responsable.
                            </div>
                            
                            ${item.cantidad > 1 ? `
                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small">Cantidad afectada:</label>
                                <input id="swal-input-cant" type="number" class="form-control text-center fw-bold" min="1" max="${item.cantidad}" value="${item.cantidad}">
                            </div>
                            ` : '<input id="swal-input-cant" type="hidden" value="1">'}

                            <div class="form-check border p-2 rounded bg-light mb-3 text-start">
                                <input class="form-check-input ms-1" type="checkbox" id="swal-check-resp" checked onchange="
                                    document.getElementById('div-select-causante').style.display = this.checked ? 'none' : 'block';
                                    if(!this.checked) setTimeout(() => document.getElementById('swal-search-input').focus(), 100);
                                ">
                                <label class="form-check-label fw-bold ms-2" for="swal-check-resp">
                                    Responsable: Solicitante original
                                    <div class="text-muted small fw-normal ps-1">(${this.cabecera.nombre})</div>
                                </label>
                            </div>

                            <div id="div-select-causante" style="display:none;" class="text-start">
                                <label class="form-label fw-bold small text-danger">Buscar causante real:</label>
                                
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" id="swal-search-input" class="form-control" placeholder="Escriba para filtrar...">
                                </div>
                                
                                <select id="swal-input-causante" class="form-select form-select-sm" size="4">
                                    ${opcionesHTML}
                                </select>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonColor: colorBtn,
                        confirmButtonText: 'Registrar Incidencia',
                        cancelButtonText: 'Cancelar',
                        didOpen: () => {
                            // LÓGICA DE FILTRADO EN TIEMPO REAL
                            const inputSearch = Swal.getPopup().querySelector('#swal-search-input');
                            const selectBox = Swal.getPopup().querySelector('#swal-input-causante');
                            const originalOptions = Array.from(selectBox.options); // Copia de seguridad

                            inputSearch.addEventListener('input', (e) => {
                                const term = e.target.value.toLowerCase();
                                selectBox.innerHTML = ''; // Limpiar
                                
                                const filtrados = originalOptions.filter(opt => opt.text.toLowerCase().includes(term));
                                
                                if(filtrados.length > 0) {
                                    filtrados.forEach(opt => selectBox.add(opt));
                                } else {
                                    // Opción vacía si no hay resultados
                                    let opt = document.createElement('option');
                                    opt.text = "No encontrado...";
                                    opt.disabled = true;
                                    selectBox.add(opt);
                                }
                            });
                        },
                        preConfirm: () => {
                            return {
                                cantidad: document.getElementById('swal-input-cant').value,
                                esSolicitante: document.getElementById('swal-check-resp').checked,
                                idCausante: document.getElementById('swal-input-causante').value
                            }
                        }
                    });

                    if (!formValues) return; 

                    cantidadAProcesar = parseInt(formValues.cantidad);
                    
                    if (cantidadAProcesar > item.cantidad || cantidadAProcesar < 1) {
                        return Swal.fire('Error', 'Cantidad inválida', 'error');
                    }

                    if (!formValues.esSolicitante) {
                        if (!formValues.idCausante) return Swal.fire('Atención', 'Debe seleccionar un nombre de la lista.', 'warning');
                        idCausanteSeleccionado = formValues.idCausante;
                    }

                } else {
                    // --- CASO BUENO (Flujo Normal) ---
                    if (item.cantidad > 1) {
                        const { value: cant } = await Swal.fire({
                            title: 'Confirmar Devolución',
                            text: `Devolver ${item.cantidad} libros. ¿Cuántos están OK?`,
                            input: 'number',
                            inputValue: item.cantidad,
                            inputAttributes: { min: 1, max: item.cantidad },
                            showCancelButton: true,
                            confirmButtonColor: colorBtn,
                            confirmButtonText: 'Confirmar'
                        });
                        if (!cant) return;
                        cantidadAProcesar = parseInt(cant);
                    } else {
                        const res = await Swal.fire({ title: 'Confirmar', text: 'Marcar libro como devuelto en buen estado', icon: 'question', showCancelButton: true, confirmButtonColor: colorBtn });
                        if (!res.isConfirmed) return;
                    }
                }

                // ENVÍO AL BACKEND
                const datos = {
                    id_detalle: item.id_detalle,
                    id_libro: item.id_libro,
                    cantidad_total: item.cantidad,
                    cantidad_procesar: cantidadAProcesar,
                    estado: estado,
                    id_causante: idCausanteSeleccionado
                };

                try {
                    const res = await fetch('../api/procesar_devolucion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(datos)
                    });
                    const r = await res.json();
                    
                    if (r.exito) {
                        Swal.fire({ icon: 'success', title: 'Procesado', text: 'Estado actualizado correctamente', timer: 1200, showConfirmButton: false });
                        this.cargarDetalles(); 
                    } else {
                        Swal.fire('Error', r.mensaje, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>