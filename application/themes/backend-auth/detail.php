<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Theme_Backend_Auth
{
    public function init()
    {
        // this only example using google font
        /*ci()->template->append_metadata(
            '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,700|Google+Sans:400,500|Product+Sans:400&amp;lang=en">'
        );*/
        if (ENVIRONMENT === 'development')
        {
            Asset::js('core::vue.js', true, 'vue');

            $httpHost = $_SERVER['HTTP_HOST'];
            $httpHost = explode(':', $httpHost);
            $httpHost = $httpHost[0];

            Asset::add_path('webpack', [
                'path' => 'http://' . $httpHost . ':9000',
                'js_dir' => '/',
                'css_dir' => '/',
                'img_dir' => '/'
            ]);

            Asset::js('webpack::webpack-dev-server.js', true, 'webpack-dev-server');
            Asset::js('webpack::dist/only-dev-server.js', true, 'webpack-dev-server');
            Asset::js('webpack::dist/webpack-dev-server.js', true, 'webpack-server');
        }
        /*else {
            Asset::add_path('webpack', [
                'path' => '',
                'js_dir' => '',
                'css_dir' => '',
                'img_dir' => ''
            ]);

            Asset::js('core::vue.min.js', true, 'vue');
            Asset::js('core::bootstrap-vue.min.js', true, 'bootstrap-vue');
        }*/

        if (ENVIRONMENT !== 'production') {
            Asset::js('webpack::dist/profiler.js', true, 'webpack-profiler');
        }

        $scriptMeta = [
            sprintf('window.API_URL="%s";', site_url('api')),
            sprintf('window.SITE_URL="%s";', site_url()),
            sprintf('window.BASE_URL="%s";', base_url()),
            sprintf('window.SITE_TITLE_FULL="%s";', ci()->template->siteNameFull),
            sprintf('window.SITE_TITLE_ABBR="%s";', ci()->template->siteNameAbbr)
        ];
        ci()->template->append_metadata(
            sprintf(
                '<script>%s</script>', implode(' ', $scriptMeta)
            )
        );

        Asset::css('webpack::dist/roboto-font.css', true, 'robot');

        Asset::css('webpack::dist/backend-auth-theme.css', true, 'webpack-bundle');
    }
}