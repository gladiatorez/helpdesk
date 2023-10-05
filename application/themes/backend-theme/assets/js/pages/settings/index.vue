<template>
  <div class="container">
    <app-page-header title="Settings" breadcrumbs></app-page-header>
    
  </div>
</template>

<script>
const AppPageHeader = () => import('../../components/AppPageHeader.vue');
export default {
  name: 'settings-page',
  components: {
    AppPageHeader
  },
  created() {
    this.$axios.get('settings');
  }
}
</script>