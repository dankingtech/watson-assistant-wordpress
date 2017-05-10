webpack = require('webpack')
fs = require('fs');
babelSettings = JSON.parse(fs.readFileSync('.babelrc'));

const config = {
  context: __dirname,
  entry: './src/app.js',
  output: {
    filename: 'app.js',
    path: __dirname + '/../watson-conversation',
  },
  resolve: {
    extensions: ['.js', '.jsx', '.json']
  },
  module: {
    loaders: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
        query: babelSettings
      }
    ]
  },
  cache: false,
  plugins: []
};

if (process.env.NODE_ENV == 'production') {
    babelSettings.plugins.push('transform-react-inline-elements');
    babelSettings.plugins.push('transform-react-constant-elements');
    config.plugins.push(new webpack.optimize.DedupePlugin())
};

module.exports = config;
