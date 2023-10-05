<template>
	<v-navigation-drawer app fixed class="faq-categories-drawer" mobile-break-point="1024" clipped>
		<v-list shaped class="mt-5">
			<template v-for="(category, index) in categories">
				<v-list-item 
					:key="index" 
					:href="category.url"
					:class="{'active': getActive(category) }">
					<v-list-item-title>
						{{category.title}}
					</v-list-item-title>
				</v-list-item>
			</template>
		</v-list>
	</v-navigation-drawer>
</template>

<script>
export default {
	name: 'faq-categories-drawer',
	computed: {
		categories() {
			let categories = [];
			if (typeof ufhy.faqCategories !== "undefined") {
				ufhy.faqCategories.forEach(element => {
					categories.push({
						id: element.id,
						url: window.SITE_URL + 'faq/category/' + element.id,
						title: element.name
					})
				});
			}
			
			return categories;
		},
	},
	methods: {
		getActive(item) {
			return item.id == ufhy.faqCategorySelected;
		}
	}
}
</script>

