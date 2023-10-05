<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Addons_api
 *
 */
class Addons_api extends Public_Controller
{
    public $_themeName = null;

    public function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(false);
    }

    public function index() {}

    public function companyoptions()
    {
        // get companies
        $this->load->model('references/company_model');
        $companies = $this->company_model->fields('id, name')
            ->order_by('name')
            ->as_array()
            ->with('branch', ['fields:id,name'])
            ->get_all(['active' => 'A']);
        $companyOptions = [];
        if ($companies) {
            foreach ($companies as $company) {
                $companyOptions[] = [
                    'value' => $company['id'],
                    'text' => $company['name'],
                    'branch' => $company['branch']
                ];
            }
        }

        // get departments
        $this->load->model('references/department_model');
        $departments = $this->department_model->fields('id,name')
            ->order_by('name')
            ->as_array()
            ->get_all();
        $departmentOptions = [];
        if ($departments) {
            foreach ($departments as $department) {
                $departmentOptions[] = [
                    'value' => $department['id'],
                    'text' => $department['name']
                ];
            }
        }
        $departmentOptions[] = [
            'value' => '-1',
            'text' => 'OTHERS'
        ];

        $this->template->build_json([
            'companyOptions' => $companyOptions,
            'departmentOptions' => $departmentOptions,
        ]);
    }

    public function i18n()
    {
        $this->template->build_json($this->lang->language);
    }

    public function i18n_by_module()
    {
        $module = $this->input->get('module');

        $langs = [];
        if ($module) {
            $lang = '';
            $deft_lang = $this->config->item('language');
            $idiom = ($lang == '') ? $deft_lang : $lang;
            list($path, $_langfile) = Modules::find($module . '_lang', $module, 'language/' . $idiom . '/');
            
            if ($path) {
                $langs = Modules::load_file($_langfile, $path, 'lang');
            }
        }

        $this->template->build_json($langs);
    }
}