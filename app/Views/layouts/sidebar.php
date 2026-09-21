<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('/') ?>">
        <div class="sidebar-brand-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="sidebar-brand-text mx-3">CONTRATOS</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= base_url('/') ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">Gestión</div>

    <!-- SECCIÓN EXCLUSIVA DE ADMINISTRADOR -->
    <?php if ((int) session()->get('rol_id') === 1): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCatalogos" aria-expanded="true" aria-controls="collapseCatalogos">
                <i class="fas fa-fw fa-folder"></i>
                <span>Catálogos</span>
            </a>
            <div id="collapseCatalogos" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('proveedores') ?>">Proveedores</a>
                    <a class="collapse-item" href="<?= base_url('areas') ?>">Áreas</a>
                    <a class="collapse-item" href="<?= base_url('tipos-contrato') ?>">Tipos de Contrato</a>
                    <a class="collapse-item" href="<?= base_url('usuarios') ?>">Usuarios</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <!-- SECCIÓN CONTRATOS (Admin y Operador) -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseContratos" aria-expanded="true" aria-controls="collapseContratos">
            <i class="fas fa-fw fa-file-signature"></i>
            <span>Contratos</span>
        </a>
        <div id="collapseContratos" class="collapse" aria-labelledby="headingContratos" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?= base_url('contratos') ?>">Ver Contratos</a>
                <a class="collapse-item" href="<?= base_url('contratos/nuevo') ?>">Generar Contrato</a>
            </div>
        </div>
    </li>

    <!-- OPCIÓN PARA OPERADOR -->
    <?php if ((int) session()->get('rol_id') === 2): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('perfil') ?>">
                <i class="fas fa-fw fa-user"></i>
                <span>Mis Datos</span>
            </a>
        </li>
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>