const webpackMerge = require('webpack-merge')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const production = require('./webpack-configs/production')
const development = require('./webpack-configs/development')
const PROD = process.env.NODE_ENV === 'production'
const modeConfig = PROD ? production : development

module.exports = webpackMerge(
  {
    entry: { 'previewer': './assets/js/main.js' },
    output: {
      path: __dirname + '/dist/',
      filename: '[name].min.js',
      publicPath: __dirname + '/dist/'
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          loader: 'babel-loader',
          options: { babelrc: true }
        },
        {
          test: /\.s?css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader'
          ]
        }
      ]
    },
    externals: { 'jquery': 'jQuery' }
  },
  modeConfig
)
