<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['supported_languages'] = array(
    'en' => array(
        'name'        => 'English',
        'folder'    => 'english',
        'direction'    => 'ltr',
        'codes'        => array('en', 'english', 'en_US'),
        'ckeditor'    => null
    ),
);

$config['check_http_accept_language'] = FALSE;