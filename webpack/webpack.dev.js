const webpack = require('webpack');
const merge = require('webpack-merge');
const common = require('./webpack.common');
const helper  = require('./helper');

const config = merge(common, {
  mode: 'development',
  devtool: 'source-map',
  output: {
    filename: '[name].js',
    chunkFilename: '[name].[hash].js',
    path:  helper.root('public','dist'),
    // publicPath: 'http://192.168.1.102:9000/dist/'
    publicPath: 'http://localhost:9000/dist/'
  },
  devServer: {
    contentBase: helper.root('public'),
    disableHostCheck: true,
    host: '0.0.0.0',
    port: 9000,
    headers: { "Access-Control-Allow-Origin": "*" },
  },
  optimization: {
    usedExports: true,
    sideEffects: true
  },
  plugins: [
    // new webpack.HotModuleReplacementPlugin(),
    new webpack.DefinePlugin({
      "ENV": JSON.stringify("development")
    }),
  ]
});

module.exports = config;