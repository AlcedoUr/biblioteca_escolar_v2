<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="mb-4">
        <h3 class="fw-bold" style="color: #8B1538;">Catálogo Bibliográfico</h3>
        <p class="text-muted">Explore los libros disponibles para reservar</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3"> <div class="row g-2">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group border rounded bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" v-model="busqueda" class="form-control border-0 bg-transparent" placeholder="Buscar por título, autor...">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select v-model="filtroCategoria" class="form-select border bg-light text-muted cursor-pointer h-100">
                        <option value="">Todas las Categorías</option>
                        <option v-for="cat in listaCategorias" :value="cat">{{ cat }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4 col-lg-3" v-for="libro in librosFiltrados" :key="libro.id">
            <div class="card h-100 border-0 shadow-sm hover-up">
                
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-secondary position-relative" style="height: 180px; font-size: 3rem;">
                    <i class="bi bi-book"></i>
                    
                    <span class="position-absolute top-0 end-0 m-2 badge rounded-pill shadow-sm" 
                          :class="getClassStock(libro)" 
                          style="font-size: 0.75rem; font-weight: 600;">
                        {{ libro.stock_disponible }} Disp.
                    </span>
                </div>
                
                <div class="card-body d-flex flex-column p-3">
                    <h6 class="fw-bold text-dark mb-1 line-clamp-2 lh-sm" :title="libro.titulo">{{ libro.titulo }}</h6>
                    <p class="text-muted small mb-2 text-uppercase" style="font-size: 0.7rem;">{{ libro.autor }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.65rem;">
                            {{ libro.categoria }}
                        </span>
                    </div>
                    
                    <div class="mt-auto">
                        <button class="btn w-100 py-1 fw-bold btn-sm shadow-sm btn-outline-vino" 
                                :class="{'disabled': libro.stock_disponible <= 0, 'btn-light text-muted border-0': libro.stock_disponible <= 0}"
                                :disabled="libro.stock_disponible <= 0"
                                @click="reservar(libro)">
                            <i class="bi bi-calendar-plus me-2"></i>{{ libro.stock_disponible > 0 ? 'Reservar' : 'Agotado' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 text-center py-5 text-muted" v-if="librosFiltrados.length === 0">
            <i class="bi bi-search display-4 mb-3 d-block opacity-25"></i>
            <p>No encontramos libros con ese criterio.</p>
        </div>
    </div>

</div>

<style>
    .hover-up { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-up:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important; }
    
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.4em; }
    
    .btn-outline-vino { color: #8B1538; border: 1px solid #8B1538; background-color: white; transition: all 0.2s; }
    .btn-outline-vino:hover:not(.disabled) { background-color: #8B1538; color: white; transform: translateY(-1px); }
    
    /* Ajuste para select */
    .form-select:focus, .form-control:focus { box-shadow: none; border-color: #8B1538; }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                listaCategorias: [],
                busqueda: '',
                filtroCategoria: ''
            }
        },
        computed: {
            librosFiltrados() {
                if (!this.libros) return [];
                return this.libros.filter(l => {
                    const t = this.busqueda.toLowerCase();
                    
                    // Filtro Texto
                    const matchTexto = l.titulo.toLowerCase().includes(t) || 
                                       l.autor.toLowerCase().includes(t) ||
                                       (l.isbn && l.isbn.includes(t));
                    
                    // Filtro Categoría
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
                    // Cargar Categorías
                    const resCat = await fetch('../api/libros.php?get_categorias=true');
                    this.listaCategorias = await resCat.json();

                    // Cargar Libros
                    const resLib = await fetch('../api/libros.php?limit=1000'); // Traer todo para filtrar en front
                    const dataLib = await resLib.json();
                    this.libros = dataLib.data ? dataLib.data : dataLib; 
                } catch(e) { console.error(e); }
            },
            
            // Lógica de colores idéntica a libros.php
            getClassStock(libro) {
                if (parseInt(libro.stock_disponible) === 0) return 'bg-secondary text-white'; // Agotado total
                
                const total = parseInt(libro.stock_total);
                const disp = parseInt(libro.stock_disponible);
                
                if (total === 0) return 'bg-secondary'; // Evitar división por cero
                
                const porcentaje = disp / total;

                // Menos del 20% -> Rojo (Peligro)
                if (porcentaje < 0.2) return 'bg-danger text-white';
                
                // Menos del 50% -> Amarillo (Advertencia)
                if (porcentaje < 0.5) return 'bg-warning text-dark';
                
                // Más del 50% -> Verde (Bien)
                return 'bg-success text-white';
            },

            reservar(libro) {
                window.location.href = `reservas.php?book_id=${libro.id}&book_title=${encodeURIComponent(libro.titulo)}`;
            }
        }
    }).mount('#app')
</script>