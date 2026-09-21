<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restringe una ruta a ciertos roles.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
       
        if (! session()->get('isLoggedIn')) 
        {
            return redirect()->to(route_to('loginForm'))
                ->with('error', 'Debe iniciar sesión para tener acceso.');
        }

        $rolUsuario    = (int) session()->get('rol_id');
        $rolesPermitidos = array_map('intval', (array) $arguments);

        if (! in_array($rolUsuario, $rolesPermitidos, true)) {
            // login que no tiene permisos para acceder a la ruta solicitada
            helper('sesion');

            return redirect()->to(ruta_dashboard($rolUsuario))
                ->with('error', 'No tiene permisos para acceder a esa sección.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
