<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'email',
        'password'
    ];

    protected $beforeInsert = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);

        return $data;
    }

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Verify admin password.
     * @param string $email
     * @param string $password
     * @return array|false The admin data if password is correct, otherwise false.
     */
    public function verifyPassword(string $email, string $password)
    {
        $admin = $this->where('email', $email)->first();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            return $admin;
        }

        return false;
    }
}

