const path = require('path');

module.exports = {
    mode: 'production',
    entry: {
        'lknFraudDetectionForWoocommerceAdminSaveFields.COMPILED': './Admin/js/lknFraudDetectionForWoocommerceAdminSaveFields.js',
        'lknFraudDetectionForWoocommerceOrderIpLinks.COMPILED':    './Admin/js/lknFraudDetectionForWoocommerceOrderIpLinks.js',
        'lknFraudDetectionForWoocommerceAdminBannedIps.COMPILED':  './Admin/js/lknFraudDetectionForWoocommerceAdminBannedIps.js',
        'lknFraudDetectionForWoocommerceAdminBlockedData.COMPILED': './Admin/js/lknFraudDetectionForWoocommerceAdminBlockedData.js',
    },
    output: {
        path:     path.resolve(__dirname, 'Admin/js/compiled'),
        filename: '[name].js',
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use:  ['style-loader', 'css-loader'],
            },
        ],
    },
    externals: {
        jquery: 'jQuery',
    },
};
