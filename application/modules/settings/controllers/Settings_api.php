<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Settings_api
 *
 * @property Setting_model $setting_model
 */
class Settings_api extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('settings/setting_model');
        $this->lang->load('settings/settings');
    }

    public function index()
    {
        $settings = $this->setting_model->as_array()->get_all(['is_gui' => 1]);
        if (!$settings) {
            $this->output->set_status_header(400, lang('msg::request_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $sections = [];
        $dataSection = [];
        foreach ($settings as $key => $setting)
        {
            if (empty($setting['module'])) {
                $setting['module'] = 'general';
            }

            if (!isset($sections[$setting['module']]))
            {
                if ($this->module_model->exists($setting['module']))
                {
                    list($path, $_langFile) = Modules::find('settings_lang', $setting['module'], 'language/' . config_item('language') . '/');
                    if ($path !== false)
                    {
                        $this->lang->load($setting['module'] . '/settings');
                        $sectionName = lang('settings::section_' . $setting['module']);
                    }
                }
                $sectionName = lang('settings::section_' . $setting['module']);

                if (strpos($sectionName, 'FIXME(') !== false) {
                    $sectionName = ucfirst(strtr($setting['module'], '_', ' '));
                }
                $sections[$setting['module']] = $sectionName;
            }

            // Get Setting title and description translations as Section name
            $arrDefault = [
                'title' => 'settings::'.$setting['slug'],
                'description' => 'settings::'.$setting['slug'].'_desc'
            ];
            foreach ($arrDefault as $key => $name) {
                ${$key} = lang($name);
                
                if (strpos(${$key}, 'FIXME(') !== false) {
                    ${$key} = $setting[$key];
                }

                $setting[$key] = ${$key};
            }

            $dataSection[$setting['module']][] = $setting;
        }

        $this->template->build_json([
            'success' => true,
            'data' => compact('sections', 'dataSection')
        ]);
    }
}