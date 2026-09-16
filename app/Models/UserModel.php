<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType       = 'array';
    protected $allowedFields = [
        'name', 'email', 'password', 'verification_token', 'avatar', 'token_expire', 'email_verified_at', 'is_active', 'role_id'
    ];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
