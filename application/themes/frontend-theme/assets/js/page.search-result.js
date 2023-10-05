VUE.$on('onPagingChange', function (payload) {
	document.location.href = SITE_URL + 'search?q=' + ufhy.searchQuery + '&page=' + payload;
});