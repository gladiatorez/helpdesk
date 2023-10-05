<template>
	 <img alt="" src="https://helpdesk-dev.kallagroup.co.id/assets/img/logo_kg.png" style="width: 100px; margin-top: 10px;">
</template>

<script>
export default {
	name: 'CoreLogo',
	props: {
		outlined: Boolean,
		appTitle: String,
	},
	computed: {
		appName() {
			return this.$store.state.layouts.siteName
		}
	}
}
</script>

<style lang="scss" scoped>
.app-logo {
	width: 100%;
	display: flex;
	.branch-text, .appname-text {
		border-radius: 28px;
		padding: 0 12px;
		height: 32px;
		display: inline-flex;
		align-items: center;
	}
	.branch-text {
		background: #34bfa3;
		color: #fff;
		font-weight: bold;
		font-size: 14px;
		border-top-right-radius: 0;
		border-bottom-right-radius: 0;
	}
	.appname-text {
		background: #ffffff;
		color: #000000;
		flex-grow: 1!important;
		font-weight: 900;
		font-size: 14px;
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;

		&--outlined {
      background: rgba(52, 191, 163, .2);
			//border: 1px solid #34bfa3
		}
	}
}
</style>
