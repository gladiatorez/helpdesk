<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Email extends CI_Email
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $config['protocol'] = Setting::get('mail_protocol');
        $config['mailtype'] = "html";
        $config['charset'] = 'utf-8';
        $config['crlf'] =  '\r\n'; //Setting::get('mail_line_endings') ? '\r\n' : PHP_EOL;
        $config['newline'] = '\r\n'; //Setting::get('mail_line_endings') ? '\r\n' : PHP_EOL;

        if (Setting::get('mail_protocol') == 'sendmail')
        {
            if (Setting::get('mail_sendmail_path') == '')
            {
                $config['mailpath'] = '/usr/sbin/sendmail';
            }
            else {
                $config['mailpath'] = Settings::get('mail_sendmail_path');
            }
        }

        if (Setting::get('mail_protocol') == 'smtp')
        {
            $config['smtp_host'] = Setting::get('mail_smtp_host');
            $config['smtp_user'] = Setting::get('mail_smtp_user');
            $config['smtp_pass'] = Setting::get('mail_smtp_pass');
            $config['smtp_port'] = Setting::get('mail_smtp_port');
        }

        $this->initialize($config);
    }
}