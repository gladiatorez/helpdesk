<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Theme_Frontend_theme
{
    public function init()
    {
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
        else {
            Asset::add_path('webpack', [
                'path' => '',
                'js_dir' => '',
                'css_dir' => '',
                'img_dir' => ''
            ]);

            Asset::js('core::vue.min.js', true, 'vue');
        }

        if (ENVIRONMENT !== 'production') {
            Asset::js('webpack::dist/profiler.js', true, 'webpack-profiler');
        }

        if (frontendIsLoggedIn()) {
            $currentUserProfile = [
                'userId' => ci()->_currentUserFrontend->id,
                'fullName' => ci()->_currentUserFrontend->informer ? ci()->_currentUserFrontend->informer->full_name : '',
                'position' => ci()->_currentUserFrontend->informer ? ci()->_currentUserFrontend->informer->position : '',
            ];
            ci()->template->append_metadata(
                '<script>' .
                    'ufhy.user = {' .
                        'profile: ' . json_encode($currentUserProfile) . ', ' .
                    '}' .
                '</script>'
            );
        }

        /*if (backendIsLoggedIn()) {
            $isUserAdmin = isUserAdmin() ? 'true' : 'false';
            $currentUserProfile = [
                'userId' => ci()->currentUser->id,
                'fullName' => ci()->currentUser->profile ? ci()->currentUser->profile->full_name : '',
            ];
            $permissions = ci()->permissions;
            $permissions['profile'] = ['read'];

            if ($isUserAdmin) {
                $permissions['dashboard'] = ['read'];
            }

            ci()->template->append_metadata(
                '<script>' .
                    'if (typeof(ufhy) === "undefined") { var ufhy = {}; }' .
                    'ufhy = {' .
                        'user: {' .
                            'permissions: ' . json_encode($permissions) . ', ' .
                            'isAdmin: ' . $isUserAdmin . ', ' .
                            'profile: ' . json_encode($currentUserProfile) . ', ' .
                        '}' .
                    '}' .
                '</script>'
            );
        }*/

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

        Asset::js('webpack::dist/vuetify.js', true, 'vuetify');
        Asset::css('webpack::dist/vuetify.css', true, 'vuetify');

        Asset::js('webpack::dist/frontend-theme.js', true, 'bundle');
        Asset::css('webpack::dist/frontend-theme.css', true, 'bundle');
        Asset::css('webpack::dist/ufhy-icon.css', true, 'icon');
    }
}