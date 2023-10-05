<template>
	<v-card class="elevation-base" :loading="loading" :disabled="loading">
		<v-card-title class="body-2 error--text">** All input fields below, is required</v-card-title>
		<v-card-text>
			<v-subheader class="font-weight-bold pl-0 pr-0 grey--text text--darken-3">
				General Information
			</v-subheader>
			<v-row >
				<v-col cols="12" md="7">
					<v-text-field
						:disabled="loading"
						label="Full name"
						placeholder="Enter full name"
						v-model="row.fullName"
						name="fullName"
						:error-messages="errorMsg.fullName"
					></v-text-field>
				</v-col>
				<v-col cols="12" md="5">
					<v-text-field
						:disabled="loading"
						label="Phone"
						placeholder="Enter phone number"
						v-model="row.phone"
						name="phone"
						:error-messages="errorMsg.phone"
					></v-text-field>
				</v-col>
			</v-row>

			<v-subheader class="font-weight-bold pl-0 pr-0 grey--text text--darken-3">
				Account Information
			</v-subheader>
			<v-row row wrap>
				<v-col cols="12" md="4">
					<v-text-field
						:disabled="loading"
						label="Email"
						placeholder="Enter email of kalla domain"
						v-model="row.email"
						name="email"
						:error-messages="errorMsg.email"
					></v-text-field>
				</v-col>
				<v-col cols="12" md="4">
					<v-text-field
						:disabled="loading"
						type="password"
						label="Password"
						placeholder="Enter password"
						v-model="row.password"
						name="password"
						:error-messages="errorMsg.password"
					></v-text-field>
				</v-col>
				<v-col cols="12" md="4">
					<v-text-field
						:disabled="loading"
						type="password"
						label="Confirm password"
						placeholder="Enter password again"
						v-model="row.passowrdConfirm"
						name="passowrdConfirm"
						:error-messages="errorMsg.passowrdConfirm"
					></v-text-field>
				</v-col>
			</v-row>

			<v-subheader class="font-weight-bold pl-0 pr-0 grey--text text--darken-3">
				Company Information
			</v-subheader>
			<v-row row wrap>
				<v-col cols="12" md="4">
					<v-text-field
						:disabled="loading"
						label="NIK"
						placeholder="Enter employee number"
						v-model="row.nik"
						name="nik"
						:error-messages="errorMsg.nik"
					></v-text-field>
				</v-col>
				<v-col cols="12" md="4">
					<v-text-field
						:disabled="loading"
						label="Position"
						placeholder="Enter position name"
						v-model="row.positionName"
						name="positionName"
						:error-messages="errorMsg.positionName"
					></v-text-field>
				</v-col>
				<v-col cols="12" md="6">
					<v-autocomplete
						:disabled="loading"
						label="Company"
						placeholder="Search company"
						v-model="row.companyId"
						name="companyName"
						:error-messages="errorMsg.companyId"
						:items='companyOptions'
					></v-autocomplete>
					<input type="hidden" v-model="row.companyId" name="companyId" />
				</v-col>
				<v-col cols="12" md="6">
					<v-autocomplete
						:disabled="loading"
						label="Location"
						placeholder="Search location"
						v-model="row.companyBranchId"
						name="companyBranchName"
						:error-messages="errorMsg.companyBranchId"
						:items='companyBranchOptions'
					></v-autocomplete>
					<input type="hidden" v-model="row.companyBranchId" name="companyBranchId" />
				</v-col>
				<v-col cols="12" md="6">
					<v-autocomplete persistent-hint
						:disabled="loading"
						label="Department"
						placeholder="Search department"
						v-model="row.departmentId"
						name="departmentName"
						:error-messages="errorMsg.departmentId"
						:items='departmentOptions'
						hint="If you dont found your department, choose 'OTHERS'"
					></v-autocomplete>
					<input type="hidden" v-model="row.departmentId" name="departmentId" />

					<v-text-field
						v-if="row.departmentId === '-1'"
						:disabled="loading"
						v-model="row.departmentOther"
						name="departmentOther"
						:error-messages="errorMsg.departmentOther"
						label="Others department"
					></v-text-field>
				</v-col>
			</v-row>
		</v-card-text>

		<v-card-actions class="pa-4">
			<v-btn color="success" type="submit">
				Join
			</v-btn>
		</v-card-actions>
	</v-card>
</template>

<script>
export default {
	name: 'join-form',
	data() {
		return {
			loading: false,
			row: {
				fullName: '',
				phone: '',
				email: '',
				password: '',
				passowrdConfirm: '',
				nik: '',
				positionName: '',
				companyId: '',
				companyBranchId: '',
				departmentId: '',
				departmentOther: '',
			},
			companyOptions: [],
			departmentOptions: []
		}
	},
	computed: {
		errorMsg() {
			return ufhy.errorMessage;
		},
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
		this.row = {
			fullName: ufhy.rowItem.fullName,
			phone: ufhy.rowItem.phone,
			email: ufhy.rowItem.email,
			// password: ufhy.rowItem.password,
			// passowrdConfirm: ufhy.rowItem.passowrdConfirm,
			nik: ufhy.rowItem.nik,
			positionName: ufhy.rowItem.positionName,
			companyId: ufhy.rowItem.companyId,
			companyBranchId: ufhy.rowItem.companyBranchId,
			departmentId: ufhy.rowItem.departmentId,
			departmentOther: ufhy.rowItem.departmentOther,
		};
		this.fetchOptions();
	},
	methods: {
		fetchOptions() {
			const that = this;
			that.loading = true;
			that.$axios.get('addons/companyoptions')
				.then(response => {
					const { data } = response;
					that.companyOptions = data.companyOptions;
					
					that.departmentOptions = data.departmentOptions;
					that.loading = false;
				})
				.catch(error => {
					console.log(error);
					this.loading = false;
				})
		},
		submitAction() {
			if (this.loading) {
				return false;
			}

			this.loading = true;

			const item = new FormData();
			item.set('fullName', this.row.fullName);
			item.set('phone', this.row.phone);
			item.set('email', this.row.email);
			item.set('password', this.row.password);
			item.set('passowrdConfirm', this.row.passowrdConfirm);
			item.set('nik', this.row.nik);
			item.set('positionName', this.row.positionName);
			item.set('companyId', this.row.companyId);
			item.set('companyBranchId', this.row.companyBranchId);
			item.set('departmentId', this.row.departmentId);
			item.set('departmentOther', this.row.departmentOther);

			this.$axios.post('auth/join', item)
				.then((response) => {
					const { data } = response;
					if (data.success) {
						this.$snackbars.success(data.message);
					} else {
						this.$snackbars.error(data.message);
					}
					this.loading = false;
				})
				.catch((error) => {
					console.log(error);
					const {statusText, data} = error;
					this.$snackbars.error(statusText);

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
