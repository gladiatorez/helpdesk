<template>
	<div class="app-logo">
		<div class="branch-text">
			CICT
		</div>
		<div class="appname-text">
			{{appName}}
		</div>
	</div>
</template>

<script>
export default {
	name: 'app-logo',
	props: {
		appName: String,
	}
}
</script>
