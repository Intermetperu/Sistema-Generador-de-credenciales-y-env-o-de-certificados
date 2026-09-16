<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $allowedFields = ['name', 'permissions', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';
}