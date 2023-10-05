<template>
	<v-card outlined class="mb-4">
		<v-card-title>
			<span>General info</span>
			<v-spacer></v-spacer>
			<v-btn icon @click="editAction">
				<v-icon>edit</v-icon>
			</v-btn>
		</v-card-title>
		<v-list>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Full name</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.fullName}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Phone</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.phone}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
		</v-list>

		<v-dialog scrollable persistent 
			v-model="showModal" 
			origin="top center" 
			max-width="800px">
			<v-card :loading="loading" :disabled="loading">
				<v-card-title>Edit general info</v-card-title>
				
				<v-card-text>
					<v-text-field
						v-model="row.fullName"
						:disabled="loading"
						:error-messages="errorMsg.fullName"
						label="Full name"
					></v-text-field>
					<v-text-field
						v-model="row.phone"
						:disabled="loading"
						:error-messages="errorMsg.phone"
						label="Phone"
					></v-text-field>
				</v-card-text>

				<v-card-actions>
					<v-btn color="primary" small depressed @click="saveChangeAction">
						Save changes
					</v-btn>
					<v-btn color="grey darken-1" small outlined @click="showModal = false">
						Cancel
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</v-card>
</template>

<script>
export default {
	name: 'profile-general-card',
	props: {
		item: {
			type: Object,
			required: true
		},
		
	},
	data() {
		return {
			loading: false,
			showModal: false,
			row: {
				fullName: '',
				phone: ''
			},
			errorMsg: {
				fullName: '',
				phone: ''
			}
		}
	},
	methods: {
		editAction() {
			this.row = {
				fullName: this.item.fullName,
				phone: this.item.phone,
			};
			this.showModal = true;
		},
		clearErrorMsg() {
			this.errorMsg = {
				fullName: '',
				phone: ''
			}
		},
		saveChangeAction() {
			if (this.loading) {
				return false;
			}

			this.clearErrorMsg();
			this.loading = true;

			const item = new FormData();
			item.set('fullName', this.row.fullName);
			item.set('phone', this.row.phone);

			this.$axios.post('profile/personalinfo/savegeneral', item)
				.then((response) => {
					const { data } = response;
					this.$coresnackbars.success(data.message);

					if (data.success) {
						this.$emit('save-success');
						this.showModal = false;
					}

					this.loading = false;
				})
				.catch((error) => {
					console.log(error);
					const {statusText, data} = error;
					this.$coresnackbars.error(statusText);

					if (typeof data !== "undefined" && typeof data.message !== "undefined") {
						if (typeof data.message === 'object') {
							this.errorMsg = Object.assign({}, this.errorMsg, data.message);
						}
					}

					this.loading = false;
				});
		}
	}
}
</script>
