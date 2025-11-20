<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - I.E. Virgen de las Mercedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* 1. EL DEGRADADO DE FONDO (Robado del código React) */
        body {
            /* bg-gradient-to-br from-[#8B1538] via-[#a01a45] to-[#6b0f2a] */
            background: linear-gradient(135deg, #8B1538 0%, #a01a45 50%, #6b0f2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* 2. LA TARJETA FLOTANTE */
        .card-login {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); /* Sombra fuerte */
            max-width: 400px;
            width: 100%;
            overflow: hidden;
        }

        /* 3. EL LOGO CIRCULAR */
        .logo-circle {
            width: 80px;
            height: 80px;
            background-color: #8B1538; /* Color Institucional */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto; /* Centrado */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .logo-icon {
            color: #D4AF37; /* Color Dorado del Icono */
            font-size: 2.5rem;
        }

        /* 4. TEXTOS INSTITUCIONALES */
        .text-school { color: #8B1538; font-weight: bold; }
        .text-gold { color: #D4AF37; }

        /* 5. INPUTS CON ICONO ADENTRO (Como en Figma) */
        .input-wrapper { position: relative; margin-bottom: 1rem; }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d; /* text-muted */
        }

        .input-custom {
            padding-left: 45px; /* Espacio para el icono */
            height: 50px;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        .input-custom:focus {
            border-color: #8B1538;
            box-shadow: 0 0 0 4px rgba(139, 21, 56, 0.1);
        }

        /* 6. BOTÓN PERSONALIZADO */
        .btn-school {
            background-color: #8B1538;
            color: white;
            height: 50px;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        .btn-school:hover {
            background-color: #6b0f2a; /* Un poco más oscuro al pasar el mouse */
            color: white;
        }
    </style>
</head>
<body>

    <div class="container p-4">
        <div class="card card-login mx-auto bg-white">
            <div class="card-body p-5">
                
                <div class="text-center mb-4">
                    <div class="logo-circle mb-3">
                        <i class="bi bi-book-half logo-icon"></i>
                    </div>
                    <h4 class="card-title text-school mb-1">Biblioteca Virgen de las Mercedes</h4>
                    <p class="text-muted small">I.E. N.° 3054 - Sistema de Gestión</p>
                </div>

                <form id="formLogin">
                    
                    <div class="input-wrapper">
                        <label class="form-label fw-bold small">Usuario</label>
                        <div class="position-relative">
                            <i class="bi bi-person"></i> <input type="text" id="username" class="form-control input-custom" placeholder="Ingrese su usuario" required autofocus>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label class="form-label fw-bold small">Contraseña</label>
                        <div class="position-relative">
                            <i class="bi bi-lock"></i> <input type="password" id="password" class="form-control input-custom" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-school w-100 mt-3">
                        <span id="btnText">Iniciar Sesión</span>
                        <span id="btnLoading" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>

                    <div id="alerta" class="mt-3 alert alert-danger d-none text-center small p-2"></div>

                </form>

                <div class="mt-4 pt-4 border-top text-center">
                    <p class="small text-muted mb-2">Credenciales de demostración:</p>
                    <div class="bg-light p-2 rounded small text-muted text-start">
                        <div><strong>Admin:</strong> admin / 123456</div>
                        <div><strong>Docente:</strong> rbolaños / 12345678</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('formLogin').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;
            const alerta = document.getElementById('alerta');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            const btn = document.querySelector('button[type="submit"]');

            // Estado de carga (UX)
            alerta.classList.add('d-none');
            btn.disabled = true;
            btnText.textContent = 'Validando...';
            btnLoading.classList.remove('d-none');

            try {
                const respuesta = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: user, password: pass })
                });

                const datos = await respuesta.json();

                if (datos.exito) {
                    // Éxito: Esperamos 1 seg para que el usuario vea el efecto
                    btn.classList.remove('btn-school');
                    btn.classList.add('btn-success');
                    btnText.textContent = '¡Bienvenido!';
                    setTimeout(() => {
                        window.location.href = 'vistas/dashboard.php';
                    }, 800);
                } else {
                    // Error
                    alerta.textContent = datos.mensaje;
                    alerta.classList.remove('d-none');
                    btn.disabled = false;
                    btnText.textContent = 'Iniciar Sesión';
                    btnLoading.classList.add('d-none');
                }
            } catch (error) {
                console.error(error);
                alerta.textContent = "Error de conexión con el servidor";
                alerta.classList.remove('d-none');
                btn.disabled = false;
                btnText.textContent = 'Iniciar Sesión';
                btnLoading.classList.add('d-none');
            }
        });
    </script>
</body>
</html>