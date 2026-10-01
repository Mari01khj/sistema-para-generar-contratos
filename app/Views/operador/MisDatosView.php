<?= $this->extend('layouts/main_layout') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Perfil de Usuario</h6>
            </div>
            <div class="card-body">
                
                <?php if(session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>

                <form action="<?= base_url('operador/actualizar-datos') ?>" method="POST">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" value="<?= esc($usuario['nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" value="<?= esc($usuario['correo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva Contraseña (Dejar en blanco para mantener la actual)</label>
                        <input type="password" class="form-control" name="password" placeholder="***">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>