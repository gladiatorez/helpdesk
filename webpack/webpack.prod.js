const webpack = require('webpack');
const merge = require('webpack-merge');
const CleanWebpackPlugin  = require('clean-webpack-plugin');
const CopyPlugin            = require('copy-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const common = require('./webpack.common');
const helper  = require('./helper');

const config = merge(common, {
  mode: 'production',
  devtool: 'none',
  output: {
    filename: '[name][hash].js',
    path: helper.root('public','dist'),
    chunkFilename: '[name]-[chunkhash].js',
    publicPath: '/dist/'
    // publicPath: '/helpdesk-2/public/dist/'
  },
  optimization: {
    minimizer: [
      new TerserPlugin({
        cache: true
      }),
      new OptimizeCSSAssetsPlugin({
        cssProcessorPluginOptions: {
          preset: ['default', { discardComments: { removeAll: true } }],
        },
        canPrint: true
      })
    ],
  },
  plugins: [
    new CopyPlugin([
      { from: helper.root('node_modules/vue/dist/vue.js'), to: helper.root('public/assets/js') },
      { from: helper.root('node_modules/vue/dist/vue.min.js'), to: helper.root('public/assets/js') },
      { from: helper.root('application/themes/frontend-theme/assets/js/plugins/dropzone/dropzone.min.js'), to: helper.root('public/assets/js') },
      { from: helper.root('application/themes/frontend-theme/assets/js/plugins/dropzone/basic.min.css'), to: helper.root('public/assets/css') },
      { from: helper.root('application/themes/frontend-theme/assets/js/plugins/dropzone/dropzone.min.css'), to: helper.root('public/assets/css') },
    ], {copyUnmodified: true}),
    new webpack.DefinePlugin({
      "ENV": JSON.stringify("production")
    }),
    new CleanWebpackPlugin([helper.root('public','dist')], {
      allowExternal: true,
      exclude:  ['index.html'],
    }),
  ],
});

module.exports = config;