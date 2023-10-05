<template>
  <v-dialog scrollable persistent fullscreen 
    transition="dialog-bottom-transition"
    v-model="showModal"
    max-width="700px">
    <v-card :loading="loading" :disabled="loading">
      <v-toolbar fixed 
        max-height="64"
        class="elevation-base"
        style="z-index:1">
        <v-btn icon @click="closeAction">
          <v-icon>close</v-icon>
        </v-btn>
        <v-toolbar-title>{{title}}</v-toolbar-title>
      </v-toolbar>

      <v-card-text style="background-color:#f3f3f3">
        <v-container>
          <form method="post" v-on:submit.prevent="saveChanges($event)" ref="formPermissions">
            <template v-if="isAdmin">
              <v-alert :value="true" type="warning">
                This is a warning alert.
              </v-alert>
            </template>
            <template v-else>
              <input type="hidden" name="id" v-model="groupId" />
              <template v-for="(moduleRole, moduleRoleIndex) in moduleRoles">
                <permission-module
                  :key="moduleRoleIndex"
                  :module-role="moduleRole"
                  :data-roles="dataRoles"
                ></permission-module>
              </template>

              <v-btn v-if="!isAdmin" color="primary" type="submit">
                <v-icon left>save</v-icon>
                Save
              </v-btn>
            </template>
          </form>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
  import PermissionModule from './includes/Module.vue';

  export default {
    name: 'users-group-form',
    components: {
      PermissionModule
    },
    data() {
      return {
        showModal: false,
        title: "Hak akses",
        loading: false,
        groupId: '',
        isAdmin: false,
        isAdminMsg: '',
        moduleRoles: [],
        dataRoles: [],
      }
    },
    created() {
      if (this.$route.params.id) {
        this.groupId = this.$route.params.id;
        this.fetchItem();
      }
    },
    mounted() {
      this.showModal = true;
    },
    methods: {
      closeAction() {
        this.showModal = false;
        return this.$router.push({name: 'users.group.index'});
      },
      fetchItem() {
        if (this.loading) {
          return false;
        }

        this.loading = true;
        this.$axios.get('users/group/permissions', { params: {id: this.groupId} })
          .then(response => {
            const { data } = response;
            if (!data.success) {
              this.isAdmin = data.isAdmin ? data.isAdmin : false;
              // this.$toasted.error(data.message);
              if (data.isAdmin) {
                this.isAdminMsg = data.message;
              }

              return false;
            }

            this.title = this.title + ': Kelompok :: ' + data.groupName;
            this.moduleRoles = data.modules;

            data.editPermissions.dashboard = ['read'];

            this.dataRoles = data.editPermissions ? data.editPermissions : {};
          })
          .catch(error => {
            const {statusText, data} = error;
            if (typeof data.message !== "undefined") {
              this.$toasted.error(data.message, {
                fullWidth: true,
                fitToScreen: true,
              });
            } else {
              this.$toasted.error(statusText, {
                fullWidth: true,
                fitToScreen: true,
              });
            }
          })
          .then(() => {
            this.loading = false;
          });
      },
      submitAction(evt) {
        evt.preventDefault();
        this.$refs.formPermissions.submit();
      },
      saveChanges(event) {

        if (this.loading) {
          return false;
        }

        const formData = new FormData(event.target);
        this.loading = true;
        this.$axios({
          url: 'users/group/permissions',
          method: 'post',
          params: { id: this.groupId },
          data: formData
        }).then(response => {
          const { data } = response;
          this.$coresnackbars.success(data.message);
          if (data.success) {
            this.$router.push({
              name: 'users.group.index',
              params: {refresh: true}
            });
          }
        }).catch((error) => {
          const {statusText} = error;
          this.$coresnackbars.error(statusText);
        }).then(() => {
          this.loading = false;
        });

      },
    }
  }
</script>