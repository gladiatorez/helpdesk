const webpack = require('webpack');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const helper = require('./helper');
const entryFiles = require('./entryFiles');
const isDev = process.env.NODE_ENV === 'development';

module.exports = {
  entry: entryFiles(isDev),
  resolve: {
    extensions: ['.js', '.vue'],
    alias: {
      '@': helper.root(),
    }
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: ['@babel/plugin-syntax-dynamic-import','@babel/plugin-transform-runtime']
          }
        }
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { sourceMap: isDev } },
          { 
            loader: 'sass-loader', 
            options: { 
              sourceMap: isDev, 
              implementation: require("sass"), 
             
            } 
          }
        ]
      },
      {
        test: /\.sass$/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { sourceMap: isDev } },
          { 
            loader: 'sass-loader', 
            options: { 
              sourceMap: isDev, 
              implementation: require("sass"), 
              prependData: `@import "@/resources/vuetify/scss/variables.scss"`,
             
            } 
          }
        ]
      },
      {
        test: /\.(png|jpe?g|gif)$/i,
        use: [
          { 
            loader: 'file-loader',
          }
        ]
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        use: [
          { 
            loader: 'file-loader',
            options: {
              esModule: false,
            }
          }
        ],
      },
      {
        test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 10240,
              mimetype: 'image/svg+xml'
            }
          }
        ]
      }
    ]
  },
  plugins: [
    new ManifestPlugin({
      publicPath: 'dist/'
    }),
    new MiniCssExtractPlugin({
      filename: "[name].css",
      chunkFilename: "[id].[hash].css",
      ignoreOrder: false
    }),
    new VueLoaderPlugin(),
  ],
  externals: {
    "vue": "Vue",
  }
};