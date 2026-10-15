<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Acceso al Sistema </title>

    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">



<div class="container">

    <div class="row justify-content-center">

        <div class="col-xl-6 col-lg-7 col-md-9 mt-5">

            <div class="card o-hidden border-0 shadow-lg my-5">

                <div class="card-body p-0">

                    <div class="p-5">

                        <div class="text-center mb-4">

                            <i class="fas fa-file-contract fa-3x text-primary mb-3"></i>

                            <h1 class="h4 text-gray-900 font-weight-bold">Gestor de Contratos</h1>

                            <p class="text-muted small">Dirección de Recursos Materiales</p>

                        </div>



                        <?php if (session()->getFlashdata('error')): ?>

                            <div class="alert alert-danger py-2 small">

                                <i class="fas fa-exclamation-circle mr-1"></i>

                                <?= session()->getFlashdata('error') ?>

                            </div>

                        <?php endif; ?>



                        <form class="user" action="<?= site_url('login-process') ?>" method="POST">

                            <?= csrf_field() ?>

                            <div class="form-group">

                                <input type="email" name="correo" class="form-control form-control-user" 

                                       placeholder="Correo electrónico" 

                                       value="<?= old('correo') ?>" required>

                            </div>

                        <div class="form-group">
                                <div class="position-relative">

                                    <input type="password" name="password" id="inputPassword"

                                           class="form-control form-control-user" style="padding-right: 40px;"

                                           placeholder="Contraseña" required>

                                    <button type="button" id="btnTogglePassword"

                                        class="btn btn-sm position-absolute"

                                        style="top: 0; right: 0; height: 100%; border: none; background: transparent; color: #858796;"

                                        tabindex="-1" aria-label="Mostrar u ocultar contrasena">

                                        <i class="fas fa-eye" id="iconoTogglePassword"></i>

                                    </button>
                            </div>
                        </div>

                            <button type="submit" class="btn btn-primary btn-user btn-block">

                                Iniciar Sesión

                            </button>
                        <div class="text-center mt-3">

                            <p class="small text-muted mb-0"> Si olvidaste tu contraseña pidesela al Adinistrador</p>

                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () 
    {

        var input = document.getElementById("inputPassword");

        var boton = document.getElementById("btnTogglePassword");

        var icono = document.getElementById("iconoTogglePassword");



        boton.addEventListener("click", function () 
        {

            var oculta = input.type === "password";

            input.type = oculta ? "text" : "password";

            icono.classList.toggle("fa-eye", !oculta);

            icono.classList.toggle("fa-eye-slash", oculta);

        });

    })();
</script>

</body>

</html>
