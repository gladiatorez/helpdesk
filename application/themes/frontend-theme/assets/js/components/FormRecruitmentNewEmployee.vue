<template>
    <v-alert v-if="hcCategory" color="primary" text :class="{'mt-4': !!errorMsg.categoryId}">
           DOWNLOAD FORM : <br><a href="uploader/master_file/FORM_REQUEST_FULFILLMENT.xlsx"><img src="https://img.icons8.com/?size=1x&id=8Q0JnVzEzZWf&format=png"></img></a>
          </v-alert>
</template>

<script>
export default {
  name: 'form-recruitment-new-employee',
  props: {
    hideText: Boolean
  }
}
</script>