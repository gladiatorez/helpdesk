<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class The_email
 *
 */
class The_email
{
    protected $ci;

    protected $fallbacks = array();

    protected $parser;

    public function __construct()
    {
        $this->ci =& get_instance();

        $this->ci->load->library('email');
        $this->ci->load->library('parser');

        $this->fallbacks = array(
            'comments'	=> array('comments'	=> 'email/comment'),
            'contact'	=> array('contact'	=> 'email/contact')
        );
    }

    /**
     * @param array $data = array(slug, to)
     * @return bool
     */
    public function send_email($data = array())
    {
        $this->ci =& get_instance();

        $canSendEmail = Setting::get('mail_activity', 'email');
        if (!$canSendEmail) {
            return false;
        }

        $slug = $data['slug'];
        unset($data['slug']);

        /*$config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://mail.kallagroup.co.id';
        $config['smtp_user'] = 'notif-hcis@kallagroup.co.id';
        $config['smtp_pass'] = 'P4SSNotifHCIS';
        $config['smtp_port'] = '465';

        $this->ci->email->initialize($config);*/

        $this->ci->load->model('addons/email_template_model');

        //get all email templates
        $templates = $this->ci->email_template_model->getTemplate($slug);

        //make sure we have something to work with
        if ( ! empty($templates))
        {
            $data['site_name'] = Setting::get('site_name_full');

            $lang	   = isset($data['lang']) ? $data['lang'] : Setting::get('site_lang');
            $from	   = isset($data['from']) ? $data['from'] : Setting::get('server_email');
            $from_name = isset($data['name']) ? $data['name'] : $data['site_name'];
            $reply_to  = isset($data['reply-to']) ? $data['reply-to'] : Setting::get('contact_email');
            $to		   = isset($data['to']) ? $data['to'] : Setting::get('contact_email');

            // perhaps they've passed a pipe separated string, let's switch it to commas for CodeIgniter
            if ( ! is_array($to)) $to = str_replace('|', ',', $to);

            $subject = array_key_exists($lang, $templates) ? $templates[$lang]->subject : $templates['id']->subject ;
            $subject = $this->ci->parser->parse($subject, $data, true, false);

            $body = array_key_exists($lang, $templates) ? $templates[$lang]->body : $templates['id']->body ;
            $body =  $this->ci->parser->parse($body, $data, true, false);

            $this->ci->email->from($from, $from_name);
            $this->ci->email->reply_to($reply_to);
            $this->ci->email->to($to);
            $this->ci->email->subject($subject);
            $this->ci->email->message($body);

            // To send attachments simply pass an array of file paths in Events::trigger('email')
            // $data['attach'][] = /path/to/file.jpg
            // $data['attach'][] = /path/to/file.zip
            if (isset($data['attach']))
            {
                foreach ($data['attach'] AS $attachment)
                {
                    $this->ci->email->attach($attachment);
                }
            }

            return (bool) $this->ci->email->send();
        }

        //return false if we can't find the necessary templates
        log_message('error', 'email template '. $slug .'not found');
        return false;
    }
}