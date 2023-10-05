<template>
	<v-card outlined>
		<v-card-title>
			<span>Company Info</span>
			<v-spacer></v-spacer>
			<v-btn icon small class="ma-0" @click="editAction">
				<v-icon>edit</v-icon>
			</v-btn>
		</v-card-title>
		<v-list dense>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::nik')}}</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.nik}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::position')}}</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.position}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::company')}}</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.companyName}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
		</v-list>

		<template v-if="showModal">
			<v-dialog scrollable persistent
				v-model="showModal"
				max-width="600">
				<v-card :loading="loading" :disabled="loading">
					<v-card-title>Edit account info</v-card-title>
					<v-card-text>
						<v-row wrap>
							<v-col cols="12" md="6">
								<v-text-field
									v-model.trim="row.nik"
									:disabled="loading"
									:error-messages="errorMsg.nik"
									:hide-details="!errorMsg.nik"
									:label="$t('profile::nik')"
								></v-text-field>
							</v-col>
							<v-col cols="12" md="6">
								<v-text-field
									v-model.trim="row.position"
									:disabled="loading"
									:error-messages="errorMsg.position"
									:hide-details="!errorMsg.position"
									:label="$t('profile::position')"
								></v-text-field>
							</v-col>
							<v-col cols="12">
								<v-autocomplete
									v-model="row.companyId"
									:disabled="loading"
									:error-messages="errorMsg.companyId"
									:hide-details="!errorMsg.companyId"
									:label="$t('profile::company')"
									:items="companyOptions"
								></v-autocomplete>
							</v-col>
						</v-row>
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
		</template>
	</v-card>
</template>

<script>
export default {
	name: 'profile-company-card',
	props: {
		item: {
			type: Object,
			required: true
		}
	},
	data() {
		return {
			loading: false,
			showModal: false,
			row: {
				nik: '',
				position: '',
				companyId: ''
			},
			errorMsg: {
				nik: '',
				position: '',
				companyId: ''
			},
			companyOptions: []
		}
	},
	created() {
		this.fetchOptions();
	},
	methods: {
		editAction() {
			this.row = {
				nik: this.item.nik,
				position: this.item.position,
				companyId: this.item.companyId
			};
			this.showModal = true;
		},
		clearErrorMsg() {
			this.errorMsg = {
				nik: '',
				position: '',
				companyId: ''
			}
		},
		fetchOptions() {
			const that = this;
			that.loading = true;
			that.$axios.get('addons/companyoptions')
				.then(response => {
					const { data } = response;
					that.companyOptions = data;
					that.loading = false;
				})
				.catch(error => {
					console.log(error);
					this.loading = false;
				})
		},
		saveChangeAction() {
			if (!this.showModal) {
				return false;
			}

			if (this.loading) {
				return false;
			}

			this.clearErrorMsg();
			this.loading = true;

			const item = new FormData();
			item.set('nik', this.row.nik);
			item.set('position', this.row.position);
			item.set('companyId', this.row.companyId ? this.row.companyId : '');

			this.$axios.post('profile/savecompany', item)
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
