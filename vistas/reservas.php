<?php include 'includes/header.php'; ?>
<div id="app">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold" style="color: #8B1538;">Reservas de Material</h3>
            <p class="text-muted">Solicitudes de docentes para fechas futuras</p>
        </div>
        <button class="btn text-white shadow-sm fw-bold" style="background-color: #8B1538;" @click="nuevaReserva">
            <i class="bi bi-calendar-plus me-2"></i>Nueva Reserva
        </button>
    </div>

    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center p-4">
        <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
        <div>
            <h5 class="fw-bold mb-1">Módulo en Construcción</h5>
            <p class="mb-0">La funcionalidad de reservas para docentes estará disponible en la próxima actualización del sistema. Se requiere implementar la tabla <code>reservas</code> y el endpoint correspondiente.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const { createApp } = Vue
    createApp({
        data() { return { reservas: [] } },
        methods: {
            nuevaReserva() { 
                Swal.fire('Próximamente', 'Esta función estará habilitada pronto.', 'info');
            }
        }
    }).mount('#app')
</script>
</body>
</html>