<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        $authUser = service('authUser');

        if (
            ! isset($authUser->profileId) ||
            ! isset($authUser->roleId)
        ) {
            return service('response')
                ->setStatusCode(
                    ResponseInterface::HTTP_UNAUTHORIZED
                )
                ->setJSON(
                    unauthorized_response(
                        'Authentication required.'
                    )
                );
        }

        $requiredPermission = $arguments[0] ?? null;

        if (empty($requiredPermission)) {
            return service('response')
                ->setStatusCode(
                    ResponseInterface::HTTP_FORBIDDEN
                )
                ->setJSON(
                    forbidden_response(
                        'Permission not specified.'
                    )
                );
        }

        $hasPermission = db_connect()
            ->table('role_permissions rp')
            ->join(
                'permissions p',
                'p.id = rp.permission_id'
            )
            ->where(
                'rp.role_id',
                $authUser->roleId
            )
            ->where(
                'p.slug',
                $requiredPermission
            )
            ->where(
                'p.status', 
                'active' 
            )
            ->countAllResults() > 0;

        if ($hasPermission) {
            return;
        }

        return service('response')
            ->setStatusCode(
                ResponseInterface::HTTP_FORBIDDEN
            )
            ->setJSON(
                forbidden_response(
                    'Access denied.'
                )
            );
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ): void {
        //
    }
}