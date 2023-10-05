<template>
	<div>
		<v-btn icon class="btn-navbar" @click="dialogSearch = true">
			<v-icon>ms-Icon ms-Icon--Search</v-icon>
		</v-btn>
		<v-dialog fullscreen hide-overlay scrollable
			transition="fade-transition"
			v-model="dialogSearch">
			<v-card class="dialog-search-card">
				<v-app-bar flat color="white" height="60"  max-height="60" light>
					<v-container class="mx-auto">
						<v-row align="center">
							<v-toolbar-title>
								<core-logo outlined app-title="MACCA" style="width:135px"></core-logo>
							</v-toolbar-title>

							<v-spacer></v-spacer>
							<v-btn icon large @click="dialogSearch = false">
								<v-icon>close</v-icon>
							</v-btn>
						</v-row>
					</v-container>
				</v-app-bar>
				<v-card-text>
					<v-container class="py-0">
						<v-text-field autofocus light flat solo hide-details
							color="success"
							style="font-size:18px"
							:placeholder="dialogSearchPlaceholder"
							class="dialog-search-input"
							v-model="dialogSearchText"
							v-on:keyup.13="searchAction"
							@input="searchActionAjax"
						></v-text-field>
						
						<v-checkbox hide-details
							class="mt-0 pt-0"
							:ripple="false"
							v-model="dialogSearchOnEnter"
							label="Press enter to search"
							color="success"
						></v-checkbox>
						
						<v-divider class="mt-3"></v-divider>

						<v-subheader v-if="searchHasResult">Search results</v-subheader>
						<v-row>
							<v-col cols="12" md="6">
								<v-list v-if="searchResultFaq.length > 0" two-line class="transparent">
									<template v-for="(faq, indexFaq) in searchResultFaq">
										<v-list-item :key="indexFaq" :href="urlResult(faq.uri)">
											<v-list-item-content>
												<v-list-item-title class="body-2 font-weight-bold">{{faq.title}}</v-list-item-title>
												<v-list-item-sub-title>Our FAQ article</v-list-item-sub-title>
											</v-list-item-content>
										</v-list-item>
									</template>
								</v-list>
							</v-col>

							<v-col cols="12" md="6">
								<v-list v-if="searchResultTicket.length > 0" two-line class="transparent">
									<template v-for="(ticket, indexTicket) in searchResultTicket">
										<v-list-item :key="indexTicket" :href="urlResult(ticket.uri)">
											<v-list-item-content>
												<v-list-item-title class="body-2 font-weight-bold">{{ticket.title}}</v-list-item-title>
												<v-list-item-sub-title>Our ticket article</v-list-item-sub-title>
											</v-list-item-content>
										</v-list-item>
									</template>
								</v-list>
							</v-col>
						</v-row>

						<v-row justify="center" v-if="searchHasResult">
							<v-btn outlined rounded color="success" :href="hrefShowFull">
								Click here to show full
							</v-btn>
						</v-row>
					</v-container>
				</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>

<script>
import _ from 'lodash';
export default {
	name: 'search-button',
	data() {
		return {
			dialogSearch: false,
			dialogSearchOnEnter: false,
			dialogSearchText: '',
			searchResultFaq: [],
			searchResultTicket: [],
		}
	},
	computed: {
		dialogSearchPlaceholder() {
			if (this.$vuetify.breakpoint.lgAndUp) {
				return 'typing here...'
			} else {
				return 'typing here...'
			}
		},
		searchHasResult() {
			if (this.searchResultFaq.length > 0 || this.searchResultTicket.length > 0) {
				return true;
			}
			return false;
		},
		hrefShowFull() {
			return SITE_URL + 'search?q=' + this.dialogSearchText;
		}
	},
	methods: {
		urlResult(url) {
			return SITE_URL + url;
		},
		searchAction() {
			if (!this.dialogSearchOnEnter) {
				return false;
			}
			if (this.dialogSearchText) {
				document.location.href = SITE_URL + 'search?q=' + this.dialogSearchText;
			}
		},
		searchActionAjax: _.debounce(function (e) {
			const that = this;
			if (that.dialogSearchOnEnter) {
				return false;
			}

      that.$axios.get('search', {
        params: {q: that.dialogSearchText}
      }).then(response => {
				const { data } = response;

				that.searchResultFaq = [];
				that.searchResultTicket = [];
				if (data.length > 0) {
					data.forEach(element => {
						if (element.entry_key === 'faq:faq') {
							that.searchResultFaq.push(element);
						} else if (element.entry_key === 'tickets:ticket') {
							that.searchResultTicket.push(element);
						}
					});
				}
      })
    }, 100),
	}
}
</script>
