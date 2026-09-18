<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // sesion iniciada
        if (! $session->get('isLoggedIn')) 
        {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión para acceder.');
        }

        // ver roles
        if (! empty($arguments)) 
        {
            $rolUsuario = (string) $session->get('rol_id');

            if (! in_array($rolUsuario, $arguments, true)) 
                {
                
                if ($rolUsuario === '2') 
                {
                    return redirect()->to(base_url('operador/dashboardOperador'))->with('error', 'No tienes permisos para acceder a esta sección.');
                }

                return redirect()->to(base_url('login'))->with('error', 'Acceso no autorizado.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción posterior
    }
}