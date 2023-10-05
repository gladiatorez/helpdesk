<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Profile
 *
 */
class Profile extends Informer_Controller
{
    public $_section = 'account';

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('profile/profile');

        $this->template->append_js('webpack::dist/frontend-account.js', true, 'account-bundle');
        $this->template->title('Account');
    }

    public function index()
    {
        $this->template
            ->pageTitle('Information')
            ->set('acc_section', 'information')
            ->build('profile/profile_view');
    }

    /*
    public function personal_info()
    {
        $accountProfile = $this->the_auth_frontend->getUserLogin();

        // general information
        $generalInformation = [
            ['label' => lang('profile::full_name'), 'value' => $accountProfile->informer->full_name],
            ['label' => lang('profile::phone'), 'value' => $accountProfile->informer->phone],
        ];
        $this->template->set('general_information', $generalInformation);

        // company information
        $this->load->model('references/company_model');
        $company = $this->company_model->set_cache('company_' . $accountProfile->informer->company_id)
            ->get(['id' => $accountProfile->informer->company_id]);
        $this->load->model('references/department_model');
        $department = $this->department_model->set_cache('department_' . $accountProfile->informer->department_id)
            ->get(['id' => $accountProfile->informer->department_id]);
        $companyInformation = [
            ['label' => lang('profile::nik'), 'value' => $accountProfile->informer->nik],
            ['label' => lang('profile::position'), 'value' => $accountProfile->informer->position],
            ['label' => lang('profile::company'), 'value' => $company ? $company->name : $accountProfile->informer->company_other],
            ['label' => lang('profile::department'), 'value' => $department ? $department->name : $accountProfile->informer->department_other],
        ];
        $this->template->set('company_information', $companyInformation);

        // account information
        $accountInformation = [
            ['label' => lang('profile::email'), 'value' => $accountProfile->email],
            ['label' => lang('profile::username'), 'value' => $accountProfile->username],
            ['label' => lang('profile::password'), 'value' => '••••••••'],
            ['label' => lang('profile::last_login'), 'value' => date('M d, Y H:i')],
        ];
        $this->template->set('account_information', $accountInformation);
        
        $this->template
            ->pageTitle('Person info')
            ->set('acc_section', 'personal_info')
            ->append_js('webpack::dist/page.account.personalInfo.js', true, 'page')
            ->build('profile/personal_info_view');
    }

    public function history()
    {
        $this->template
            ->pageTitle('History')
            ->set('acc_section', 'history')
            ->build('profile/history_view');
    }
    */
}