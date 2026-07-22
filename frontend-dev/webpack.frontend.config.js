const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const safePostCssParser = require('postcss-safe-parser');
const svgToMiniDataURI = require('mini-svg-data-uri')
// const autoprefixer = require('autoprefixer');
// const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin

module.exports = (env, argv) => {
  const production = argv.mode !== 'development'
  return {
    devtool: production ? false : 'source-map',
    entry: {
      'bit-gclid': path.resolve(__dirname, 'src/user-frontend/bit_gclid.js'),
    },
    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, '../assets/js/'),
      chunkFilename: production ? '[name].js?v=[contenthash:6]' : '[name].js',
      library: '_bitwelzp_front',
      libraryExport: 'default',
      libraryTarget: 'umd',
      devtoolNamespace: 'bpf',
    },
    optimization: {
      runtimeChunk: false,
      /* splitChunks: {
        cacheGroups: {
          frontend: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors-frontend',
            chunks: chunk => chunk.name === 'bitwelzpFrontend',
          },
        },
      }, */
      minimize: production,
      minimizer: [
        ...(!production ? [] : [
          new TerserPlugin({
            terserOptions: {
              parse: { ecma: 8 },
              compress: {
                drop_console: production,
                ecma: 5,
                warnings: false,
                comparisons: false,
                inline: 2,
              },
              mangle: { safari10: true },
            },
            extractComments: {
              condition: true,
              filename: (fileData) => `${fileData.filename}.LICENSE.txt${fileData.query}`,
              banner: (commentsFile) => `Bitcode license information ${commentsFile}`,
            },
          }),
          new OptimizeCSSAssetsPlugin({
            cssProcessorOptions: {
              parser: safePostCssParser,
              map: !production
                ? {
                  inline: false,
                  annotation: true,
                }
                : false,
            },
            cssProcessorPluginOptions: {
              preset: ['default', { minifyFontValues: { removeQuotes: false } }],
            },
          }),
        ]),
      ],
    },
    plugins: [
      // new BundleAnalyzerPlugin(),
      // new CleanWebpackPlugin(),
      new webpack.DefinePlugin({
        'process.env': {
          NODE_ENV: production ? JSON.stringify('production') : JSON.stringify('development'),
        },
      }),
      new MiniCssExtractPlugin({
        filename: '../css/[name].css?v=[contenthash:6]',
        ignoreOrder: true,
      }),
    ],

    resolve: {
      extensions: ['.js', '.jsx', '.json', '.css'],
    },

    module: {
      strictExportPresence: true,
      rules: [
        {
          test: /\.(jsx?)$/,
          exclude: /node_modules/,
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  // useBuiltIns: 'entry',
                  // corejs: 3,
                  targets: {
                    browsers: !production ? ['Chrome >= 88'] : ['>0.2%', 'ie >= 11'],
                    // browsers: ['>0.2%', 'ie >= 9', 'not dead', 'not op_mini all'],
                  },
                },
              ],
              ['@babel/preset-react', { runtime: 'automatic' }],
            ],
            plugins: [
              ['@babel/plugin-transform-react-jsx', { runtime: 'automatic' }],
              '@babel/plugin-transform-runtime',
            ],
          },
        },
        {
          test: /\.(sa|sc|c)ss$/,
          exclude: /node_modules/,
          use: [
            // 'style-loader',
            {
              loader: MiniCssExtractPlugin.loader,
              options: {
                publicPath: '',
              },
            },
            {
              loader: 'css-loader',
            },
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [
                    ['autoprefixer'],
                  ],
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  outputStyle: 'compressed',
                },
              },
            },
          ],
        },
        {
          test: /\.css$/,
          include: /node_modules/,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.svg$/i,
          use: [
            {
              loader: 'url-loader',
              options: {
                options: {
                  generator: (content) => svgToMiniDataURI(content.toString()),
                },
                name: production ? '[name][contenthash:8].[ext]' : '[name].[ext]',
                outputPath: '../img',
              },
            },
          ],
        },
        {
          test: /\.(jpe?g|png|gif|ttf|woff|woff2|eot)$/i,
          use: [
            {
              loader: 'url-loader',
              options: {
                limit: 10000,
                name: production ? '[name][contenthash:8].[ext]' : '[name].[ext]',
                outputPath: '../img',
              },
            },
          ],
        },
      ],
    },
  }
}
