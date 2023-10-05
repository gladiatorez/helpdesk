<template>
  <v-container fluid fill-height class="justify-center">
    <v-card color="error" flat dark>
        <v-card-text class="text-xs-center">
          <h1 class="display-1"><i class="ms-Icon--protectRestrict"></i></h1>
          <h2 class="display-3 font-weight-light">ERROR !!</h2>
          <h4 class="font-weight-normal">Anda tidak memiliki izin untuk bisa melihat halaman tersebut.</h4>
        </v-card-text>
      </v-card>
  </v-container>
</template>

<script>
export default {
  name: 'error-permissions',
}
</script>