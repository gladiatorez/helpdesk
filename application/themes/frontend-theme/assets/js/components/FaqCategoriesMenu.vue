<template>
	<div>
		<v-menu offset-y>
			<template v-slot:activator="{ on }">
				<v-btn block text v-on="on">
					Explore FAQ
					<v-icon right style="font-size: 13px;">ms-Icon ms-Icon--ChevronDown</v-icon>
				</v-btn>
			</template>
			<v-list>
				<template v-for="(category, index) in categories">
					<v-list-item 
						:key="index" 
						:href="category.url">
						<v-list-item-title>
							{{category.title}}
						</v-list-item-title>
					</v-list-item>
				</template>
			</v-list>
		</v-menu>
	</div>
</template>

<script>
export default {
	name: 'faq-categories-menu',
	data() {
		return {
			showMenu: false,
		}
	},
	computed: {
		categories() {
			let categories = [];
			if (typeof ufhy.faqCategories !== "undefined") {
				ufhy.faqCategories.forEach(element => {
					categories.push({
						url: window.SITE_URL + 'faq/category/' + element.id,
						title: element.name
					})
				});
			}
			
			return categories;
		}
	}
}
</script>

