var webpack = require('webpack');
var path = require('path');

var BUILD_DIR = path.resolve(__dirname, 'src/public/crud');
var APP_DIR = path.resolve(__dirname, 'frontend_src/scripts');

var config = {
    devtool: "eval",
    resolve: {
        root: APP_DIR,
        extensions: ['', '.js']
    },
    entry: [
        APP_DIR + '/fields.js'
    ],
    output: {
        path: BUILD_DIR,
        filename: 'bundle.js'
    },
    module: {
        loaders: [
            {
                exclude: /(node_modules|bower_components)/,
                test: /\.js?/,
                include: APP_DIR,
                loaders: ['babel']
            }
        ]
    }
};

module.exports = config;