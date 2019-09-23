const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const CopyPlugin = require('copy-webpack-plugin')

module.exports = {
  mode: 'production',
  devtool: false,
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].min.css',
      chunkFilename: '[id].css'
    }),
    new TerserPlugin({
      extractComments: false,
      parallel: true,
      terserOptions: {
        ecma: 6
      }
    }),
    new CopyPlugin({
      patterns: [
        {
          from: 'node_modules/pdfjs-dist-viewer-min/build/minified/',
          to: '../dist/viewer/',
          globOptions: {
            ignore: ['**/**/*.pdf']
          }
        },
        {
          from: 'assets/viewer.php',
          to: '../dist/viewer/web/viewer.php',
          force: true
        }
      ]
    })
  ]
}
