'use strict';

const environment = (process.env.NODE_ENV || 'development').trim();

if (environment === 'development') {
    module.exports = require('./webpack/webpack.dev');
} else {
    module.exports = require('./webpack/webpack.prod');
}