<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_reports_widget_api
 *
 * @property The_tickets $the_tickets
 */
class Backend_reports_widget_api extends Backend_Api_Controller
{
    public $_section = 'by_widget';

    public function __construct()
    {
        parent::__construct();
    }

    public function form_options()
    {
        $this->load->model('references/category_model');
        $categories = $this->category_model->fields('id,name')
            ->as_array()
            ->order_by('parent_id', 'ASC')
            ->with('child', ['fields:id,name'])
            ->get_all(['active' => 'A', 'parent_id' => '0']);
        if (!$categories) {
            $this->template->build_json([]);
            return false;
        }

        $categoryOptions = [];
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'value' => $category['id'],
                'text' => $category['name'],
                'childrens' => $category['child']
            ];
        }
        $categoryOptions[] = [
            'value' => 'ALL',
            'text' => 'All category except oracle',
            'childrens' => []
        ];

        // get staff
        $this->load->model('staff/staff_view_model');
        $staffs = $this->staff_view_model->fields('id,full_name AS fullName,position')
            ->as_array()
            ->order_by('full_name')
            ->get_all(['active' => '1']);
        $staffOptions = [
            ['value' => 'ALL', 'text' => 'All']
        ];
        if ($staffs) {
            foreach ($staffs as $staff) {
                $staffOptions[] = [
                    'value' => $staff['id'],
                    'text' => $staff['fullName'],
                ];
            }
        }
        
        // get sbu
        $this->load->model('references/company_model');
        $companies = $this->company_model->fields(['id','name'])
            ->order_by('name')
            ->as_array()
            ->get_all(['active' => 'A']);
        $companyOptions = [
            ['value' => 'ALL', 'text' => 'All']
        ];
        if ($companies) {
            foreach ($companies as $company) {
                $companyOptions[] = [
                    'value' => $company['id'],
                    'text' => $company['name'],
                ];
            }
        }

        $this->template->build_json([
            'categories' => $categoryOptions ? $categoryOptions : [],
            'staffs' => $staffOptions,
            'companies' => $companyOptions
        ]);
    }

    public function request_reports()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;

        $this->form_validation->set_rules('dateStart', 'From date', 'required');
        $this->form_validation->set_rules('dateEnd', 'To date', 'required');
        $this->form_validation->set_rules('category', 'Category', 'required');
        $this->form_validation->set_rules('categorySub', 'Category sub', 'required');
        $this->form_validation->set_rules('sbu', 'SBU', 'required');
        $this->form_validation->set_rules('staff', 'Staff', 'required');
        $this->form_validation->set_rules('flag', 'Status', 'required');
        if ($this->form_validation->run())
        {
            $data = [
                'dateStart' => $this->input->post('dateStart'),
                'dateEnd' => $this->input->post('dateEnd'),
                'category' => $this->input->post('category'),
                'categorySub' => $this->input->post('categorySub'),
                'sbu' => $this->input->post('sbu'),
                'staff' => $this->input->post('staff'),
                'flag' => $this->input->post('flag'),
            ];
            $this->load->library('tickets/the_tickets');
            $requestReport = $this->the_tickets->requestReports($data);
            if ($requestReport === false) {
                $this->output->set_status_header('400', $this->the_tickets->getErrors());
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            } else {
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages(),
                    'file'    => $requestReport
                ]);
            }
        } 
        else {
            $this->output->set_status_header('400', lang('msg::saving_failed'));
            $this->template->build_json([
                'success' => false,
                'message' => $this->form_validation->error_array()
            ]);
        }
    }
}