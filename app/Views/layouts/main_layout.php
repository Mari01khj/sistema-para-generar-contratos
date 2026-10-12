<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $titulo ?? 'Sistema de Contratos' ?></title>



    <!-- Iconos -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">



    <!-- CSS de SB Admin 2 -->

    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">

    <style>
        #contenedorToasts {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1080;
            min-width: 320px;
        }
        #contenedorToasts .toast {
            min-width: 320px;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.2);
        }
    </style>

</head>



<body id="page-top">



    <div id="wrapper">



        <!-- Sidebar -->

        <?= view('layouts/sidebar') ?>

        <!-- End of Sidebar -->



        <!-- Content Wrapper -->

        <div id="content-wrapper" class="d-flex flex-column">



            <!-- Main Content -->

            <div id="content">



                <!-- Topbar -->

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">

                        <i class="fa fa-bars"></i>

                    </button>
                    
                    <span class="d-none d-sm-inline-block text-gray-600 small">

                    &bull; Dirección de Recursos Materiales

                    </span>



                    <!-- Topbar Navbar -->

                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>



                        <!-- User Info -->

                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">

                                    <?= session()->get('nombre') ?? 'Usuario' ?>

                                </span>

                                <i class="fas fa-user-circle fa-2x text-gray-400"></i>

                            </a>

                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="<?= base_url('perfil') ?>">

                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Perfil

                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="<?= base_url('logout') ?>">

                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Cerrar Sesión

                                </a>

                            </div>

                        </li>

                    </ul>

                </nav>

                <div class="container-fluid">

                    <?= $this->renderSection('content') ?>

                </div>
            </div>

            <footer class="sticky-footer bg-white">

                <div class="container my-auto">

                    <div class="copyright text-center my-auto">

                        <span>Gestor de Contratos &copy; Recursos Materiales</span>

                    </div>

                </div>

            </footer>
        </div>


    </div>

    <a class="scroll-to-top rounded" href="#page-top">

        <i class="fas fa-angle-up"></i>

    </a>

    <!-- Notificaciones (toasts): un solo contenedor para toda la app. -->
    <div id="contenedorToasts" aria-live="polite" aria-atomic="true"></div>

    <!-- Scripts: jQuery y Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="<?= base_url('vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <!-- JS de SB Admin 2 -->
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>

    <script>
     
        function mostrarToast(mensaje, tipo = 'success') {
            const iconos = {
                success: 'fa-check-circle',
                danger:  'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
            };
            const icono = iconos[tipo] || iconos.success;

            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${tipo} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icono} mr-2"></i>${mensaje}
                    </div>
                    <button type="button" class="close mr-2 m-auto text-white" data-dismiss="toast" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            document.getElementById('contenedorToasts').appendChild(toastEl);
            $(toastEl).toast({ delay: 5000 }).toast('show');
            $(toastEl).on('hidden.bs.toast', () => toastEl.remove());
        }

        
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($mensajeFlash = session()->getFlashdata('mensaje')): ?>
                mostrarToast(<?= json_encode($mensajeFlash) ?>, 'success');
            <?php endif; ?>
            <?php if ($errorFlash = session()->getFlashdata('error')): ?>
                mostrarToast(<?= json_encode($errorFlash) ?>, 'danger');
            <?php endif; ?>
        });
    </script>

    <?= $this->renderSection('scripts') ?>

</body>

</html>
