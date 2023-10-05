<?php

if (!function_exists('indoDate'))
{
    function indoDate($timestamp = '', $date_format = 'l, j F Y | H:i', $suffix = '')
    {
        if (trim($timestamp) == '') {
            $timestamp = time();
        } elseif (!ctype_digit($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        # remove S (st,nd,rd,th) there are no such things in indonesia :p
        $date_format = preg_replace("/S/", "", $date_format);
        $pattern = array(
            '/Mon[^day]/', '/Tue[^sday]/', '/Wed[^nesday]/', '/Thu[^rsday]/',
            '/Fri[^day]/', '/Sat[^urday]/', '/Sun[^day]/', '/Monday/', '/Tuesday/',
            '/Wednesday/', '/Thursday/', '/Friday/', '/Saturday/', '/Sunday/',
            '/Jan[^uary]/', '/Feb[^ruary]/', '/Mar[^ch]/', '/Apr[^il]/', '/May/',
            '/Jun[^e]/', '/Jul[^y]/', '/Aug[^ust]/', '/Sep[^tember]/', '/Oct[^ober]/',
            '/Nov[^ember]/', '/Dec[^ember]/', '/January/', '/February/', '/March/',
            '/April/', '/June/', '/July/', '/August/', '/September/', '/October/',
            '/November/', '/December/',
        );
        $replace = array(
            'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min',
            'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu',
            'Jan ', 'Feb ', 'Mar ', 'Apr ', 'Mei ', 'Jun ', 'Jul ', 'Ags ', 'Sep ', 'Okt', 'Nov ', 'Des ',
            'Januari', 'Februari', 'Maret', 'April', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember',
        );
        $date = date($date_format, $timestamp);
        $date = preg_replace($pattern, $replace, $date);
        $date = "{$date} {$suffix}";
        return $date;
    }
}

if (!function_exists('terbilang')) 
{
    function terbilang($number, $suffix = 'rupiah')
    {
        $search = ['bilyun', 'milyar', 'juts'];
        $replace = ['triliun', 'miliar', 'juta'];
        $formatNumber = new NumberFormatter("id", NumberFormatter::SPELLOUT);
        $format = $formatNumber->format($number);

        return str_replace($search, $replace, $format) . ' ' . $suffix;
    }
}

if (!function_exists('dbprefix')) 
{
    function dbPrefix($table)
    {
        return get_instance()->db->dbprefix($table);
    }
}

if (!function_exists('bgExec'))
{
    function bgExec($command)
    {
        if (substr(php_uname(), 0, 7) == "Windows")
        {
            pclose(popen("start /B " . $command, "r"));
        }
        else {
            exec($command . " > /dev/null &");
        }
    }
}

if (!function_exists('slugify')) {
    /**
     * Make slug from a given string
     *
     * @param string $str The string you want to convert to a slug.
     * @param string $separator The symbol you want in between slug parts.
     * @return string The string in slugified form.
     */
    function slugify($string, $separator = '-')
    {
        $string = trim($string);
        $string = strtolower($string);
        $string = preg_replace('/[\s-]+/', $separator, $string);
        $string = preg_replace("/[^0-9a-zA-Z-]/", '', $string);

        return $string;
    }
}

if (!function_exists('genUid')) 
{
    function genUid()
    {
        require_once APPPATH . "third_party/random_compat-2.0.11/lib/random.php";

        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('site_url_backend')) {
    /**
     * Site URL
     *
     * Create a local URL based on your basepath. Segments can be passed via the
     * first parameter either as a string or an array.
     *
     * @param	string	$uri
     * @param	string	$protocol
     * @return	string
     */
    function site_url_backend($uri = '', $protocol = null)
    {
        return site_url(BACKEND_URLPREFIX . '/' . $uri, $protocol);
    }
}

function is_multi_array($a)
{
    $rv = array_filter($a, 'is_array');
    if (count($rv) > 0) return true;
    return false;
}