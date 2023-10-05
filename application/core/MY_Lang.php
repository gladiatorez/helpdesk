<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Router class */
require APPPATH."third_party/MX/Lang.php";

class MY_Lang extends MX_Lang
{
    public function line($line, $log_errors = TRUE)
    {
        $value = isset($this->language[$line]) ? $this->language[$line] : FALSE;
        if ($value !== FALSE) {
            return $value;
        }

        // Because killer robots like unicorns!
        if ($value === FALSE && $log_errors === TRUE)
        {
            log_message('error', 'Could not find the language line "'.$line.'"');
        }

        return "FIXME('{$line}')";
    }
}