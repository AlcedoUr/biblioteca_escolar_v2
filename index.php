<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Biblioteca Virgen de las Mercedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #8B1538 0%, #a01a45 50%, #6b0f2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card-main {
            border: none; border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 450px; width: 100%;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .role-card { cursor: pointer; transition: all 0.3s; border: 2px solid transparent; text-decoration: none; }
        .role-card:hover { transform: translateY(-3px); border-color: #8B1538; background-color: #fff5f7; }
        .btn-vino { background-color: #8B1538; color: white; font-weight: 600; transition: all 0.2s; }
        .btn-vino:hover { background-color: #6b0f2a; color: white; }
        .btn-vino:disabled { background-color: #e0e0e0; border-color: #e0e0e0; color: #999; cursor: not-allowed; }
        .animate-fade { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* Alerta CapsLock Flotante */
        .caps-warning {
            font-size: 0.75rem; color: #e67e22; font-weight: bold;
            margin-top: 5px; display: flex; align-items: center;
            animation: shake 0.5s;
        }
        @keyframes shake { 0% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } 100% { transform: translateX(0); } }
    </style>
</head>
<body>

<div id="app" class="container p-3">
    <div class="card card-main mx-auto">
        <div class="card-body p-5">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-3" style="width: 80px; height: 80px; border: 4px solid #f8f9fa;">
                    <i class="bi bi-book-half fs-1" style="color: #8B1538;"></i>
                </div>
                <h4 class="fw-bold mb-1" style="color: #8B1538;">Biblioteca Digital</h4>
                <small class="text-muted">I.E. Virgen de las Mercedes</small>
            </div>

            <div v-if="step === 1" class="animate-fade">
                <p class="text-center text-dark mb-4 fw-bold">Seleccione una opción:</p>
                
                <a href="vistas/biblioteca_virtual.php" class="card role-card mb-3 p-3 shadow-sm d-block text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3 text-primary">
                            <i class="bi bi-cloud-download fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Biblioteca Virtual</h6>
                            <small class="text-muted">Acceso libre a recursos</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </div>
                </a>

                <div class="card role-card p-3 shadow-sm" @click="step = 2">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3 text-danger">
                            <i class="bi bi-shield-lock fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Gestión Interna</h6>
                            <small class="text-muted">Solo personal autorizado</small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </div>
                </div>
            </div>

            <div v-if="step === 2" class="animate-fade">
                <button class="btn btn-link text-muted text-decoration-none p-0 mb-3" @click="step = 1">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </button>

                <form @submit.prevent="login">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Usuario</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-white text-muted border-0"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   v-model="usuario" 
                                   @input="limpiarUsuario" 
                                   class="form-control border-0" 
                                   placeholder="Solo letras (ej. jperez)" 
                                   required>
                        </div>
                        <div class="form-text small" v-if="usuario && !/^[a-zA-Z]+$/.test(usuario)">Solo se permiten letras.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Contraseña</label>
                        <div class="input-group border rounded" :class="{'border-danger': password.length > 0 && password.length < 8}">
                            <span class="input-group-text bg-white text-muted border-0"><i class="bi bi-lock"></i></span>
                            <input :type="mostrarPassword ? 'text' : 'password'" 
                                   v-model="password" 
                                   @keyup="verificarCaps"
                                   @keydown="verificarCaps"
                                   @click="verificarCaps"
                                   class="form-control border-0" 
                                   placeholder="Mínimo 8 caracteres" 
                                   required>
                            <button type="button" class="btn bg-white border-0 text-muted" @click="mostrarPassword = !mostrarPassword">
                                <i class="bi" :class="mostrarPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                            </button>
                        </div>
                        
                        <div v-if="capsLockActivado" class="caps-warning animate-fade">
                            <i class="bi bi-capslock-fill me-2"></i> ¡Mayúsculas Activadas!
                        </div>
                        
                        <div v-if="password.length > 0 && password.length < 8" class="text-danger small mt-1">
                            <i class="bi bi-info-circle me-1"></i> Faltan {{ 8 - password.length }} caracteres
                        </div>
                    </div>

                    <button type="submit" class="btn btn-vino w-100 py-2 rounded-3" 
                            :disabled="cargando || password.length < 8 || usuario.length === 0">
                        <span v-if="cargando" class="spinner-border spinner-border-sm me-2"></span>
                        {{ cargando ? 'Validando...' : 'Ingresar al Sistema' }}
                    </button>

                    <div v-if="errorMsg" class="alert alert-danger mt-3 py-2 small text-center border-0 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ errorMsg }}
                    </div>
                </form>
            </div>

        </div>
    </div>
    <div class="text-center mt-3 text-white-50 small">&copy; 2025 Biblioteca Escolar</div>
</div>

<script>
    const { createApp } = Vue
    createApp({
        data() {
            return { 
                step: 1, 
                usuario: '', 
                password: '', 
                mostrarPassword: false, 
                capsLockActivado: false,
                cargando: false, 
                errorMsg: '' 
            }
        },
        methods: {
            // Solo permite letras (elimina números, espacios y símbolos)
            limpiarUsuario() {
                this.usuario = this.usuario.replace(/[^a-zA-Z]/g, '');
                this.errorMsg = '';
            },
            
            // Detecta si Bloq Mayús está activo usando la API nativa del navegador
            verificarCaps(event) {
                if (event.getModifierState) {
                    this.capsLockActivado = event.getModifierState('CapsLock');
                }
            },

            async login() {
                if (this.password.length < 8) return; // Doble seguridad

                this.cargando = true; 
                this.errorMsg = '';
                
                try {
                    const res = await fetch('api/login.php', {
                        method: 'POST', 
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username: this.usuario, password: this.password })
                    });
                    const data = await res.json();
                    
                    if (data.exito) {
                        window.location.href = data.redirect || 'vistas/dashboard.php';
                    } else { 
                        this.errorMsg = data.mensaje; 
                        this.cargando = false; 
                    }
                } catch (e) { 
                    this.errorMsg = "Error de conexión con el servidor"; 
                    this.cargando = false; 
                }
            }
        }
    }).mount('#app')
</script>
</body>
</html>