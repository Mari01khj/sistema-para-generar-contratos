<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Panel de Control - Operador</h1>
</div>

<div class="row">
    <!-- Acceso a Contratos -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary">
                <h6 class="m-0 font-weight-bold text-white">Gestión de Contratos</h6>
            </div>
            <div class="card-body">
                <p>Consulta los contratos registrados o genera uno nuevo.</p>
                <a href="<?= base_url('contratos/nuevo') ?>" class="btn btn-success btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
                    <span class="text">Generar Contrato</span>
                </a>
                <a href="<?= base_url('contratos') ?>" class="btn btn-secondary btn-icon-split ml-2">
                    <span class="icon text-white-50"><i class="fas fa-search"></i></span>
                    <span class="text">Ver Contratos</span>
                </a>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>