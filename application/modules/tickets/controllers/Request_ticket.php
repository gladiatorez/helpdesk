<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Request_ticket
 *
 * @property Category_model $category_model
 * @property Group_model $group_model
 * @property The_tickets $the_tickets
 * @property Tickets_model $tickets_model
 */
class Request_ticket extends Public_Controller
{
    public $_section = 'request-ticket';

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('tickets/tickets');
        $this->template->title('Request ticket');
    }
    
    public function index()
    {
        $this->load->model('references/category_model');
        $categories = $this->category_model->set_cache('all_categories_parent_active')
            ->order_by('name')
            ->get_all(['active' => 'A', 'parent_id' => '0']);
        $categoryOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoryOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name
                ];
            }
        }
        $this->template->set('category_options', $categoryOptions ? $categoryOptions : []);

        $this->template
            ->append_js('core::dropzone.min.js', true, 'page-vue')
            ->append_css('core::basic.min.css', true, 'libpage')
            ->append_css('core::dropzone.min.css', true, 'libpage')
            ->append_js('webpack::/dist/page.request-ticket.js', true, 'page-vue')
            ->build('tickets/request_ticket_view');
    }

    public function uploadattachment()
    {
        $this->load->library('files/the_file');
        $checkUpload = The_file::checkUpload(48, false, 'file', false, false, false, 'jpg|jpeg|png|doc|docx|xls|xlsx|pdf');
        if (!$checkUpload['status']) {
            $this->output->set_status_header(500, $checkUpload['message']);
            echo $checkUpload['message'];
        }
    }

    public function services()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $this->load->model('references/category_model');
        $categories = $this->category_model
            ->order_by('name')
            ->get_all(['active' => 'A', 'parent_id' => '0']);
        $servicesOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $servicesOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name,
                    'description' => $category->description,
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $servicesOptions
        ]);
    }

    public function sbu()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $this->load->model('references/company_model');
        $companies = $this->company_model
            ->order_by('name')
            ->get_all(['active' => 'A']);
        $sbuOptions = [];
        if ($companies) {
            foreach ($companies as $companies) {
                $sbuOptions[] = [
                    'value' => $companies->id,
                    'text' => $companies->name,
                    'description' => $companies->abbr,
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $sbuOptions
        ]);
    }

    public function branch()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $sbuid = $this->input->get('parent_id');

        $this->load->model('references/company_branch_model');
        $branch = $this->company_branch_model
            ->order_by('name')
            ->get_all(['active' => 'A', 'company_id' => $sbuid]);
        $branchOptions = [];
        if ($branch) {
            foreach ($branch as $branch) {
                $branchOptions[] = [
                    'value' => $branch->id,
                    'text' => $branch->name,
                    'description' => $branch->name,
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $branchOptions
        ]);
    }

    
    public function category()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $parentCategory = $this->input->get('parent_id');

        $this->load->model('references/category_model');
        $categories = $this->category_model
            ->order_by('name')
            ->get_all(['active' => 'A', 'parent_id' => $parentCategory]);
        $categoriesOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name,
                    'description' => $category->description,
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $categoriesOptions
        ]);
    }

    public function categorysub()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $parentCategory = $this->input->get('parent_id');
        if (!$parentCategory) {
            $this->template->build_json([
                'success' => false,
                'message' => lang('msg::request_failed')
            ]);
            return false;
        }

        $this->load->model('references/category_model');
        $categories = $this->category_model->order_by('name')->get_all([
            'parent_id' => $parentCategory,
            'active' => 'A',
            'task' => '0',
        ]);
        $categoriesOptions = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoriesOptions[] = [
                    'value' => $category->id,
                    'text' => $category->name,
                    'description' => $category->description,
                ];
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $categoriesOptions
        ]);
    }

    public function subjectsuggestion()
    {
        $q = $this->input->get('q', TRUE);
        $categoryId = $this->input->get('category', TRUE);
        $subCategoryId = $this->input->get('subcategory', TRUE);

        $this->load->model('tickets/tickets_model');
        $tickets = $this->tickets_model->where('subject', 'like', $q)
            ->as_array()
            ->fields('subject')
            ->limit(5);
        if ($categoryId) {
            $tickets = $tickets->where(['category_id' => $categoryId]);
        }
        if ($subCategoryId) {
            $tickets = $tickets->where(['category_sub_id' => $subCategoryId]);
        }
        $tickets = $tickets->get_all();

        $suggestions = [];
        if ($tickets) {
            foreach ($tickets as $ticket) {
                if (!in_array($ticket['subject'], $suggestions)) {
                    array_push($suggestions, $ticket['subject']);
                }
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $suggestions
        ]);
    }

    public function niksuggestion()
    {
        $q = $this->input->get('q', TRUE);

        $this->load->model('informer/informer_model');
       $tickets = $this->informer_model->where('nik like "%'.$q.'%" or full_name like "%'.$q.'%"')
            ->as_array()
            ->fields('nik,full_name');
       
        $tickets = $tickets->get_all();

        $suggestions = [];
        if ($tickets) {
            foreach ($tickets as $ticket) {
                if (!in_array($ticket['nik'], $suggestions)) {
                    array_push($suggestions, $ticket['nik'].' - '.$ticket['full_name']);
                }
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $suggestions
        ]);
    }

    /*public function email_()
    {
        $email = $this->input->get('email');

        $this->load->model('informer/informer_model');
       $tickets = $this->informer_model->where('nik like "%'.$q.'%" or full_name like "%'.$q.'%"')
            ->as_array()
            ->fields('nik,full_name');
       
        $tickets = $tickets->get_all();

        $suggestions = [];
        if ($tickets) {
            foreach ($tickets as $ticket) {
                if (!in_array($ticket['nik'], $suggestions)) {
                    array_push($suggestions, $ticket['nik'].' - '.$ticket['full_name']);
                }
            }
        }

        $this->template->build_json([
            'success' => true,
            'rows' => $suggestions
        ]);
    }*/

    public function send()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        if (!frontendIsLoggedIn()) {
            $this->form_validation->set_rules('email', 'lang:tickets::lb:email', 'trim|required|max_length[100]|valid_email|callback__check_email');
            $this->form_validation->set_rules('sbuId', 'lang:tickets::lb:sbuId', 'trim|required');
            $this->form_validation->set_rules('BranchId', 'lang:tickets::lb:BranchId', 'trim|required');
        }
        $this->form_validation->set_rules('subject', 'lang:tickets::lb:subject', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('servicesId', 'lang:tickets::lb:services', 'trim|required');
        $this->form_validation->set_rules('categoryId', 'lang:tickets::lb:category', 'trim|required');
        $this->form_validation->set_rules('categorySubId', 'lang:tickets::lb:category_sub', 'trim|required');
        $this->form_validation->set_rules('ticketDescr', 'lang:tickets::lb:descr', 'trim|required');
        if ($this->form_validation->run()) 
        {
            $filesCount = (int)$this->input->post('fileCount');
            $sbu = $this->input->post('sbuId');
            $company_branch_id = $this->input->post('BranchId');
            
            $dataTicket = array(
                'subject' => $this->input->post('subject'),
                'description' => $this->input->post('ticketDescr', true),
                'services_id' => $this->input->post('servicesId'),
                'category_id' => $this->input->post('categoryId'),
                'category_sub_id' => $this->input->post('categorySubId'),
                'network' => $this->input->post('network'),
            );

            $email = $this->input->post('email', true);
            if (frontendIsLoggedIn()) {
                $email = $this->_currentUserFrontend->email;
            }

            $this->load->library('tickets/the_tickets');
            $requestTicket = $this->the_tickets->requestTicket($dataTicket, $email, true, $filesCount, $sbu, $company_branch_id);
            if ($requestTicket) {
                $this->the_tickets->ticketPosting($requestTicket);
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ]);
            } else {
                $this->output->set_status_header('400', $this->the_tickets->getErrors());
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
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


    public function sendBot()
    {
        $this->output->enable_profiler(false);
        $this->template->set_layout(false);

       
            $filesCount = (int)$this->input->post('fileCount');
            $sbu = $this->input->post('sbuId');
            $company_branch_id = $this->input->post('BranchId');
            $email = $this->input->post('email', true);
            
            $dataTicket = array(
                'subject' => $this->input->post('subject'),
                'description' => $this->input->post('ticketDescr', true),
                'services_id' => $this->input->post('servicesId'),
                'category_id' => $this->input->post('categoryId'),
                'category_sub_id' => $this->input->post('categorySubId'),
                'network' => $this->input->post('network'),
            );

            
         

            $this->load->library('tickets/the_tickets');
            $requestTicket = $this->the_tickets->requestTicket($dataTicket, $email, true, $filesCount, $sbu, $company_branch_id);
            if ($requestTicket) {
                $this->the_tickets->ticketPosting($requestTicket);
                $this->template->build_json([
                    'success' => true,
                    'message' => $this->the_tickets->getMessages()
                ]);
            } else {
                $this->output->set_status_header('400', $this->the_tickets->getErrors());
                $this->template->build_json([
                    'success' => false,
                    'message' => $this->the_tickets->getErrors()
                ]);
            }
            
           
        echo $requestTicket;
    }

    public function _check_email($email = '')
    {
        if (!empty($email)) 
        {
            $this->load->model('users/group_model');
            $group = $this->group_model->canSendTicket();

            if (!$group) {
                $this->form_validation->set_message('_check_email', 'Something wrong, please call direct our helpdesk');
                return false;
            }

            $user = $this->user_model
                ->where('group_id', $group)
                ->get(['email' => $email]);
            if (!$user) {
                $this->form_validation->set_message('_check_email', 'Sory, email your entered not registered yet in our system, please call direct our helpdesk');
                return false;
            }
        }

        return true;
    }
    public function success()
    {
        $this->template
            ->title('Request ticket success')
            ->build('tickets/request_ticket_success');
    }
}