<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // =====================================================
            // Roles
            // =====================================================
            [
                'module'      => 'roles',
                'name'        => 'Role View',
                'slug'        => generate_slug('role-view'),
                'description' => 'View roles.',
            ],
            [
                'module'      => 'roles',
                'name'        => 'Role Create',
                'slug'        => generate_slug('role-create'),
                'description' => 'Create roles.',
            ],
            [
                'module'      => 'roles',
                'name'        => 'Role Edit',
                'slug'        => generate_slug('role-edit'),
                'description' => 'Edit roles.',
            ],
            [
                'module'      => 'roles',
                'name'        => 'Role Delete',
                'slug'        => generate_slug('role-delete'),
                'description' => 'Delete roles.',
            ],

            // =====================================================
            // Permissions
            // =====================================================
            [
                'module'      => 'permissions',
                'name'        => 'Permission View',
                'slug'        => generate_slug('permission-view'),
                'description' => 'View permissions.',
            ],
            [
                'module'      => 'permissions',
                'name'        => 'Permission Create',
                'slug'        => generate_slug('permission-create'),
                'description' => 'Create permissions.',
            ],
            [
                'module'      => 'permissions',
                'name'        => 'Permission Edit',
                'slug'        => generate_slug('permission-edit'),
                'description' => 'Edit permissions.',
            ],
            [
                'module'      => 'permissions',
                'name'        => 'Permission Delete',
                'slug'        => generate_slug('permission-delete'),
                'description' => 'Delete permissions.',
            ],

            // =====================================================
            // Profiles
            // =====================================================
            [
                'module'      => 'profiles',
                'name'        => 'Profile View',
                'slug'        => generate_slug('profile-view'),
                'description' => 'View profiles.',
            ],
            [
                'module'      => 'profiles',
                'name'        => 'Profile Create',
                'slug'        => generate_slug('profile-create'),
                'description' => 'Create profiles.',
            ],
            [
                'module'      => 'profiles',
                'name'        => 'Profile Edit',
                'slug'        => generate_slug('profile-edit'),
                'description' => 'Edit profiles.',
            ],
            [
                'module'      => 'profiles',
                'name'        => 'Profile Delete',
                'slug'        => generate_slug('profile-delete'),
                'description' => 'Delete profiles.',
            ],
            
        ];

        foreach ($permissions as $permission) {

            $exists = $this->db
                ->table('permissions')
                ->where('slug', $permission['slug'])
                ->countAllResults();

            if ($exists === 0) {

                $this->db->table('permissions')->insert([
                    'uuid'        => generate_uuid(),
                    'module'      => $permission['module'],
                    'name'        => $permission['name'],
                    'slug'        => $permission['slug'],
                    'description' => $permission['description'],
                    'status'      => 'active',
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}