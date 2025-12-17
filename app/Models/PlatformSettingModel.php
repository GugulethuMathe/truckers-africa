<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformSettingModel extends Model
{
    protected $table = 'platform_settings';
    protected $primaryKey = 'setting_key';
    protected $returnType = 'array';

    protected $allowedFields = ['setting_key', 'setting_value'];

    /**
     * Get a specific setting value by its key.
     * @param string $key
     * @return mixed|null
     */
    public function getSetting(string $key)
    {
        $setting = $this->find($key);
        return $setting ? $setting['setting_value'] : null;
    }

    /**
     * Get all settings as an associative array.
     * @return array
     */
    public function getAllSettings(): array
    {
        $settings = $this->findAll();
        return array_column($settings, 'setting_value', 'setting_key');
    }

    /**
     * Set a specific setting value.
     * This will update an existing setting or create a new one.
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setSetting(string $key, string $value): bool
    {
        return $this->save([
            'setting_key'   => $key,
            'setting_value' => $value
        ]);
    }
}
