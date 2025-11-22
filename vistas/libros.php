<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Catálogo de Libros</h3>
            <p class="text-muted">Gestión de inventario y recursos bibliográficos</p>
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <div class="input-group border border-2 rounded-pill bg-light overflow-hidden">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="filtros.busqueda" @input="cargarLibros(1)" class="form-control border-0 bg-transparent" placeholder="Buscar por ISBN, título, autor...">
                    </div>
                </div>
                <div class="col-md-7 text-md-end">
                    <div class="d-inline-flex gap-2 overflow-auto pb-1 align-items-center" style="max-width: 100%; white-space: nowrap;">
                        <span class="text-muted small fw-bold text-uppercase me-2">Categorías:</span>
                        <button class="btn btn-sm rounded-pill px-3 fw-bold cat-btn" :class="filtroActivo === 'TODO' ? 'cat-active' : 'cat-inactive'" @click="filtrar('TODO')">Todo</button>
                        <button v-for="cat in listaCategorias" class="btn btn-sm rounded-pill px-3 fw-bold cat-btn text-capitalize" :class="filtroActivo === cat ? 'cat-active' : 'cat-inactive'" @click="filtrar(cat)">
                            {{ cat }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3 text-center" style="width: 150px;">ISBN / Código</th>
                            <th class="cursor-pointer user-select-none" @click="ordenar('titulo')">Libro <i class="bi" :class="getIconoOrden('titulo')"></i></th>
                            <th>Autor</th>
                            <th>Categoría</th>
                            <th class="text-center">Recurso</th>
                            <th style="width: 180px;" class="cursor-pointer user-select-none" @click="ordenar('stock_total')">Stock <i class="bi" :class="getIconoOrden('stock_total')"></i></th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="libro in libros" :key="libro.id">
                            <td class="ps-4 text-center">
                                <div class="badge bg-light text-dark border font-monospace text-truncate" style="max-width: 140px;">
                                    <i class="bi bi-upc-scan me-1 text-muted"></i>{{ libro.isbn || 'S/N' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.6rem;">ID: {{ libro.id }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3 rounded bg-white text-secondary d-flex align-items-center justify-content-center fs-4 fw-bold border shadow-sm" style="width: 40px; height: 55px;">{{ libro.titulo.charAt(0) }}</div>
                                    <div>
                                        <div class="fw-bold text-dark text-capitalize" style="font-size: 0.95rem;">{{ libro.titulo }}</div>
                                        <small class="text-muted text-capitalize">{{ libro.editorial || 'Sin editorial' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-dark text-capitalize fw-semibold text-muted">{{ libro.autor }}</td>
                            <td><span class="badge bg-light text-secondary border rounded-pill">{{ libro.categoria }}</span></td>
                            <td class="text-center">
                                <a v-if="libro.url_digital" :href="libro.url_digital" target="_blank" class="btn btn-sm btn-outline-info border-0 rounded-circle" title="Ver Recurso Digital"><i class="bi bi-link-45deg fs-5"></i></a>
                                <span v-else class="text-muted opacity-25">-</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-between small mb-1 fw-bold">
                                    <span :class="getColorTexto(libro)"><i class="bi" :class="getIconoStock(libro)"></i> {{ libro.stock_disponible }}</span>
                                    <span class="text-muted">/ {{ libro.stock_total }}</span>
                                </div>
                                <div class="progress shadow-sm" style="height: 6px;">
                                    <div class="progress-bar" :class="getColorBarra(libro)" role="progressbar" :style="{ width: (libro.stock_disponible / libro.stock_total * 100) + '%' }"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button @click="editar(libro)" class="btn btn-sm btn-light text-primary me-1 border shadow-sm hover-scale" title="Editar"><i class="bi bi-pencil-fill"></i></button>
                                <button @click="eliminar(libro)" class="btn btn-sm btn-light text-danger border shadow-sm hover-scale" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                        <tr v-if="libros.length === 0"><td colspan="7" class="text-center py-5 text-muted">No se encontraron libros.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted fw-bold">Página {{ pagination.current_page }} de {{ pagination.total_pages }}</small>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-1 fw-bold" :disabled="pagination.current_page <= 1" @click="cargarLibros(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i> Anterior</button>
                    <button class="btn btn-sm btn-outline-secondary fw-bold" :disabled="pagination.current_page >= pagination.total_pages" @click="cargarLibros(pagination.current_page + 1)">Siguiente <i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
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
                <div class="modal-body pt-4 px-4">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">ISBN / Código <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc"></i></span>
                                <input type="text" 
                                       v-model="libroForm.isbn" 
                                       @blur="aplicarFormatoISBN"
                                       @keyup.enter="aplicarFormatoISBN"
                                       class="form-control border-start-0 font-monospace" 
                                       :class="{'is-invalid': errores.isbn}"
                                       placeholder="Ingrese números..." 
                                       maxlength="17">
                            </div>
                            <div class="invalid-feedback d-block" v-if="errores.isbn">ISBN requerido (10 o 13 dígitos)</div>
                            <small class="text-muted fst-italic" style="font-size: 0.7rem;">Ej: 978-1-234-56789-0</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">Título del Libro <span class="text-danger">*</span></label>
                            <input type="text" v-model="libroForm.titulo" class="form-control text-capitalize" :class="{'is-invalid': errores.titulo}" @input="errores.titulo = false">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Autor <span class="text-danger">*</span></label>
                            <input type="text" v-model="libroForm.autor" class="form-control text-capitalize" :class="{'is-invalid': errores.autor}" @input="errores.autor = false">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Categoría <span class="text-danger">*</span></label>
                            <input type="text" list="catList" v-model="libroForm.categoria" class="form-control text-capitalize" :class="{'is-invalid': errores.categoria}" @input="errores.categoria = false">
                            <datalist id="catList"><option v-for="c in listaCategorias" :value="c"></option></datalist>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Editorial</label>
                            <input type="text" v-model="libroForm.editorial" class="form-control text-capitalize">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Stock Total <span class="text-danger">*</span></label>
                            <input type="number" v-model.number="libroForm.stock" class="form-control text-center fw-bold" min="1" :class="{'is-invalid': errores.stock}" @input="errores.stock = false">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">URL Recurso (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" v-model="libroForm.url_digital" class="form-control border-start-0" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 pt-2 pb-4 px-4">
                    <button type="button" class="btn btn-light text-secondary fw-bold px-4" @click="modal.visible = false">Cancelar</button>
                    <button type="button" class="btn text-white fw-bold shadow-sm px-4 btn-gold" @click="guardarLibro">
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
                        Formato CSV requerido:<br>
                        <b>ISBN, Título, Autor, Editorial, Categoría, Stock, URL</b>
                    </p>
                    <input type="file" ref="fileInput" class="form-control mb-3" accept=".csv">
                    <button class="btn btn-success w-100 fw-bold" @click="subirArchivo">Procesar Archivo</button>
                </div>
            </div>
        </div>
    </div>

</div> 

<style>
    .cat-btn { background-color: white; border: 2px solid #dee2e6; color: #6c757d; transition: all 0.2s; }
    .cat-btn:hover { transform: translateY(-2px); border-color: #343a40; color: #343a40; }
    .cat-active { background-color: #212529 !important; color: white !important; border-color: #212529 !important; }
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
                libroForm: { id: null, isbn: '', titulo: '', autor: '', editorial: '', categoria: '', stock: 1, url_digital: '' },
                errores: { isbn: false, titulo: false, autor: false, categoria: false, stock: false }
            }
        },
        mounted() {
            this.cargarCategorias();
            this.cargarLibros();
        },
        methods: {
            // === LÓGICA DE FORMATEO ISBN ===
            aplicarFormatoISBN() {
                if (!this.libroForm.isbn) return;
                
                // 1. Limpiar: Dejar solo números
                let limpio = this.libroForm.isbn.replace(/[^0-9X]/gi, ''); // Permitir X para ISBN-10 antiguos
                
                // 2. Detectar longitud y aplicar guiones
                if (limpio.length === 13) {
                    // Formato ISBN-13: 978-1-234-56789-0 (Genérico 3-1-3-5-1)
                    // Nota: Los grupos reales varían, esto es un formateo visual estándar
                    this.libroForm.isbn = `${limpio.slice(0,3)}-${limpio.slice(3,4)}-${limpio.slice(4,8)}-${limpio.slice(8,12)}-${limpio.slice(12)}`;
                    this.errores.isbn = false;
                } else if (limpio.length === 10) {
                    // Formato ISBN-10: 0-12-345678-9 (Genérico 1-2-6-1)
                    this.libroForm.isbn = `${limpio.slice(0,1)}-${limpio.slice(1,3)}-${limpio.slice(3,9)}-${limpio.slice(9)}`;
                    this.errores.isbn = false;
                } else {
                    // Si no cumple longitud, dejamos limpio pero no marcamos error estricto hasta guardar
                    // o lo dejamos como el usuario lo escribió si no es estándar
                    if(limpio.length > 0) this.libroForm.isbn = limpio; 
                }
            },

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
                this.libroForm = { id: null, isbn: '', titulo: '', autor: '', editorial: '', categoria: '', stock: 1, url_digital: '' };
                this.errores = { isbn: false, titulo: false, autor: false, categoria: false, stock: false }; 
                this.modal.titulo = 'Registrar Nuevo Libro';
                this.modal.visible = true;
            },
            editar(libro) {
                this.libroForm = { ...libro, stock: libro.stock_total }; 
                this.errores = { isbn: false, titulo: false, autor: false, categoria: false, stock: false };
                this.modal.titulo = 'Editar Libro';
                this.modal.visible = true;
            },
            ajustarStock(cantidad) {
                let nuevo = parseInt(this.libroForm.stock) + cantidad;
                if (nuevo >= 1) this.libroForm.stock = nuevo;
            },
            async guardarLibro() {
                // Antes de validar, aseguramos formato ISBN
                this.aplicarFormatoISBN();

                this.errores.titulo = !this.libroForm.titulo;
                this.errores.autor = !this.libroForm.autor;
                this.errores.categoria = !this.libroForm.categoria;
                this.errores.stock = this.libroForm.stock < 1;
                // ISBN opcional pero si se pone debe ser válido (o al menos no vacío si lo marcas obligatorio)
                this.errores.isbn = !this.libroForm.isbn; 

                if(this.errores.titulo || this.errores.autor || this.errores.categoria || this.errores.stock || this.errores.isbn) {
                    Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Por favor complete los campos obligatorios (*)', confirmButtonColor: '#8B1538' });
                    return;
                }

                try {
                    const res = await fetch('../api/libros.php', { 
                        method: 'POST', 
                        headers: { 'Content-Type': 'application/json' }, 
                        body: JSON.stringify(this.libroForm) 
                    });
                    
                    // Verificación de respuesta si no es JSON válido
                    if (!res.ok) throw new Error("Error en el servidor");
                    
                    const data = await res.json();
                    
                    if(data.exito) {
                        this.modal.visible = false;
                        Swal.fire({ icon: 'success', title: 'Guardado', text: 'El libro se guardó correctamente', timer: 1500, showConfirmButton: false });
                        this.cargarLibros(this.pagination.current_page);
                        this.cargarCategorias(); 
                    } else {
                        Swal.fire('Error', data.mensaje || 'No se pudo guardar', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire('Error', 'Ocurrió un problema de conexión con el servidor.', 'error');
                }
            },
            async eliminar(libro) {
                const result = await Swal.fire({ title: '¿Estás seguro?', text: `Se eliminará "${libro.titulo}"`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar' });
                if (result.isConfirmed) {
                    const res = await fetch(`../api/libros.php?id=${libro.id}`, { method: 'DELETE' });
                    const data = await res.json();
                    if(data.exito) {
                        this.cargarLibros(this.pagination.current_page);
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