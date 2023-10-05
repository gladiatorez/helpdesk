<?php

class The_mpdf
{
    protected $_mpdf;

    public function __construct($params = array())
    {
        $params['tempDir'] = APPPATH . 'cache/mpdf';

        $this->_mpdf = new \Mpdf\Mpdf($params);
    }

    public function setOfficialHeader()
    {
        $header = ci()->load->view('mpdf/header_official', [], TRUE);
        $this->_mpdf->SetHTMLHeader($header);
        $this->_mpdf->SetTopMargin(45);

        return $this;
    }

    public function setOfficialFooter()
    {
        $footer = ci()->load->view('mpdf/footer_official', [], TRUE);

        $this->_mpdf->SetHTMLFooter(
            $footer
        );
        return $this;
    }

    public function build($html, $title, $filename, $nextPage = '')
    {
        $this->_mpdf->useSubstitutions = false;
        $this->_mpdf->simpleTables = true;
        $this->_mpdf->packTableData = true;
        $this->_mpdf->use_kwt = true;
        $this->_mpdf->shrink_tables_to_fit = 1;
        $this->_mpdf->imageVars['kallaImg'] = file_get_contents(FCPATH . 'assets/img/logo_kg.png');
        $this->_mpdf->SetAuthor('Vehicle and Logistic System (Velos)');
        $this->_mpdf->SetTitle($title);
        $this->_mpdf->WriteHTML($html);
        if (!empty($nextPage)) {
            $this->_mpdf->AddPage('','','','','','5','5');
            $this->_mpdf->WriteHTML($nextPage);
        }
        $this->_mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }
}