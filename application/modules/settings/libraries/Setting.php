<?php

class Setting
{
    protected static $_items = [];

    public function __construct()
    {
        ci()->load->model('settings/setting_model');
        $settings = ci()->setting_model->as_array()
            ->set_cache('all_settings')
            ->get_all();
        
        self::$_items = $settings ? $settings : [];
    }

    public static function get($name, $module = null)
    {
        $filter = function($value) use($name) {
            if ($value['slug'] == $name) {
                return true;
            }
            return false;
        };

        if (!is_null($module))
        {
            $filter = function($value) use($name, $module) {
                if ($value['slug'] == $name && $value['module'] == $module) {
                    return true;
                }
                return false;
            };
        }

        $setting = array_filter(self::$_items, $filter);
        if (count($setting) <= 0) {
            return null;
        }

        $setting = array_values($setting);
        $setting = $setting[0];

        if ($setting['type'] == 'checkbox') {
            $value = !$setting
                ? config_item($name)
                : $setting['value']
                    ? explode(',', $setting['value'])
                    : explode(',', $setting['default']) ;
        }
        else {
            $value = !$setting
                ? config_item($name)
                : $setting['value']
                    ? $setting['value']
                    : $setting['default'] ;
        }

        return $value;
    }
}