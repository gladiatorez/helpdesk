<template>
	<v-row>
		<v-col cols="12" lg="9">
			<v-container fluid>
				<profile-general-card 
					:item="item"
					@save-success="fethInformation"
				></profile-general-card>
				<profile-company-card 
					:item="item"
					@save-success="fethInformation"
				></profile-company-card>
				<profile-account-card 
					:item="item"
					@save-success="fethInformation"
				></profile-account-card>
			</v-container>
		</v-col>
	</v-row>
</template>

<script>
import ProfileGeneralCard from '../components/ProfileGeneralCard.vue';
import ProfileCompanyCard from '../components/ProfileCompanyCard.vue';
import ProfileAccountCard from '../components/ProfileAccountCard.vue';
export default {
	name: 'account-personal-info-page',
	components: {
		ProfileGeneralCard, ProfileCompanyCard, ProfileAccountCard
	},
	data() {
		return {
			loading: false,
			item: {}
		}
	},
	computed: {
		generalInfo() {
			const rows = [
				{label: 'Full name', value: this.item.fullName},
				{label: 'Phone', value: this.item.phone},
			];

			return rows;
		},
		companyInfo() {
			const rows = [
				{label: 'NIK', value: this.item.nik},
				{label: 'Position', value: this.item.position},
				{label: 'Company', value: this.item.companyName},
				{label: 'Department', value: this.item.departmentName},
			];

			return rows;
		},
		accountInfo() {
			const rows = [
				{label: 'Email', value: this.item.email},
				{label: 'Username', value: this.item.username},
				{label: 'Password', value: '••••••••'},
				{label: 'Last login', value: this.item.lastLogin},
			];

			return rows;
		}
	},
	created() {
		this.fethInformation();
	},
	methods: {
		fethInformation() {
			this.loading = true;
			this.$axios.get('profile/personalinfo')
				.then(response => {
					const { data } = response;
					if (data.success) {
						this.item = data.row;
					}
					this.loading = false;
				})
				.catch(error => {
					console.log(error);
					this.loading = false;
				})
		}
	}
}
</script>
