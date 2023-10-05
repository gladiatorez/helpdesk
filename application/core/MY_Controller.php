<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class MY_Controller
 *
 * @property MY_Loader $load
 * @property MY_Router $router
 * @property MY_Input $input
 * @property CI_Output $output
 * @property CI_Security $security
 * @property CI_Session $session
 * @property CI_URI $uri
 * @property Template $template
 * @property MY_Lang $lang
 * @property MX_Config $config
 * @property MY_Form_validation $form_validation
 * @property Bt_server $bt_server
 */
class MY_Controller extends MX_Controller
{
    public $_themeName = 'frontend-theme';

    protected $_currentUser = null;
    protected $_permissions = null;
    protected $_moduleDetails = false;
    protected $_module = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->library(['session','asset','template','settings/setting']);
        $this->load->helper(['language','url','form','html','application','bootstrap']);

        $siteLang = Setting::get('site_lang');
        $langs = $this->config->item('supported_languages');

        defined('CURRENT_LANGUAGE') or define('CURRENT_LANGUAGE', $siteLang);
        $ufhy['lang'] = $langs[CURRENT_LANGUAGE];
        $ufhy['lang']['code'] = CURRENT_LANGUAGE;
        // reconfig language
        $this->config->set_item('language', $langs[CURRENT_LANGUAGE]['folder']);
        $this->lang->load('application');

        // set template theme
        $this->template->set_theme($this->_themeName);
        $this->template->set_layout('default');
        Asset::add_path('theme', [
            'path' => 'themes/' . $this->_themeName . '/',
            'js_dir' => 'assets/js/',
            'css_dir' => 'assets/css/',
            'img_dir' => 'assets/img/'
        ]);
		Asset::add_path('webpack', [
			'path' => '',
			'js_dir' => '',
			'css_dir' => '',
			'img_dir' => ''
		]);

        $this->_module = ci()->module = $this->router->fetch_module();

        $ufhy['site_name_full'] = $this->template->siteNameFull = Setting::get('site_name_full');
        $ufhy['site_name_abbr'] = $this->template->siteNameAbbr = Setting::get('site_name_abbr');

        // set public vars for views
        $this->load->vars($ufhy);

        // set global variable for javascript
		ci()->template->append_metadata(
			'<script>if (typeof(ufhy) === "undefined") { var ufhy = {}; }</script>'
		);

        // using xss clean by default if method using POST
        $_POST = $this->security->xss_clean($_POST);

        if (ENVIRONMENT !== 'production' || $this->input->get('_debug'))
        {
            !$this->input->is_ajax_request() && $this->output->enable_profiler(true);
        }
    }

    protected function _loadThemeConfiguration()
    {
        $detailFile = APPPATH.'themes/'.$this->_themeName.'/detail'.EXT;
        if ( ! is_file($detailFile)) {
            return false;
        }
        include_once $detailFile;

        $class = 'Theme_'.ucfirst(strtolower(str_replace('-', '_', $this->_themeName)));
        if ( ! class_exists($class)) {
            throw new Exception("Theme $this->_themeName has an incorrect details.php class. It should be called '$class'.");
        }

        $theme = new $class;
        if (method_exists($theme, 'init')) {
            $theme->init();
        }
    }
}

function ci() {
    return get_instance();
}