<template>
	<v-card outlined class="mb-4">
		<v-card-title>
			<span>Company info</span>
			<v-spacer></v-spacer>
			<v-btn icon @click="editAction">
				<v-icon>edit</v-icon>
			</v-btn>
		</v-card-title>
		<v-list>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">NIK</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.nik}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Position</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.position}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Company</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.companyName}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Location</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.companyBranchName}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Department</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.departmentName}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
		</v-list>

		<v-dialog scrollable persistent 
			v-model="showModal" 
			origin="top center" 
			max-width="800px">
			<v-card :loading="loading" :disabled="loading">
				<v-card-title>Edit company info</v-card-title>
				
				<v-card-text>
					<v-row wrap>
						<v-col cols="12" md="4">
							<v-text-field required box
								background-color="white"
								v-model="row.nik"
								:disabled="loading"
								:error-messages="errorMsg.nik"
								label="NIK"
							></v-text-field>
						</v-col>
						<v-col cols="12" md="8">
							<v-text-field required box
								background-color="white"
								v-model="row.position"
								:disabled="loading"
								:error-messages="errorMsg.position"
								label="Position"
							></v-text-field>
						</v-col>
						<v-col cols="12" md="6">
							<v-autocomplete required box
								background-color="white"
								v-model="row.companyId"
								:items="companyOptions"
								:disabled="loading"
								:error-messages="errorMsg.companyId"
								label="Company"
							></v-autocomplete>
						</v-col>

						<v-col cols="12" md="6">
							<v-autocomplete required box
								background-color="white"
								v-model="row.companyBranchId"
								:items="companyBranchOptions"
								:disabled="loading"
								:error-messages="errorMsg.companyBranchId"
								label="Location"
							></v-autocomplete>
						</v-col>
						
						<v-col cols="12" md="6">
							<v-autocomplete required box persistent-hint
								background-color="white"
								v-model="row.departmentId"
								:items="departmentOptions"
								:disabled="loading"
								:error-messages="errorMsg.departmentId"
								label="Department"
								hint="If you dont found your department, choose 'OTHERS'"
							></v-autocomplete>

							<v-text-field required box
								v-if="row.departmentId === '-1'"
								:disabled="loading"
								background-color="white"
								v-model="row.departmentOther"
								:error-messages="errorMsg.departmentOther"
								label="Others department"
							></v-text-field>
						</v-col>
					</v-row>
				</v-card-text>

				<v-card-actions class="pa-3">
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
	name: 'profile-company-card',
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
				nik: '',
				position: '',
				companyId: null,
				companyBranchId: null,
				departmentId: null,
				departmentOther: ''
			},
			errorMsg: {
				nik: '',
				position: '',
				companyId: '',
				companyBranchId: '',
				departmentId: '',
				departmentOther: ''
			},
			companyOptions: [],
			departmentOptions: []
		}
	},
	computed: {
		companyBranchOptions() {
			const that = this;
			const companies = that.companyOptions.filter(element => {
				return element.value === that.row.companyId;
			});
			
			if (companies.length <= 0) {
				return [];
			}
			
			let options = [];
			companies.forEach(company => {
				if (company.branch) {
					company.branch.forEach(branch => {
						options.push({
							text: branch.name,
							value: branch.id
						})
					});
				}
			});
			return options;
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
				companyId: this.item.companyId,
				companyBranchId: this.item.companyBranchId,
				departmentId: this.item.departmentId,
				departmentOther: this.item.departmentName
			};
			this.showModal = true;
		},
		clearErrorMsg() {
			this.errorMsg = {  
				nik: '',
				position: '',
				companyId: '',
				companyBranchId: '',
				departmentId: '',
				departmentOther: ''
			}
		},
		fetchOptions() {
			const that = this;
			that.loading = true;
			that.$axios.get('profile/personalinfo/companyoptions')
				.then(response => {
					const { data } = response;
					that.companyOptions = [];
					data.companyOptions.forEach(element => {
						that.companyOptions.push({
							value: element.id,
							text: element.name,
							branch: element.branch
						});
					});
					
					that.departmentOptions = [];
					data.departmentOptions.forEach(element => {
						that.departmentOptions.push({
							value: element.id,
							text: element.name
						});
					});
					that.departmentOptions.push({
						value: '-1',
						text: 'OTHERS'
					});

					that.loading = false;
				})
				.catch(error => {
					console.log(error);
					this.loading = false;
				})
		},
		saveChangeAction() {
			if (this.loading) {
				return false;
			}

			this.clearErrorMsg();
			this.loading = true;

			const item = new FormData();
			item.set('nik', this.row.nik);
			item.set('position', this.row.position);
			item.set('companyId', this.row.companyId);
			item.set('companyBranchId', this.row.companyBranchId);
			item.set('departmentId', this.row.departmentId);
			item.set('departmentOther', this.row.departmentOther);

			this.$axios.post('profile/personalinfo/savecompany', item)
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
