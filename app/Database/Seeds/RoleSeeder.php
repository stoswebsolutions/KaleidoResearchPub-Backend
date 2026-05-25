<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'uuid'        => generate_uuid(),
                'name'        => 'Super Admin',
                'slug'        => generate_slug('Super Admin'),
                'description' => 'Full system access with all privileges.',
                'status'      => 'active',
                'is_system'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'        => generate_uuid(),
                'name'        => 'Admin',
                'slug'        => generate_slug('Super Admin'),
                'description' => 'Administrative access for managing platform operations.',
                'status'      => 'active',
                'is_system'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'        => generate_uuid(),
                'name'        => 'Editor',
                'slug'        => generate_slug('Super Admin'),
                'description' => 'Editorial access for reviewing and managing publications.',
                'status'      => 'active',
                'is_system'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'        => generate_uuid(),
                'name'        => 'Author',
                'slug'        => generate_slug('Super Admin'),
                'description' => 'Author access for article submission and profile management.',
                'status'      => 'active',
                'is_system'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($roles as $role) {
            $exists = $this->db->table('roles')
                ->where('slug', $role['slug'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('roles')->insert($role);
            }
        }
    }
}