<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    protected $allowedFields = ['key', 'value'];
    public $timestamps = false;

    public function updateOrInsert($key, $value)
    {
        $exists = $this->where('key', $key)->first();
        if ($exists) {
            $this->where('key', $key)->set(['value' => $value])->update();
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }
    }
}
