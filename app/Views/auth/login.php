<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Contratos</title>
    <link href="<?= base_url('css/bootstrap.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Iniciar Sesión</h4>
                            <p class="text-muted small">Recursos Materiales</p>
                        </div>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Correo Institucional</label>
                                <input type="email" class="form-control" placeholder="usuario@ayuntamiento.gob.mx" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="button" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>