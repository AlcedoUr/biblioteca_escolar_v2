<?php include 'includes/header.php'; ?>

<div id="app">

    <div class="d-none d-print-block mb-3">
        <div class="text-center mb-2">
            <h5 class="fw-bold text-uppercase text-dark mb-0">I.E. Virgen de las Mercedes</h5>
            <p class="text-muted small mb-0" style="font-size: 0.8rem;">Biblioteca Escolar - Inventario</p>
        </div>
        
        <div class="border-top border-bottom py-1 small text-dark mb-2" style="font-size: 0.75rem;">
            <div class="row">
                <div class="col-6"><strong>Reporte:</strong> {{ tituloReporte }}</div>
                <div class="col-6 text-end"><strong>Generado:</strong> <?php echo date('d/m/Y H:i'); ?></div>
            </div>
            <div class="row">
                <div class="col-12"><strong>Filtro:</strong> {{ resumenFiltros }}</div>
            </div>
        </div>

        <div class="row g-0 border border-dark mb-3 text-center" style="font-size: 0.7rem;">
            <div class="col border-end border-dark p-1">
                <span class="fw-bold d-block">TOTAL TÍTULOS</span>
                <span>{{ librosFiltrados.length }}</span>
            </div>
            <div class="col border-end border-dark p-1">
                <span class="fw-bold d-block">DISPONIBLES</span>
                <span>{{ stats.disponibles }}</span>
            </div>
            <div class="col border-end border-dark p-1">
                <span class="fw-bold d-block">STOCK BAJO</span>
                <span>{{ stats.stockBajo }}</span>
            </div>
            <div class="col border-end border-dark p-1">
                <span class="fw-bold d-block">AGOTADOS</span>
                <span>{{ stats.agotados }}</span>
            </div>
            <div class="col p-1">
                <span class="fw-bold d-block">EN PRÉSTAMO</span>
                <span>{{ stats.enPrestamo }}</span>
            </div>
        </div>
    </div>

    <div class="d-print-none">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: #8B1538;">Inventario de Libros</h3>
                <p class="text-muted small mb-0">Control de existencias basado en inventario general.</p>
            </div>
            <div class="d-flex gap-2">
                <button @click="exportarExcel" class="btn btn-success px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Excel
                </button>
                <button @click="imprimir" class="btn text-white px-3 fw-bold shadow-sm" style="background-color: #8B1538;">
                    <i class="bi bi-printer-fill me-2"></i>Imprimir
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3">
                
                <div class="mb-3 d-flex gap-2 pb-3 border-bottom overflow-auto">
                    <button @click="setReporte('GENERAL')" 
                            class="btn btn-sm rounded-pill px-3" 
                            :class="tipoReporte === 'GENERAL' ? 'btn-dark' : 'btn-outline-dark border-0'">
                        Todo
                    </button>
                    <button @click="setReporte('DISPONIBLE')" 
                            class="btn btn-sm rounded-pill px-3" 
                            :class="tipoReporte === 'DISPONIBLE' ? 'btn-success' : 'btn-outline-success border-0'"
                            title="Stock sano (>5) y disponible">
                        ✔ Disponibles
                    </button>
                    <button @click="setReporte('BAJO')" 
                            class="btn btn-sm rounded-pill px-3" 
                            :class="tipoReporte === 'BAJO' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark border-0'"
                            title="Inventario Total <= 5">
                        🔺 Stock Bajo (General)
                    </button>
                    <button @click="setReporte('AGOTADO')" 
                            class="btn btn-sm rounded-pill px-3" 
                            :class="tipoReporte === 'AGOTADO' ? 'btn-danger' : 'btn-outline-danger border-0'"
                            title="Stock sano (>5) pero todo prestado">
                        ❌ Agotados
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group bg-white rounded border-0">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" v-model="filtros.busqueda" class="form-control border-0 shadow-none" placeholder="Buscar título o autor...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filtros.categoria" class="form-select border-0 shadow-none bg-white cursor-pointer">
                            <option value="">Todas las Categorías</option>
                            <option v-for="c in listaCategorias" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="agruparSwitch" v-model="agruparPorEstado">
                            <label class="form-check-label small fw-bold cursor-pointer" for="agruparSwitch">Agrupar visualmente</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            
            <div v-if="agruparPorEstado">
                <div v-for="(grupo, nombre) in librosAgrupados" :key="nombre" class="mb-0">
                    <div class="px-4 py-2 fw-bold text-uppercase small group-header-print" 
                         :class="claseGrupo(nombre)">
                        {{ nombre }} ({{ grupo.length }})
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-print-compact">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Cód.</th>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>Stock General</th> <th class="text-center">Disp.</th>
                                    <th class="text-center">Prest.</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="libro in grupo" :key="libro.id">
                                    <td class="ps-4 font-monospace small">LIB-{{ libro.id }}</td>
                                    <td class="fw-bold text-dark text-truncate" style="max-width: 250px;">{{ libro.titulo }}</td>
                                    <td class="small">{{ libro.autor }}</td>
                                    
                                    <td class="fw-bold text-muted">{{ libro.stock_total }}</td>
                                    
                                    <td class="text-center fw-bold text-dark">{{ libro.stock_disponible }}</td>
                                    
                                    <td class="text-center text-muted small">{{ libro.stock_total - libro.stock_disponible }}</td>
                                    
                                    <td class="small fw-bold">{{ textoEstado(libro).toUpperCase() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-print-compact">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3">Cód.</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th class="text-center">Total Gral.</th>
                            <th class="text-center">Disp.</th>
                            <th class="text-center">Prest.</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="libro in librosFiltrados" :key="libro.id">
                            <td class="ps-4 font-monospace small">LIB-{{ libro.id }}</td>
                            <td class="fw-bold text-dark">{{ libro.titulo }}</td>
                            <td class="small">{{ libro.autor }}</td>
                            
                            <td class="text-center text-muted">{{ libro.stock_total }}</td>
                            
                            <td class="text-center">
                                <strong class="text-dark">{{ libro.stock_disponible }}</strong>
                            </td>
                            
                            <td class="text-center text-muted small">
                                {{ libro.stock_total - libro.stock_disponible }}
                            </td>
                            
                            <td>
                                <span class="d-print-none badge rounded-pill" :class="claseBadge(libro)">{{ textoEstado(libro) }}</span>
                                <span class="d-none d-print-inline fw-bold small text-dark" style="font-size: 0.7rem;">
                                    {{ textoEstado(libro).toUpperCase() }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="librosFiltrados.length === 0">
                            <td colspan="7" class="text-center py-5 text-muted">
                                No se encontraron libros con los filtros actuales.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="d-none d-print-block mt-4 avoid-break">
        <div class="row text-center mt-4 pt-2" style="font-size: 0.75rem;">
            <div class="col-4"><div class="border-top border-dark mx-3 pt-1"><strong>Responsable</strong></div></div>
            <div class="col-4"><div class="border-top border-dark mx-3 pt-1"><strong>Dirección</strong></div></div>
            <div class="col-4"><div class="border-top border-dark mx-3 pt-1"><strong>Fecha</strong></div></div>
        </div>
    </div>

</div>

<style>
    @media print {
        @page { margin: 1cm; size: A4; }
        body { background: white; font-family: 'Arial', sans-serif; color: black; font-size: 10pt; }
        .d-print-none, #sidebar, .topbar { display: none !important; }
        #content { margin: 0; padding: 0; width: 100%; }
        
        .table-print-compact th { border-bottom: 2px solid #000 !important; color: #000 !important; font-weight: bold; background: transparent !important; padding: 2px 4px !important; }
        .table-print-compact td { border-bottom: 1px solid #ccc !important; padding: 2px 4px !important; font-size: 9pt; }
        .avoid-break { page-break-inside: avoid; }
        .group-header-print { color: black !important; background: transparent !important; border-bottom: 1px solid black; font-weight: bold; padding-left: 0 !important; margin-top: 10px; }
    }
</style>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                libros: [],
                filtros: { busqueda: '', categoria: '' },
                tipoReporte: 'GENERAL',
                agruparPorEstado: false
            }
        },
        computed: {
            listaCategorias() { return [...new Set(this.libros.map(l => l.categoria).filter(c => c))].sort(); },
            
            tituloReporte() {
                if (this.tipoReporte === 'DISPONIBLE') return 'Libros Disponibles';
                if (this.tipoReporte === 'BAJO') return 'Inventario Bajo (Alerta)';
                if (this.tipoReporte === 'AGOTADO') return 'Libros Agotados (Prestados)';
                return 'Inventario General';
            },
            
            librosFiltrados() {
                return this.libros.filter(l => {
                    const txt = this.filtros.busqueda.toLowerCase();
                    const matchTxt = l.titulo.toLowerCase().includes(txt) || l.autor.toLowerCase().includes(txt);
                    const matchCat = !this.filtros.categoria || l.categoria === this.filtros.categoria;
                    
                    // --- NUEVA LÓGICA DE FILTRADO ---
                    // "Stock Bajo" se refiere al Inventario General (stock_total), NO a la disponibilidad.
                    let matchEstado = true;
                    if (this.tipoReporte === 'BAJO') {
                        // Solo libros con inventario total <= 5
                        matchEstado = parseInt(l.stock_total) <= 5;
                    }
                    else if (this.tipoReporte === 'AGOTADO') {
                        // Libros con buen inventario (>5) pero 0 disponibles
                        matchEstado = parseInt(l.stock_disponible) === 0 && parseInt(l.stock_total) > 5;
                    }
                    else if (this.tipoReporte === 'DISPONIBLE') {
                        // Libros con buen inventario (>5) y disponibles > 0
                        matchEstado = parseInt(l.stock_disponible) > 0 && parseInt(l.stock_total) > 5;
                    }
                    
                    return matchTxt && matchCat && matchEstado;
                }).sort((a,b) => a.titulo.localeCompare(b.titulo));
            },
            
            librosAgrupados() {
                if (!this.agruparPorEstado) return {};
                // Grupos Excluyentes para evitar duplicados visuales
                const grupos = { 'Disponibles': [], 'Stock Bajo (General)': [], 'Agotados (Prestados)': [] };
                
                this.librosFiltrados.forEach(l => {
                    // La misma lógica de prioridad que el badge
                    if (parseInt(l.stock_total) <= 5) grupos['Stock Bajo (General)'].push(l);
                    else if (parseInt(l.stock_disponible) === 0) grupos['Agotados (Prestados)'].push(l);
                    else grupos['Disponibles'].push(l);
                });
                return grupos;
            },

            stats() {
                // Estadísticas Globales (sobre todo el inventario cargado)
                const data = this.libros; 
                return {
                    total: data.reduce((sum, l) => sum + parseInt(l.stock_total), 0),
                    // Conteo estricto según categorías nuevas
                    stockBajo: data.filter(l => parseInt(l.stock_total) <= 5).length,
                    agotados: data.filter(l => parseInt(l.stock_disponible) === 0 && parseInt(l.stock_total) > 5).length,
                    disponibles: data.filter(l => parseInt(l.stock_disponible) > 0 && parseInt(l.stock_total) > 5).length,
                    enPrestamo: data.reduce((sum, l) => sum + (parseInt(l.stock_total) - parseInt(l.stock_disponible)), 0)
                }
            },
            
            resumenFiltros() {
                let f = [this.tituloReporte];
                if (this.filtros.categoria) f.push("Cat: " + this.filtros.categoria);
                if (this.filtros.busqueda) f.push("Busq: " + this.filtros.busqueda);
                return f.join(" | ");
            }
        },
        mounted() {
            this.cargarDatos();
        },
        methods: {
            async cargarDatos() {
                const res = await fetch('../api/reportes.php?tipo=inventario');
                this.libros = await res.json();
            },
            setReporte(tipo) {
                this.tipoReporte = tipo;
                this.agruparPorEstado = (tipo === 'GENERAL');
            },
            imprimir() { window.print(); },
            
            exportarExcel() {
                let csvContent = "\uFEFF"; 
                csvContent += "ID,Titulo,Autor,Categoria,Stock Total,Stock Disponible,Prestados,Estado\n";
                
                this.librosFiltrados.forEach(l => {
                    const enPrestamo = l.stock_total - l.stock_disponible;
                    const estado = this.textoEstado(l).toUpperCase();
                    const titulo = l.titulo.replace(/"/g, '""');
                    const autor = l.autor.replace(/"/g, '""');
                    
                    // Asegurar que el estado en Excel coincida con la lógica
                    const fila = `"${l.id}","${titulo}","${autor}","${l.categoria}",${l.stock_total},${l.stock_disponible},${enPrestamo},"${estado}"`;
                    csvContent += fila + "\n";
                });
                
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = url;
                const fecha = new Date().toISOString().slice(0,10);
                link.download = `inventario_${this.tipoReporte.toLowerCase()}_${fecha}.csv`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            
            // --- LÓGICA CENTRAL DE ESTADOS ---
            textoEstado(l) {
                // Prioridad 1: Stock Total Bajo (Alerta de Inventario)
                if (parseInt(l.stock_total) <= 5) return 'Stock Bajo';
                // Prioridad 2: Agotado (Solo si el stock total es saludable)
                if (parseInt(l.stock_disponible) === 0) return 'Agotado';
                // Prioridad 3: Disponible
                return 'Disponible';
            },
            claseBadge(l) {
                if (parseInt(l.stock_total) <= 5) return 'bg-warning bg-opacity-10 text-dark border border-warning';
                if (parseInt(l.stock_disponible) === 0) return 'bg-danger bg-opacity-10 text-danger border border-danger';
                return 'bg-success bg-opacity-10 text-success border border-success';
            },
            claseGrupo(nombre) {
                if (nombre.includes('Disponibles')) return 'bg-success text-white d-print-none';
                if (nombre.includes('Bajo')) return 'bg-warning text-dark d-print-none';
                return 'bg-danger text-white d-print-none';
            }
        }
    }).mount('#app')
</script>
</body>
</html>