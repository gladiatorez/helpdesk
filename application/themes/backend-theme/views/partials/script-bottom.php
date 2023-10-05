<?php

echo Asset::render_js('vue');
echo Asset::render_js('vuetify');

if (ENVIRONMENT === 'development') {
    echo Asset::render_js('webpack-dev-server');
}
if (ENVIRONMENT !== 'production') {
    echo Asset::render_js('webpack-profiler');
}

echo Asset::render_js('webpack-bundle');
echo Asset::render_js('webpack-page');