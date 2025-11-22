<?php include 'includes/header.php'; ?>
<!-- SweetAlert2 para alertas modernas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Catálogo de Libros</h3>
            <p class="text-muted">Gestión de inventario y existencias</p>
        </div>
        <div>
            <button class="btn btn-outline-success me-2 shadow-sm fw-bold" @click="abrirModalImportar">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar
            </button>
            <button class="btn text-white shadow-sm btn-hover-gold fw-bold" style="background-color: #8B1538;" @click="abrirModalNuevo">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Libro
            </button>
        </div>
    </div>

    <!-- BARRA DE HERRAMIENTAS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <!-- Buscador -->
                <div class="col-md-4">
                    <div class="input-group border border-2 rounded-pill bg-light overflow-hidden">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="filtros.busqueda" @input="cargarLibros(1)" class="form-control border-0 bg-transparent" placeholder="Buscar por título, autor...">
                    </div>
                </div>
                
                <!-- Filtros Dinámicos (Categorías Mejoradas) -->
                <div class="col-md-8 text-md-end">
                    <div class="d-inline-flex gap-2 overflow-auto pb-1 align-items-center" style="max-width: 100%; white-space: nowrap;">
                        <span class="text-muted small fw-bold text-uppercase me-2">Categorías:</span>
                        <button class="btn btn-sm rounded-pill px-3 fw-bold cat-btn" 
                                :class="filtroActivo === 'TODO' ? 'cat-active' : 'cat-inactive'" 
                                @click="filtrar('TODO')">Todo</button>
                        <button v-for="cat in listaCategorias" 
                                class="btn btn-sm rounded-pill px-3 fw-bold cat-btn text-capitalize" 
                                :class="filtroActivo === cat ? 'cat-active' : 'cat-inactive'" 
                                @click="filtrar(cat)">
                            {{ cat }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE LIBROS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3 cursor-pointer user-select-none" @click="ordenar('titulo')">
                                Libro <i class="bi" :class="getIconoOrden('titulo')"></i>
                            </th>
                            <th>Categoría</th>
                            <th class="cursor-pointer user-select-none" @click="ordenar('autor')">
                                Autor <i class="bi" :class="getIconoOrden('autor')"></i>
                            </th>
                            <th>Editorial</th>
                            <th style="width: 220px;" class="cursor-pointer user-select-none" @click="ordenar('stock_total')">
                                Disponibilidad <i class="bi" :class="getIconoOrden('stock_total')"></i>
                            </th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="libro in libros" :key="libro.id">
                            <!-- Columna Libro -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <!-- Icono de Estado -->
                                    <div class="me-3 rounded d-flex align-items-center justify-content-center fs-4 shadow-sm border" 
                                         :class="libro.stock_disponible > 0 ? 'bg-white text-success border-success' : 'bg-light text-muted border-secondary'"
                                         style="width: 45px; height: 60px;">
                                        <i class="bi" :class="libro.stock_disponible > 0 ? 'bi-book' : 'bi-book-fill'"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark text-capitalize" style="font-size: 0.95rem;">{{ libro.titulo }}</div>
                                        <span class="badge bg-light text-secondary border rounded-1 px-1" style="font-size: 0.65rem; font-weight: normal; letter-spacing: 0.5px;">
                                            LIB-{{ libro.id }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Categoría Estilizada -->
                            <td>
                                <span class="badge rounded-pill px-3 py-2 text-capitalize text-dark" 
                                      style="background-color: #e9ecef; border: 1px solid #ced4da; font-weight: 600;">
                                    {{ libro.categoria }}
                                </span>
                            </td>
                            
                            <!-- Autor y Editorial -->
                            <td class="text-dark text-capitalize fw-semibold">{{ libro.autor }}</td>
                            <td class="text-muted small text-capitalize">{{ libro.editorial || '-' }}</td>
                            
                            <!-- Semáforo de Stock Mejorado -->
                            <td>
                                <div class="d-flex justify-content-between small mb-1 fw-bold">
                                    <span :class="getColorTexto(libro)">
                                        <i class="bi" :class="getIconoStock(libro)"></i> {{ libro.stock_disponible }} disp.
                                    </span>
                                    <span class="text-muted">/ {{ libro.stock_total }}</span>
                                </div>
                                <div class="progress shadow-sm" style="height: 8px; background-color: #e9ecef;">
                                    <div class="progress-bar" 
                                         :class="getColorBarra(libro)"
                                         role="progressbar" 
                                         :style="{ width: (libro.stock_disponible / libro.stock_total * 100) + '%' }">
                                    </div>
                                </div>
                            </td>

                            <!-- Acciones con Tooltip -->
                            <td class="text-end pe-4">
                                <button @click="editar(libro)" class="btn btn-sm btn-light text-primary me-2 border shadow-sm hover-scale" title="Editar información del libro">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button @click="eliminar(libro)" class="btn btn-sm btn-light text-danger border shadow-sm hover-scale" title="Eliminar o dar de baja">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <tr v-if="libros.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-search display-4 opacity-25"></i></div>
                                No se encontraron libros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- PAGINACIÓN -->
        <div class="card-footer bg-white border-top-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted fw-bold">Página {{ pagination.current_page }} de {{ pagination.total_pages }}</small>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-1 fw-bold" :disabled="pagination.current_page <= 1" @click="cargarLibros(pagination.current_page - 1)">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </button>
                    <button class="btn btn-sm btn-outline-secondary fw-bold" :disabled="pagination.current_page >= pagination.total_pages" @click="cargarLibros(pagination.current_page + 1)">
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL NUEVO/EDITAR (Validación Visual) -->
    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-2 me-3" style="background-color: #fdf2f4; color: #8B1538;">
                            <i class="bi bi-journal-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">{{ modal.titulo }}</h5>
                            <p class="text-muted small mb-0">Información del material</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="modal.visible = false"></button>
                </div>
                <div class="modal-body pt-4">
                    
                    <!-- Campo Título con Validación -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Título <span class="text-danger">*</span></label>
                        <input type="text" v-model="libroForm.titulo" 
                               class="form-control form-control-lg text-capitalize" 
                               :class="{'is-invalid': errores.titulo}"
                               @input="errores.titulo = false"
                               placeholder="Ej. Cien Años de Soledad">
                        <div class="invalid-feedback">El título es obligatorio</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Campo Autor con Validación -->
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Autor <span class="text-danger">*</span></label>
                            <input type="text" v-model="libroForm.autor" 
                                   class="form-control text-capitalize" 
                                   :class="{'is-invalid': errores.autor}"
                                   @input="errores.autor = false"
                                   placeholder="Nombre del autor">
                             <div class="invalid-feedback">Falta el autor</div>
                        </div>
                        
                        <!-- Campo Categoría con Validación -->
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Categoría <span class="text-danger">*</span></label>
                            <input type="text" list="catList" v-model="libroForm.categoria" 
                                   class="form-control text-capitalize" 
                                   :class="{'is-invalid': errores.categoria}"
                                   @input="errores.categoria = false"
                                   placeholder="Buscar...">
                            <datalist id="catList"><option v-for="c in listaCategorias" :value="c"></option></datalist>
                            <div class="invalid-feedback">Falta la categoría</div>
                        </div>
                    </div>

                    <!-- Editorial (Opcional) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Editorial</label>
                        <input type="text" v-model="libroForm.editorial" class="form-control text-capitalize" placeholder="Ej. Santillana">
                    </div>

                    <!-- Stock con Stepper y Validación -->
                    <div class="mb-2 bg-light p-3 rounded border" :class="{'border-danger': errores.stock}">
                        <label class="form-label fw-bold text-dark small mb-2">Stock Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" @click="ajustarStock(-1)" style="width: 50px;"><i class="bi bi-dash-lg"></i></button>
                            <input type="number" v-model.number="libroForm.stock" 
                                   class="form-control text-center fw-bold fs-5 bg-white" 
                                   :class="{'is-invalid': errores.stock}"
                                   min="1">
                            <button class="btn btn-outline-secondary" type="button" @click="ajustarStock(1)" style="width: 50px;"><i class="bi bi-plus-lg"></i></button>
                        </div>
                        <div class="text-danger small mt-1" v-if="errores.stock">El stock debe ser mayor a 0</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light text-secondary fw-bold px-4" @click="modal.visible = false">Cancelar</button>
                    <button type="button" class="btn text-white fw-bold shadow-sm px-4 btn-gold" @click="guardarLibro">
                        <i class="bi bi-save-fill me-2"></i> Guardar
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
                    <h5 class="modal-title fw-bold">Importación Masiva</h5>
                    <button type="button" class="btn-close" @click="modalImportar = false"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-4"><i class="bi bi-file-earmark-spreadsheet display-1 text-success opacity-50"></i></div>
                    <p class="text-muted small">Orden CSV: <b>Título, Autor, Editorial, Categoría, Stock</b></p>
                    <input type="file" ref="fileInput" class="form-control mb-3" accept=".csv">
                    <button class="btn btn-success w-100 fw-bold" @click="subirArchivo">Procesar Archivo</button>
                </div>
            </div>
        </div>
    </div>

</div> <!-- Fin App -->
</div> <!-- Fin Wrapper -->

<style>
    /* Estilos para Botones de Categoría */
    .cat-btn { 
        background-color: white;
        border: 2px solid #dee2e6; 
        color: #6c757d; 
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .cat-btn:hover { 
        transform: translateY(-2px); 
        border-color: #343a40;
        color: #343a40;
    }
    .cat-active { 
        background-color: #212529 !important; 
        color: white !important; 
        border-color: #212529 !important; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
    }
    
    /* Utilidades */
    .btn-gold { background-color: #D4AF37; border: none; }
    .btn-gold:hover { background-color: #c4a030; transform: translateY(-1px); }
    .hover-scale:hover { transform: scale(1.1); }
    .cursor-pointer { cursor: pointer; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                filtros: { busqueda: '' },
                filtroActivo: 'TODO',
                orden: { columna: 'id', direccion: 'DESC' },
                listaCategorias: [],
                pagination: { current_page: 1, total_pages: 1, total_items: 0 },
                modal: { visible: false, titulo: 'Nuevo Libro' },
                modalImportar: false,
                libroForm: { id: null, titulo: '', autor: '', editorial: '', categoria: '', stock: 1 },
                errores: { titulo: false, autor: false, categoria: false, stock: false } // Estado de errores
            }
        },
        mounted() {
            this.cargarCategorias();
            this.cargarLibros();
        },
        methods: {
            async cargarCategorias() {
                const res = await fetch('../api/libros.php?get_categorias=true');
                this.listaCategorias = await res.json();
            },
            async cargarLibros(page = 1) {
                let url = `../api/libros.php?page=${page}&limit=10&sort=${this.orden.columna}&order=${this.orden.direccion}`;
                
                if(this.filtros.busqueda) url += `&q=${this.filtros.busqueda}`;
                if(this.filtroActivo !== 'TODO') url += `&categoria=${this.filtroActivo}`;

                const res = await fetch(url);
                const data = await res.json();
                this.libros = data.data;
                this.pagination = data.pagination;
            },
            ordenar(columna) {
                if (this.orden.columna === columna) {
                    this.orden.direccion = this.orden.direccion === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    this.orden.columna = columna;
                    this.orden.direccion = 'ASC';
                }
                this.cargarLibros(1);
            },
            getIconoOrden(columna) {
                if (this.orden.columna !== columna) return 'bi-arrow-down-up text-muted small opacity-25';
                return this.orden.direccion === 'ASC' ? 'bi-sort-down-alt text-dark' : 'bi-sort-up text-dark';
            },
            filtrar(categoria) {
                this.filtroActivo = categoria;
                this.cargarLibros(1);
            },
            getColorBarra(libro) {
                const pct = libro.stock_disponible / libro.stock_total;
                if (pct < 0.2) return 'bg-danger';
                if (pct < 0.5) return 'bg-warning';
                return 'bg-success';
            },
            getColorTexto(libro) {
                const pct = libro.stock_disponible / libro.stock_total;
                if (pct < 0.2) return 'text-danger';
                return 'text-dark';
            },
            getIconoStock(libro) {
                const pct = libro.stock_disponible / libro.stock_total;
                if (pct < 0.2) return 'bi-exclamation-circle-fill'; 
                if (pct < 0.5) return 'bi-dash-circle';             
                return 'bi-check-circle-fill text-success';         
            },
            abrirModalNuevo() {
                this.libroForm = { id: null, titulo: '', autor: '', editorial: '', categoria: '', stock: 1 };
                this.errores = { titulo: false, autor: false, categoria: false, stock: false }; // Reset errores
                this.modal.titulo = 'Registrar Nuevo Libro';
                this.modal.visible = true;
            },
            editar(libro) {
                this.libroForm = { ...libro, stock: libro.stock_total }; 
                this.errores = { titulo: false, autor: false, categoria: false, stock: false };
                this.modal.titulo = 'Editar Libro';
                this.modal.visible = true;
            },
            ajustarStock(cantidad) {
                let nuevo = parseInt(this.libroForm.stock) + cantidad;
                if (nuevo >= 1) {
                    this.libroForm.stock = nuevo;
                    this.errores.stock = false;
                }
            },
            async guardarLibro() {
                // Validación Visual Estricta
                this.errores.titulo = !this.libroForm.titulo;
                this.errores.autor = !this.libroForm.autor;
                this.errores.categoria = !this.libroForm.categoria;
                this.errores.stock = this.libroForm.stock < 1;

                if(this.errores.titulo || this.errores.autor || this.errores.categoria || this.errores.stock) {
                    // Mostrar alerta visual si falta algo
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos incompletos',
                        text: 'Por favor complete todos los campos marcados con (*)',
                        confirmButtonColor: '#8B1538'
                    });
                    return;
                }

                const res = await fetch('../api/libros.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(this.libroForm) 
                });
                const data = await res.json();
                
                if(data.exito) {
                    this.modal.visible = false;
                    Swal.fire({ icon: 'success', title: 'Guardado', text: 'El libro se registró correctamente', timer: 1500, showConfirmButton: false });
                    this.cargarLibros(this.pagination.current_page);
                    this.cargarCategorias(); 
                } else {
                    Swal.fire('Error', data.mensaje, 'error');
                }
            },
            async eliminar(libro) {
                const result = await Swal.fire({
                    title: '¿Estás seguro?', text: `Se eliminará "${libro.titulo}"`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar'
                });
                if (result.isConfirmed) {
                    const res = await fetch(`../api/libros.php?id=${libro.id}`, { method: 'DELETE' });
                    const data = await res.json();
                    if(data.exito) {
                        this.cargarLibros(this.pagination.current_page);
                        this.cargarCategorias();
                        Swal.fire('Eliminado', 'El libro ha sido eliminado.', 'success');
                    } else {
                        Swal.fire('Error', data.mensaje, 'error');
                    }
                }
            },
            abrirModalImportar() { this.modalImportar = true; },
            async subirArchivo() {
                const file = this.$refs.fileInput.files[0];
                if(!file) return Swal.fire('Atención', 'Seleccione un archivo CSV', 'info');
                const formData = new FormData();
                formData.append('archivo_csv', file);
                const res = await fetch('../api/libros.php', { method: 'POST', body: formData });
                const data = await res.json();
                Swal.fire(data.exito ? 'Éxito' : 'Error', data.mensaje, data.exito ? 'success' : 'error');
                this.modalImportar = false;
                this.cargarLibros();
                this.cargarCategorias();
            }
        }
    }).mount('#app')
</script>
</body>
</html>