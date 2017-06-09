webpack = require('webpack')
fs = require('fs');

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
        loader: 'babel-loader'
      }
    ]
  },
  resolve: {
    alias: {
      'react': 'preact-compat',
      'react-dom': 'preact-compat',
      'create-react-class': 'preact-compat/lib/create-react-class'
    }
  },
  cache: false,
  plugins: []
};

module.exports = config;
