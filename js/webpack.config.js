var DashboardPlugin = require('webpack-dashboard/plugin');

const config = {
  context: __dirname,
  entry: './src/index.js',
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
  plugins: [
    new DashboardPlugin()
  ]
};

module.exports = config;
