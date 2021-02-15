const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CopyPlugin = require('copy-webpack-plugin')

module.exports = {
  mode: 'development',
  devtool: 'eval-source-map',
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].min.css',
      chunkFilename: '[id].css',
    }),
    new CopyPlugin({
      patterns: [
        {
          from: 'node_modules/pdfjs-dist-viewer-min-es5/build/minified-es5/',
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
