<template>
	<div>
		<v-btn icon depressed 
			class="btn-navbar" 
			:class="{'success--text': loginActive}"
			:href="hrefLogin">
			<v-icon>ms-Icon ms-Icon--Contact</v-icon>
		</v-btn>

		<template v-if="!loginOnly">
			<v-btn rounded depressed outlined
				class="hidden-sm-and-down" 
				:href="hrefJoin"
				color="success">
				Join
			</v-btn>
		</template>
	</div>
</template>

<script>
export default {
	name: 'login-button',
	props: {
		hrefLogin: {
			type: String,
			required: true
		},
		hrefJoin: {
			type: String,
			required: true
		},
		loginOnly: {
			type: Boolean,
			default: false,
		},
		loginActive: {
			type: Boolean,
			default: false,
		}
	}
}
</script>
