<?php include 'includes/header.php'; ?>

<div id="app">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Catálogo de Libros</h3>
            <p class="text-muted">Gestión de inventario y existencias</p>
        </div>
        <div>
            <button class="btn btn-outline-success me-2 shadow-sm" @click="abrirModalImportar">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar Excel
            </button>
            <button class="btn text-white shadow-sm btn-hover-gold" style="background-color: #8B1538;" @click="abrirModalNuevo">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Libro
            </button>
        </div>
    </div>

    <!-- BARRA DE HERRAMIENTAS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <!-- Buscador -->
                <div class="col-md-4">
                    <div class="input-group border rounded bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="filtros.busqueda" @input="cargarLibros(1)" class="form-control border-0 bg-transparent" placeholder="Buscar título, autor o editorial...">
                    </div>
                </div>
                
                <!-- Filtros Dinámicos (Categorías) -->
                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <span class="text-muted small me-2">Categoría:</span>
                    
                    <button class="btn btn-sm rounded-pill me-1" 
                            :class="filtroActivo === 'TODO' ? 'btn-dark' : 'btn-light border'" 
                            @click="filtrar('TODO')">Todo</button>
                    
                    <button v-for="cat in listaCategorias" 
                            class="btn btn-sm rounded-pill me-1" 
                            :class="filtroActivo === cat ? 'btn-dark' : 'btn-light border'" 
                            @click="filtrar(cat)">
                        {{ cat }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE LIBROS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4">Libro</th>
                            <th>Categoría</th>
                            <th>Autor</th>
                            <th>Editorial</th>
                            <th style="width: 200px;">Estado del Stock</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="libro in libros" :key="libro.id">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 rounded bg-light text-secondary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 55px; border: 1px solid #eee;">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ libro.titulo }}</div>
                                        <small class="text-muted">COD: LIB-{{ libro.id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ libro.categoria }}</span></td>
                            <td class="text-secondary fw-semibold">{{ libro.autor }}</td>
                            <td class="text-muted small">{{ libro.editorial || '-' }}</td>
                            
                            <!-- SEMÁFORO DE STOCK -->
                            <td>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold" :class="getColorTexto(libro)">{{ libro.stock_disponible }} disponibles</span>
                                    <span class="text-muted">de {{ libro.stock_total }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" 
                                         :class="getColorBarra(libro)"
                                         role="progressbar" 
                                         :style="{ width: (libro.stock_disponible / libro.stock_total * 100) + '%' }">
                                    </div>
                                </div>
                            </td>

                            <td class="text-end pe-4">
                                <button @click="editar(libro)" class="btn btn-sm btn-light text-primary me-2" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button @click="eliminar(libro)" class="btn btn-sm btn-light text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr v-if="libros.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
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
                <small class="text-muted">Página {{ pagination.current_page }} de {{ pagination.total_pages }} (Total: {{ pagination.total_items }})</small>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-1" 
                            :disabled="pagination.current_page <= 1"
                            @click="cargarLibros(pagination.current_page - 1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" 
                            :disabled="pagination.current_page >= pagination.total_pages"
                            @click="cargarLibros(pagination.current_page + 1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL NUEVO/EDITAR (REDDISEÑADO) -->
    <div v-if="modal.visible" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                
                <!-- CABECERA LIMPIA -->
                <div class="modal-header border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-2 me-3" style="background-color: #fdf2f4; color: #8B1538;">
                            <i class="bi bi-journal-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">{{ modal.titulo }}</h5>
                            <p class="text-muted small mb-0">Complete la información del material</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="modal.visible = false"></button>
                </div>

                <div class="modal-body pt-4">
                    
                    <!-- CAMPO TÍTULO -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Título del Libro <span class="text-danger">*</span></label>
                        <input type="text" v-model="libroForm.titulo" 
                               class="form-control form-control-lg" 
                               :class="{'is-invalid': errores.titulo}"
                               placeholder="Ej. Cien Años de Soledad">
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- CAMPO AUTOR -->
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Autor <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" v-model="libroForm.autor" class="form-control border-start-0" placeholder="Nombre del autor">
                            </div>
                        </div>
                        
                        <!-- CAMPO CATEGORÍA -->
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small">Categoría <span class="text-danger">*</span></label>
                            <input type="text" list="catList" v-model="libroForm.categoria" class="form-control" placeholder="Buscar...">
                            <datalist id="catList">
                                <option v-for="c in listaCategorias" :value="c"></option>
                            </datalist>
                        </div>
                    </div>

                    <!-- CAMPO EDITORIAL (VISIBLE AHORA) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Editorial</label>
                        <input type="text" v-model="libroForm.editorial" class="form-control" placeholder="Ej. Santillana">
                    </div>

                    <!-- CAMPO STOCK CON STEPPER -->
                    <div class="mb-2 bg-light p-3 rounded border">
                        <label class="form-label fw-bold text-dark small mb-2">Stock Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" @click="ajustarStock(-1)" style="width: 50px;"><i class="bi bi-dash-lg"></i></button>
                            <input type="number" v-model.number="libroForm.stock" class="form-control text-center fw-bold fs-5 bg-white" min="1">
                            <button class="btn btn-outline-secondary" type="button" @click="ajustarStock(1)" style="width: 50px;"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>

                </div>
                
                <!-- FOOTER CON BOTONES MEJORADOS -->
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light text-secondary fw-bold px-4" @click="modal.visible = false">
                        Cancelar
                    </button>
                    <button type="button" class="btn text-white fw-bold shadow-sm px-4 d-flex align-items-center btn-gold" @click="guardarLibro">
                        <i class="bi bi-save-fill me-2"></i> Guardar Datos
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
                    <p class="text-muted">Orden CSV: <b>Título, Autor, Editorial, Categoría, Stock</b></p>
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
    
    /* Estilo especial para el botón dorado del modal */
    .btn-gold {
        background-color: #D4AF37; 
        border: none;
        transition: all 0.2s;
    }
    .btn-gold:hover {
        background-color: #c4a030;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(212, 175, 55, 0.3);
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                filtros: { busqueda: '' },
                filtroActivo: 'TODO',
                listaCategorias: [],
                pagination: { current_page: 1, total_pages: 1, total_items: 0 },
                modal: { visible: false, titulo: 'Nuevo Libro' },
                modalImportar: false,
                libroForm: { id: null, titulo: '', autor: '', editorial: '', categoria: '', stock: 1 },
                errores: { titulo: false }
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
                let url = `../api/libros.php?page=${page}&limit=10`;
                if(this.filtros.busqueda) url += `&q=${this.filtros.busqueda}`;
                if(this.filtroActivo !== 'TODO') url += `&categoria=${this.filtroActivo}`;

                const res = await fetch(url);
                const data = await res.json();
                this.libros = data.data;
                this.pagination = data.pagination;
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
            abrirModalNuevo() {
                this.libroForm = { id: null, titulo: '', autor: '', editorial: '', categoria: '', stock: 1 };
                this.errores = { titulo: false };
                this.modal.titulo = 'Registrar Nuevo Libro';
                this.modal.visible = true;
            },
            editar(libro) {
                this.libroForm = { ...libro, stock: libro.stock_total }; 
                this.errores = { titulo: false };
                this.modal.titulo = 'Editar Libro';
                this.modal.visible = true;
            },
            ajustarStock(cantidad) {
                let nuevoStock = parseInt(this.libroForm.stock) + cantidad;
                if (nuevoStock >= 1) this.libroForm.stock = nuevoStock;
            },
            async guardarLibro() {
                // Validación visual
                this.errores.titulo = !this.libroForm.titulo;
                
                if(!this.libroForm.titulo || !this.libroForm.stock || !this.libroForm.autor || !this.libroForm.categoria) {
                    alert("Por favor complete los campos obligatorios (*)");
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
                    alert('✅ Guardado correctamente');
                    this.cargarLibros(this.pagination.current_page);
                    this.cargarCategorias(); 
                } else {
                    alert('❌ Error: ' + data.mensaje);
                }
            },
            async eliminar(libro) {
                if(!confirm(`¿Está seguro de eliminar el libro: "${libro.titulo}"?`)) return;
                const res = await fetch(`../api/libros.php?id=${libro.id}`, { method: 'DELETE' });
                const data = await res.json();
                if(data.exito) {
                    this.cargarLibros(this.pagination.current_page);
                    this.cargarCategorias();
                } else {
                    alert(data.mensaje);
                }
            },
            abrirModalImportar() { this.modalImportar = true; },
            async subirArchivo() {
                const file = this.$refs.fileInput.files[0];
                if(!file) return alert("Seleccione CSV");
                const formData = new FormData();
                formData.append('archivo_csv', file);
                const res = await fetch('../api/libros.php', { method: 'POST', body: formData });
                const data = await res.json();
                alert(data.mensaje);
                this.modalImportar = false;
                this.cargarLibros();
                this.cargarCategorias();
            }
        }
    }).mount('#app')
</script>
</body>
</html>