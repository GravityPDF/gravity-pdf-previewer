const webpack = require('webpack')
const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const PROD = (process.env.NODE_ENV === 'production')
const chunkPath = __dirname + '/dist'

module.exports = {
  entry: {
    'previewer': './assets/js/main.js'
  },
  output: {
    path: __dirname + '/dist/js/',
    filename: '[name].min.js',
    publicPath: chunkPath
  },
  mode: PROD ? 'production' : 'development',
  devtool: PROD ? false : 'eval-source-map',
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
  externals: {
    'jquery': 'jQuery'
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env': {
        'NODE_ENV': JSON.stringify('production')
      }
    }),
    new MiniCssExtractPlugin({
      filename: '../css/[name].min.css',
      chunkFilename: '[id].css',
    }),
    new UglifyJSPlugin({
      parallel: true,
      sourceMap: true,
      uglifyOptions: {
        output: {
          comments: false
        }
      }
    }),
    new CopyWebpackPlugin([{
      from: 'node_modules/pdfjs-dist-viewer-min/build/minified/',
      to: __dirname + '/dist/viewer/',
      ignore: ['*.pdf']
    },
      {
        from: 'assets/viewer.php',
        to: __dirname + '/dist/viewer/web/viewer.php',
        force: true
      }])
  ]
}
