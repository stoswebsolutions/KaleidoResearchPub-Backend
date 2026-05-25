<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = $this->db
            ->table('roles')
            ->where('slug', 'super-admin')
            ->get()
            ->getRowArray();

        if ($role === null) {
            throw new \RuntimeException(
                'Super Admin role not found. Please run RoleSeeder first.'
            );
        }

        $email = env('SUPER_ADMIN_EMAIL');

        $profile = $this->db
            ->table('profiles')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if ($profile === null) {

            $profileData = [
                'uuid'                  => generate_uuid(),
                'role_id'               => (int) $role['id'],
                'full_name'             => env('SUPER_ADMIN_NAME'),
                'email'                 => env('SUPER_ADMIN_EMAIL'),
                'phone'                 => env('SUPER_ADMIN_PHONE'),
                'password_hash'         => password_hash(
                    env('SUPER_ADMIN_PASSWORD'),
                    PASSWORD_DEFAULT
                ),
                'profile_image'         => null,
                'status'                => 'active',
                'email_verified_at'     => date('Y-m-d H:i:s'),
                'phone_verified_at'     => null,
                'last_login_at'         => null,
                'last_login_ip'         => null,
                'failed_login_attempts' => 0,
                'locked_until'          => null,
                'created_at'            => date('Y-m-d H:i:s'),
            ];

            $this->db->table('profiles')->insert($profileData);

            $profileId = (int) $this->db->insertID();
        } else {
            $profileId = (int) $profile['id'];
        }

        $permissions = $this->db
            ->table('permissions')
            ->select('id')
            ->get()
            ->getResultArray();

        foreach ($permissions as $permission) {

            $exists = $this->db
                ->table('role_permissions')
                ->where('role_id', $role['id'])
                ->where('permission_id', $permission['id'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('role_permissions')->insert([
                    'role_id'       => $role['id'],
                    'permission_id' => $permission['id'],
                    'created_by'    => $profileId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}