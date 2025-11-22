<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Biblioteca Virtual</h3>
            <p class="text-muted mb-0">Recursos digitales, libros y guías de estudio.</p>
        </div>
        <div class="text-end d-none d-md-block">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-book me-1"></i> {{ librosFiltrados.length }} Recursos disponibles
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group border rounded bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar por título, autor o tema...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select v-model="filtroCategoria" class="form-select border-0 bg-light shadow-none cursor-pointer">
                        <option value="">Todas las Categorías</option>
                        <option v-for="cat in categorias" :value="cat">{{ cat }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100 border-0" @click="limpiarFiltros" v-if="busqueda || filtroCategoria">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3" v-for="libro in librosFiltrados" :key="libro.id">
            <div class="card h-100 border-0 shadow-sm hover-card">
                
                <div class="card-body text-center pt-4 pb-2">
                    <div class="mb-3 position-relative d-inline-block">
                        <div class="rounded-circle p-4" style="background-color: #fdf2f4; color: #8B1538;">
                            <i class="bi bi-file-earmark-pdf-fill display-4"></i>
                        </div>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-light">
                            PDF
                        </span>
                    </div>
                    
                    <h6 class="fw-bold text-dark mb-1 line-clamp-2" :title="libro.titulo">
                        {{ libro.titulo }}
                    </h6>
                    <p class="text-muted small mb-2 text-uppercase" style="font-size: 0.7rem;">
                        {{ libro.autor }}
                    </p>
                    
                    <span class="badge bg-light text-secondary border fw-normal mb-3">
                        {{ libro.categoria }}
                    </span>
                </div>

                <div class="card-footer bg-white border-top-0 pb-4 px-4 pt-0">
                    <a :href="libro.url_digital" target="_blank" class="btn btn-vino w-100 fw-bold shadow-sm btn-hover-effect">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Abrir Recurso
                    </a>
                </div>
            </div>
        </div>

        <div v-if="librosFiltrados.length === 0" class="col-12 text-center py-5">
            <div class="opacity-50 mb-3">
                <i class="bi bi-folder2-open display-1 text-secondary"></i>
            </div>
            <h5 class="text-muted">No se encontraron recursos</h5>
            <p class="text-muted small">Intenta con otros términos de búsqueda.</p>
        </div>
    </div>

</div>

<style>
    /* Estilos Personalizados */
    .btn-vino { background-color: #8B1538; color: white; border: none; transition: all 0.2s; }
    .btn-vino:hover { background-color: #6b0f2a; color: white; transform: translateY(-2px); }
    
    .hover-card { transition: transform 0.2s, box-shadow 0.2s; border: 1px solid rgba(0,0,0,0.05) !important; }
    .hover-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.5em; /* Ajuste para altura uniforme */
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                busqueda: '',
                filtroCategoria: ''
            }
        },
        computed: {
            // Obtener lista única de categorías para el select
            categorias() {
                const cats = this.libros.map(l => l.categoria).filter(c => c);
                return [...new Set(cats)].sort();
            },
            // Lógica de filtrado
            librosFiltrados() {
                return this.libros.filter(l => {
                    const texto = this.busqueda.toLowerCase();
                    const matchTexto = l.titulo.toLowerCase().includes(texto) || 
                                       l.autor.toLowerCase().includes(texto) ||
                                       l.categoria.toLowerCase().includes(texto);
                    
                    const matchCat = !this.filtroCategoria || l.categoria === this.filtroCategoria;
                    
                    return matchTexto && matchCat;
                });
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                try {
                    // Usamos el endpoint existente de libros
                    const res = await fetch('../api/libros.php?limit=1000');
                    const data = await res.json();
                    
                    // FILTRO CLAVE: Solo libros que tengan URL (url_digital no vacía)
                    this.libros = data.data.filter(l => l.url_digital && l.url_digital.trim().length > 5);
                } catch(e) {
                    console.error("Error cargando biblioteca virtual:", e);
                }
            },
            limpiarFiltros() {
                this.busqueda = '';
                this.filtroCategoria = '';
            }
        }
    }).mount('#app')
</script>
</body>
</html>