const path = require('path');
const glob = require('glob');

const splitString = function (stringToSplit, separator) {
	return stringToSplit.split(separator);
};

const appPath = path.join(__dirname, '../application');
const pathEntries = [
	path.resolve(appPath, 'themes/frontend-theme/assets/js/*.js'),
	path.resolve(appPath, 'themes/backend-theme/assets/js/*.js'),
	path.resolve(appPath, 'themes/backend-auth/assets/js/*.js'),
];

module.exports = function(isDev) {
	let bundleFiles = {
		'ufhy-icon': path.resolve(__dirname, '../resources/ufhy-icon.js'),
		// 'roboto-font': path.resolve(__dirname, '../resources/roboto-font/roboto-font.js'),
		// 'lato-font': path.resolve(__dirname, '../resources/lato-font/lato-font.js'),
		'profiler': path.resolve(appPath, 'views/profiler/profiler.js'),
		'vuetify': path.resolve(__dirname, '../resources/vuetify/loader.js'),
	};
	if (isDev) {
		bundleFiles = {
			...bundleFiles,
			'webpack-dev-server': 'webpack-dev-server/client?http://0.0.0.0:9000',
			'only-dev-server': 'webpack/hot/only-dev-server',
		}
	}

	pathEntries.forEach((path) => {
		const globpaths = glob.sync(path);
		const parentdir = 'js';
		const ext = 'js';
		globpaths.forEach((path) => {
			const key = splitString(path, `/${parentdir}/`).slice(-1)[0].replace(`.${ext}`, '');
			bundleFiles[key] = path;
		});
	});

	return bundleFiles;
}