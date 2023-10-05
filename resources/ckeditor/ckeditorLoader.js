//window.CKEDITOR_BASEPATH = __webpack_public_path__ + 'ckeditor/'
window.CKEDITOR_BASEPATH = __webpack_public_path__ + 'helpdesk-2/public/'

require(`!file-loader?context=dist&outputPath=ckeditor/&name=[name].[ext]!./config.js`)