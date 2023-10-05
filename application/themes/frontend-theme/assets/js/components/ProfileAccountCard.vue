<template>
	<v-card outlined class="mb-4" :loading="loading" :disabled="loading">
		<v-card-title>
			<span>Account info</span>
			<v-spacer></v-spacer>
			<v-btn icon class="ma-0" @click="editAction">
				<v-icon>edit</v-icon>
			</v-btn>
		</v-card-title>
		<v-list>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Email</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.email}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Username</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.username}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list-item>
				<v-list-item-icon style="width: 150px">
					<span class="body-2 grey--text text--darken-1">Last login</span>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{item.lastLogin}}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
		</v-list>

		<v-dialog scrollable persistent 
			v-model="showModal" 
			origin="top center" 
			max-width="800px">
			<v-card>
				<v-card-title>Edit account info</v-card-title>
				
				<v-card-text>
					<v-row>
						<v-col cols="12" md="6">
							<v-text-field
								v-model="row.email"
								:disabled="loading"
								:error-messages="errorMsg.email"
								label="Email"
							></v-text-field>
						</v-col>
						<v-col cols="12" md="6">
							<v-text-field
								v-model="row.username"
								:disabled="loading"
								:error-messages="errorMsg.username"
								label="Username"
							></v-text-field>
						</v-col>
						<v-col cols="12" md="3">
							<v-checkbox hide-details
								color="primary"
								v-model="row.passwordChange"
								label="Change password"
							></v-checkbox>
						</v-col>
						<v-col cols="12" md="9" v-if="row.passwordChange">
							<v-text-field
								type="password"
								v-model="row.oldPassword"
								:disabled="loading"
								:error-messages="errorMsg.oldPassword"
								label="Current password"
								placeholder="Enter current password"
							></v-text-field>

							<v-text-field
								type="password"
								v-model="row.newPassword"
								:disabled="loading"
								:error-messages="errorMsg.newPassword"
								label="New password"
								placeholder="Enter new password"
							></v-text-field>

							<v-text-field
								type="password"
								v-model="row.newPasswordConfirm"
								:disabled="loading"
								:error-messages="errorMsg.newPasswordConfirm"
								label="Confirm new password"
								placeholder="Enter new password again"
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
				email: '',
				username: '',
				oldPassword: '',
				newPassword: '',
				newPasswordConfirm: '',
				passwordChange: false,
			},
			errorMsg: {
				email: '',
				username: '',
				oldPassword: '',
				newPassword: '',
				newPasswordConfirm: '',
			}
		}
	},
	methods: {
		editAction() {
			this.row = {
				email: this.item.email,
				username: this.item.username,
				oldPassword: '',
				newPassword: '',
				newPasswordConfirm: '',
				passwordChange: false
			};
			this.showModal = true;
		},
		clearErrorMsg() {
			this.errorMsg = {
				email: '',
				username: '',
				oldPassword: '',
				newPassword: '',
				newPasswordConfirm: '',
			}
		},
		saveChangeAction() {
			if (this.loading) {
				return false;
			}

			this.clearErrorMsg();
			this.loading = true;

			const item = new FormData();
			item.set('email', this.row.email);
			item.set('username', this.row.username);
			item.set('changePassword', this.row.passwordChange ? '1' : '0');
			item.set('oldPassword', this.row.oldPassword);
			item.set('newPassword', this.row.newPassword);
			item.set('newPasswordConfirm', this.row.newPasswordConfirm);

			this.$axios.post('profile/personalinfo/saveaccount', item)
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
