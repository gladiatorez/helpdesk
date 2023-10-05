<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Backend_recomendations
 *
 * @property Tickets_view_model $tickets_view_model
 * @property Tickets_log_model $tickets_log_model
 * @property The_mpdf $the_mpdf
 */
class Backend_recomendations extends Backend_Controller
{
    public $_section = 'read';

    public function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(false);
    }

    public function index()
    {
        $id = $this->input->get('id');
        if (!$id) {
            show_error('param not found');
            return false;
        }

        $this->load->model('recomendations/recomendations_model');
        $item = $this->recomendations_model
            ->as_array()
            ->fields(
                'id,letter_no,letter_no_suffix,letter_subject,letter_date,letter_attach,ticket_id,ticket_number,ticket_subject,ticket_informer_full_name,' .
                'ticket_company_name,ticket_department_name,ticket_company_branch_name,'.
                'descr_background,descr_examination,descr_handling,descr_results,descr_recomendation,'.
                'maker_full_name,'.
                'maker_position,approve_full_name,approve_position,approve_user_id,created_at,created_by,updated_at,updated_by,serial_number'
            )
            ->with('photos', ['fields:id,file,description'])
            ->get(['id' => $id]);
        if (!$item) {
            show_error('Data not found');
            return false;
        }

        $this->load->library('the_mpdf', [
            'default_font' => 'FreeSans',
        ]);

        // Write some HTML code:
        $html = $this->load->view('recomendations/recomendations_preview', ['row' => $item], TRUE);
        $nextPage = $this->load->view('recomendations/recomendations_preview_photos', ['row' => $item], TRUE);
        $this->the_mpdf->setOfficialHeader();
        $this->the_mpdf->build(
            $html, 
            'Surat rekomendasi - ' . $item['letter_subject'], 
            'Surat rekomendasi - ' . $item['letter_subject'] . '.pdf',
            $nextPage
        );
    }
}