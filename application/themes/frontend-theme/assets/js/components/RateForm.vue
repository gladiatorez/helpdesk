<template>
	<div>
		<div class="body-2">{{title}}</div>
		<template v-if="!isSend">
			<v-radio-group v-model="rate" @change="submitAction" :loading="loading" class="mt-0" row hide-details>
				<v-radio light :label="labelYes" value="Y"></v-radio>
				<v-radio light :label="labelNo" value="N"></v-radio>
			</v-radio-group>
		</template>
		<template v-else>
			<div class="info--text">Thank you for your feedback.</div>
		</template>
	</div>
</template>

<script>
export default {
	name: 'rate-form',
	props: {
		title: {
			type: String,
			required: true
		},
		paramId: {
			type: String,
			required: true
		},
		labelYes: {
			type: String,
			default: 'Yes'
		},
		labelNo: {
			type: String,
			default: 'No'
		}
	},
	data() {
		return {
			rate: null,
			loading: true,
			isSend: false,
		}
	},
	methods: {
		submitAction() {
			const item = new FormData();
			item.set('id', this.paramId);
			item.set('option', this.rate ? this.rate : '');
			
			this.$axios.post('faq/rate', item)
			.then((response) => {
				const { data } = response;
				if (data.success) {
					this.isSend = true
				} else {
					this.isSend = false
				}
			})
			.catch((error) => {
				console.error(error);
			});
		}
	}
}
</script>
