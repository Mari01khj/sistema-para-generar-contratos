<?php

/**
 * Helpers de sesión / roles.
 * Se cargan con helper('sesion').
 */

if (! function_exists('ruta_dashboard')) {
    /**
     * Devuelve la ruta (relativa) del dashboard que le corresponde a un rol.
     * 1 = Administrador, cualquier otro = Operador.
     * Es el ÚNICO lugar donde se decide esto, para no repetir if/else por todos lados.
     */
    function ruta_dashboard(?int $rolId = null): string
    {
        $rolId ??= (int) session()->get('rol_id');

        return $rolId === 1 ? 'admin/dashboard' : 'operador/dashboardOperador';
    }
}
