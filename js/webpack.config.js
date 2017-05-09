module.exports = {
  context: __dirname,
  entry: "./src/app.js",
  output: {
    filename: "app.js",
    path: __dirname + "/../watson-conversation",
  },
  resolve: {
    extensions: ['.js', '.jsx', '.json']
  },
  module: {
    loaders: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        loaders: ["babel-loader"]
      }
    ]
  },
  cache: false
};
