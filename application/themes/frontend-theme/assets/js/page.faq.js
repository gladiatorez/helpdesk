VUE.$on('onPagingChange', function(payload) {
	document.location.href = SITE_URL + 'faq/category/' + ufhy.faqCategorySelected + '?page=' + payload;
});