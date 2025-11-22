<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca Virtual - Acceso Libre</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F5F6FA; }
        .bg-vino { background-color: #8B1538; }
        .text-vino { color: #8B1538; }
        .btn-vino { background-color: #8B1538; color: white; border: none; transition: all 0.2s; }
        .btn-vino:hover { background-color: #6b0f2a; color: white; transform: translateY(-2px); }
        
        .hover-card { transition: transform 0.2s, box-shadow 0.2s; border: 1px solid rgba(0,0,0,0.05) !important; }
        .hover-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.5em; }
        
        /* Navbar estilo público */
        .public-navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div id="app">

    <nav class="navbar navbar-expand-lg navbar-dark bg-vino public-navbar mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
                <i class="bi bi-book-half fs-4"></i>
                <span>Biblioteca Virtual</span>
            </a>
            <div class="d-flex">
                <a href="../index.php" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Acceso Administrativo
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="text-center mb-5">
            <h2 class="fw-bold text-vino">Recursos Digitales Disponibles</h2>
            <p class="text-muted">Explora nuestra colección de libros y documentos de libre acceso.</p>
            <div class="d-inline-block bg-white px-3 py-1 rounded-pill border shadow-sm text-muted small">
                <i class="bi bi-check-circle-fill text-success me-1"></i> {{ librosFiltrados.length }} recursos encontrados
            </div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                                    <input type="text" v-model="busqueda" class="form-control border-0 shadow-none" placeholder="Buscar por título, autor o tema...">
                                </div>
                            </div>
                            <div class="col-md-4 border-start">
                                <select v-model="filtroCategoria" class="form-select border-0 shadow-none cursor-pointer text-muted">
                                    <option value="">Todas las Categorías</option>
                                    <option v-for="cat in categorias" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3" v-for="libro in librosFiltrados" :key="libro.id">
                <div class="card h-100 border-0 shadow-sm hover-card bg-white">
                    
                    <div class="card-body text-center pt-4 pb-2">
                        <div class="mb-3 position-relative d-inline-block">
                            <div class="rounded-circle p-3" style="background-color: #fdf2f4; color: #8B1538;">
                                <i class="bi bi-file-earmark-pdf-fill display-4"></i>
                            </div>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-light">PDF</span>
                        </div>
                        
                        <h6 class="fw-bold text-dark mb-1 line-clamp-2" :title="libro.titulo">
                            {{ libro.titulo }}
                        </h6>
                        <p class="text-muted small mb-2 text-uppercase" style="font-size: 0.75rem;">
                            {{ libro.autor }}
                        </p>
                        
                        <span class="badge bg-light text-secondary border fw-normal mb-3">
                            {{ libro.categoria }}
                        </span>
                    </div>

                    <div class="card-footer bg-white border-top-0 pb-4 px-4 pt-0">
                        <a :href="libro.url_digital" target="_blank" class="btn btn-vino w-100 fw-bold shadow-sm">
                            <i class="bi bi-cloud-download me-2"></i>Descargar / Ver
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="librosFiltrados.length === 0" class="col-12 text-center py-5">
                <div class="opacity-25 mb-3">
                    <i class="bi bi-journal-x display-1 text-secondary"></i>
                </div>
                <h5 class="text-muted">No se encontraron recursos</h5>
                <p class="text-muted small">Intenta buscar con otros términos.</p>
            </div>
        </div>

    </div>
    
    <footer class="text-center py-4 text-muted small mt-5 border-top">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> Biblioteca Digital - Acceso Público</p>
    </footer>

</div>

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
            categorias() {
                const cats = this.libros.map(l => l.categoria).filter(c => c);
                return [...new Set(cats)].sort();
            },
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
                    // El endpoint api/libros.php NO requiere login, así que funcionará perfecto
                    const res = await fetch('../api/libros.php?limit=1000');
                    const data = await res.json();
                    // Solo mostramos libros que tengan URL digital
                    this.libros = data.data.filter(l => l.url_digital && l.url_digital.trim().length > 5);
                } catch(e) {
                    console.error("Error cargando biblioteca:", e);
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>