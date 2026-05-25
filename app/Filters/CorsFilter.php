<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        if ($request->getMethod() === 'options') {

            return service('response')
                ->setStatusCode(200)
                ->setHeader(
                    'Access-Control-Allow-Origin',
                    env('CORS_ALLOWED_ORIGIN', '*')
                )
                ->setHeader(
                    'Access-Control-Allow-Headers',
                    'Origin, X-Requested-With, Content-Type, Accept, Authorization'
                )
                ->setHeader(
                    'Access-Control-Allow-Methods',
                    'GET, POST, PUT, PATCH, DELETE, OPTIONS'
                );
        }

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ): void {
        $response->setHeader(
            'Access-Control-Allow-Origin',
            env('CORS_ALLOWED_ORIGIN', '*')
        );

        $response->setHeader(
            'Access-Control-Allow-Headers',
            'Origin, X-Requested-With, Content-Type, Accept, Authorization'
        );

        $response->setHeader(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        );
    }
}