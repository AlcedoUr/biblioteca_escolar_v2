<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
// Recibimos el ID por URL
$id_prestamo = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Préstamo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div id="app" class="container py-4">
    <a href="historial.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Gestionar Devolución (Préstamo #<?php echo $id_prestamo; ?>)</h4>
        </div>
        <div class="card-body">
            <p class="alert alert-info">
                <i class="bi bi-info-circle"></i> Marque el estado de cada libro conforme se vayan devolviendo.
            </p>

            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Cantidad</th>
                        <th>Estado Actual</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in detalles" :key="d.id_detalle">
                        <td>{{ d.titulo }}</td>
                        <td class="fw-bold">{{ d.cantidad }}</td>
                        <td>
                            <span v-if="d.estado_devolucion == 'PENDIENTE'" class="badge bg-warning text-dark">Pendiente</span>
                            <span v-else-if="d.estado_devolucion == 'BUENO'" class="badge bg-success">Devuelto OK</span>
                            <span v-else class="badge bg-danger">{{ d.estado_devolucion }}</span>
                        </td>
                        <td>
                            <div v-if="d.estado_devolucion == 'PENDIENTE'" class="btn-group">
                                <button @click="procesar(d, 'BUENO')" class="btn btn-sm btn-success" title="Devolver Bien">
                                    <i class="bi bi-check-lg"></i> OK
                                </button>
                                <button @click="procesar(d, 'DAÑADO')" class="btn btn-sm btn-warning" title="Reportar Dañado">
                                    <i class="bi bi-exclamation-triangle"></i> Dañado
                                </button>
                                <button @click="procesar(d, 'PERDIDO')" class="btn btn-sm btn-danger" title="Reportar Perdido">
                                    <i class="bi bi-x-circle"></i> Perdido
                                </button>
                            </div>
                            <div v-else class="text-muted small">
                                <i class="bi bi-lock"></i> Procesado
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const { createApp } = Vue

    createApp({
        data() {
            return {
                idPrestamo: <?php echo $id_prestamo; ?>,
                detalles: []
            }
        },
        mounted() {
            this.cargarDetalles();
        },
        methods: {
            async cargarDetalles() {
                const res = await fetch(`../api/obtener_detalle.php?id=${this.idPrestamo}`);
                this.detalles = await res.json();
            },
            async procesar(item, estado) {
                if (!confirm(`¿Marcar libro como ${estado}?`)) return;

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
                        this.cargarDetalles(); // Recargar tabla
                    } else {
                        alert("Error: " + r.mensaje);
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>