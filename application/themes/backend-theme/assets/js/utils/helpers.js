import api from './api'

export const fetchDtRows = (url, options, search = '') => {
	const { sortBy, sortDesc, page, itemsPerPage, filters } = options
	
	let sortOrders = []
	for (let index = 0; index < sortDesc.length; index++) {
		const element = sortDesc[index]
		sortOrders.push(element ? 'desc' : 'asc')
	}
	return new Promise((resolve, reject) => {
		api().get(url, {
			params: {
				sortFields: sortBy,
				sortOrders: sortOrders,
				page: page,
				limit: itemsPerPage,
				search: search,
				filters: filters,
			}
		}).then(response => {
			resolve({
				items: Object.freeze(response.data.rows),
				total: response.data.rows ? response.data.total : 0,
			});
		}).catch(error => {
			reject(error)
		})
	});
}


export const apiUrl = (uri) => {
	if (typeof API_URL !== "undefined") {
		if (API_URL.substr(API_URL.length - 1, API_URL.length) === '/') {
			return API_URL + uri;
		} else {
			return API_URL + '/' + uri;
		}
	}

	return uri;
};

export const validEmailKalla = (email) => {
	const re = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
	if (re.test(email)) {
		if (email.indexOf('@kallagroup.co.id', email.length - '@kallagroup.co.id'.length) !== -1) {
			return true;
		}
	} 

	return false;
};

export const unicode_charAt = function(string, index) {
    var first = string.charCodeAt(index);
    var second;
    if (first >= 0xD800 && first <= 0xDBFF && string.length > index + 1) {
        second = string.charCodeAt(index + 1);
        if (second >= 0xDC00 && second <= 0xDFFF) {
            return string.substring(index, index + 2);
        }
    }
    return string[index];
};

export const unicode_slice = function(string, start, end) {
    var accumulator = "";
    var character;
    var stringIndex = 0;
    var unicodeIndex = 0;
    var length = string.length;

    while (stringIndex < length) {
        character = unicode_charAt(string, stringIndex);
        if (unicodeIndex >= start && unicodeIndex < end) {
            accumulator += character;
        }
        stringIndex += character.length;
        unicodeIndex += 1;
    }
    return accumulator;
};