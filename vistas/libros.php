<?php include 'includes/header.php'; ?>

<div id="app">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Gestión de Libros</h3>
            <p class="text-muted">Administre el inventario físico</p>
        </div>
        <button class="btn text-white" style="background-color: #8B1538;" @click="mostrarFormulario = !mostrarFormulario">
            <i class="bi bi-plus-lg"></i> Nuevo Libro
        </button>
    </div>

    <div v-if="mostrarFormulario" class="card border-0 shadow mb-4 animate-fade-in">
        <div class="card-body">
            <h5 class="card-title text-primary mb-3">Registrar Libro</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Título</label>
                    <input type="text" v-model="nuevoLibro.titulo" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Autor</label>
                    <input type="text" v-model="nuevoLibro.autor" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Stock</label>
                    <input type="number" v-model="nuevoLibro.stock" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Ubicación</label>
                    <input type="text" v-model="nuevoLibro.ubicacion" class="form-control" placeholder="Ej. E-10">
                </div>
                <div class="col-md-12 text-end">
                    <button class="btn btn-secondary me-2" @click="mostrarFormulario = false">Cancelar</button>
                    <button class="btn btn-success" @click="guardarLibro">Guardar Libro</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" v-model="busqueda" class="form-control border-0" placeholder="Buscar libro...">
                </div>
            </div>

            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase">
                        <th class="ps-4">ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="libro in librosFiltrados" :key="libro.id">
                        <td class="ps-4 fw-bold text-secondary">#{{ libro.id }}</td>
                        <td class="fw-semibold">{{ libro.titulo }}</td>
                        <td class="text-muted">{{ libro.autor }}</td>
                        <td class="small"><i class="bi bi-geo-alt"></i> {{ libro.ubicacion }}</td>
                        
                        <td>
                            <span v-if="libro.stock_disponible > 0" class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                Disponible
                            </span>
                            <span v-else class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                Agotado
                            </span>
                        </td>

                        <td class="fw-bold">
                            {{ libro.stock_disponible }} / {{ libro.stock_total }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div> </div> 

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                listaLibros: [],
                mostrarFormulario: false,
                busqueda: '',
                nuevoLibro: { titulo: '', autor: '', ubicacion: '', stock: '' }
            }
        },
        computed: {
            librosFiltrados() {
                return this.listaLibros.filter(l => 
                    l.titulo.toLowerCase().includes(this.busqueda.toLowerCase()) ||
                    l.autor.toLowerCase().includes(this.busqueda.toLowerCase())
                );
            }
        },
        mounted() {
            this.cargarLibros();
        },
        methods: {
            async cargarLibros() {
                const respuesta = await fetch('../api/libros.php');
                this.listaLibros = await respuesta.json();
            },
            async guardarLibro() {
                if(!this.nuevoLibro.titulo || !this.nuevoLibro.stock) {
                    alert("Complete los datos obligatorios");
                    return;
                }
                const respuesta = await fetch('../api/libros.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.nuevoLibro)
                });
                const dato = await respuesta.json();
                if(dato.exito) {
                    this.mostrarFormulario = false;
                    this.nuevoLibro = { titulo: '', autor: '', ubicacion: '', stock: '' };
                    this.cargarLibros(); 
                } else {
                    alert("Error: " + dato.mensaje);
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>