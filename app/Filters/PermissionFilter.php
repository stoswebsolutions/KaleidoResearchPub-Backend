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
        if (
            ! isset($request->user)
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

        $user = $request->user;

        $db = db_connect();

        /**
         * Direct profile permission.
         */
        $profilePermission = $db
            ->table('profile_permissions pp')
            ->select('pp.id')
            ->join(
                'permissions p',
                'p.id = pp.permission_id'
            )
            ->where(
                'pp.profile_id',
                $user->profile_id
            )
            ->where(
                'p.slug',
                $requiredPermission
            )
            ->where(
                'pp.status',
                'active'
            )
            ->get()
            ->getRow();

        if ($profilePermission !== null) {
            return;
        }

        /**
         * Role permission.
         */
        $rolePermission = $db
            ->table('role_permissions rp')
            ->select('rp.id')
            ->join(
                'permissions p',
                'p.id = rp.permission_id'
            )
            ->where(
                'rp.role_id',
                $user->role_id
            )
            ->where(
                'p.slug',
                $requiredPermission
            )
            ->where(
                'rp.status',
                'active'
            )
            ->get()
            ->getRow();

        if ($rolePermission !== null) {
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