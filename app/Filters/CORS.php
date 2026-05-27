<?php

namespace App\Filters;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CORS implements FilterInterface
{
    public function before($request, $arguments = null)
    {
        $response = service('response');

        // Allowed origins — add your Angular domain here
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        $response->setHeader('Access-Control-Allow-Origin', $origin);
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Max-Age', '86400');

        // Handle OPTIONS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return $response->setStatusCode(200);
        }
    }

    public function after($request, $response, $arguments = null)
    {
        return $response;
    }
}
