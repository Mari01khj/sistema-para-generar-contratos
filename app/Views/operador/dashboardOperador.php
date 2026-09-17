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
                <p>Consulta los contratos registrados o genera un nuevo contrato a partir de los machotes oficiales vigentes.</p>
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

    <!-- Datos de su cuenta -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos de mi Cuenta</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nombre del Operador</label>
                        <input type="text" class="form-control" value="Operador Recursos Materiales">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Correo Institucional</label>
                        <input type="email" class="form-control" value="operador@ayuntamiento.gob.mx">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Cambiar Contraseña</label>
                        <input type="password" class="form-control" placeholder="Nueva contraseña (opcional)">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm">Actualizar datos</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>