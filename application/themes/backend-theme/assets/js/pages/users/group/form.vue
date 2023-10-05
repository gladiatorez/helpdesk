<template>
  <v-dialog scrollable persistent
    v-model="showModal" 
    transition="slide-y-transition"
    max-width="700px" 
    :fullscreen="$vuetify.breakpoint.xsOnly">
    <v-card :loading="loading" :disabled="loading">
      <v-card-title>{{title}}</v-card-title>
      
      <v-card-text class="pt-4">
        <v-text-field
          v-model="item.name"
          :disabled="loading"
          :error-messages="errorMsg.name"
          :label="$t('users::group:name')"
        ></v-text-field>

        <v-text-field
          v-model="item.descr"
          :disabled="loading"
          :error-messages="errorMsg.descr"
          :label="$t('users::group:descr')"
        ></v-text-field>

        <v-checkbox hide-details
          class="mt-0"
          color="primary"
          :label="$t('users::group:is_default')"
          v-model="item.isDefault"
        ></v-checkbox>

        <v-checkbox hide-details
          class="mt-0"
          color="primary"
          :label="$t('users::group:is_helpdesk')"
          v-model="item.isHelpdesk"
        ></v-checkbox>

        <v-checkbox hide-details
          class="mt-0"
          color="primary"
          :label="$t('users::group:is_admin')"
          v-model="item.isAdmin"
        ></v-checkbox>

        <v-checkbox hide-details
          class="mt-0"
          color="primary"
          :label="$t('users::group:view_cp')"
          v-model="item.viewCp"
        ></v-checkbox>

        <v-checkbox hide-details
          class="mt-0"
          color="primary"
          :label="$t('users::group:send_ticket')"
          v-model="item.sendTicket"
        ></v-checkbox>
      </v-card-text>

      <v-card-actions class="pa-3">
        <v-btn color="primary" small depressed @click="addCloseAction">
          {{$t('btn::save_close')}}
        </v-btn>
        <v-btn v-if="mode !== 'edit'" color="primary" small depressed @click="addAction" class="hidden-sm-and-down">
          {{$t('btn::save')}}
        </v-btn>
        <v-btn color="grey darken-1" small outlined @click="closeAction">
          {{$t('btn::cancel')}}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
  export default {
    name: 'users-group-form',
    data() {
      return {
        showModal: false,
        title: this.$t('users::group:heading_new'),
        loading: false,
        mode: 'add',
        item: {
          id: '',
          name: '',
          descr: '',
          isDefault: false,
          isHelpdesk: false,
          isAdmin: false,
          viewCp: false,
          sendTicket: false,
        },
        errorMsg: {
          id: '',
          name: '',
          descr: '',
          isDefault: '',
          isHelpdesk: '',
          isAdmin: '',
          viewCp: '',
          sendTicket: ''
        }
      }
    },
    created() {
      this.title = this.$t('users::group:heading_new');
      if (this.$route.params.id) {
        this.mode = 'edit';
        this.title = this.$t('users::group:heading_edit');
        this.item.id = this.$route.params.id;
        this.fetchItem();
      }
    },
    mounted() {
      this.showModal = true;
    },
    methods: {
      closeAction() {
        this.showModal = false;
        return this.$router.push({
          name: 'users.group.index',
          params: {
            refresh: true
          }
        });
      },
      clearErrorMsg() {
        this.errorMsg = {
          id: '',
          name: '',
          descr: '',
          isDefault: '',
          isHelpdesk: '',
          isAdmin: '',
          viewCp: '',
          sendTicket: '',
        };
      },
      setEmptyItem() {
        this.item = {
          id: '',
          name: '',
          descr: '',
          isDefault: false,
          isHelpdesk: false,
          isAdmin: false,
          viewCp: false,
          sendTicket: false,
        };
      },
      fetchItem() {
        if (this.loading) {
          return false;
        }

        this.loading = true;
        this.$axios.get('users/group/item', { params: {id: this.item.id} })
          .then(response => {
            const { data } = response;
            const row = data.data;

            this.item = {
              id: row.id,
              name: row.name,
              descr: row.descr,
              isDefault: row.isDefault,
              isHelpdesk: row.isHelpdesk,
              isAdmin: row.isAdmin,
              viewCp: row.viewCp,
              sendTicket: row.sendTicket,
            };

            this.loading = false;
          })
          .catch(error => {
            const {statusText, data} = error;
            if (typeof data.message !== "undefined") {
              this.$coresnackbars.error(data.message);
            } else {
              this.$coresnackbars.error(statusText);
            }
            this.loading = false;
          });
      },
      addAction() {
        this.saveChanges('save')
      },
      addCloseAction() {
        this.saveChanges('saveClose')
      },
      saveChanges(mode) {
        if (this.loading) {
          return false;
        }

        this.clearErrorMsg();
        this.loading = true;

        const item = new FormData();
        item.set('name', this.item.name);
        item.set('descr', this.item.descr);
        item.set('isDefault', this.item.isDefault ? 1 : 0);
        item.set('isHelpdesk', this.item.isHelpdesk ? 1 : 0);
        item.set('isAdmin', this.item.isAdmin ? 1 : 0);
        item.set('viewCp', this.item.viewCp ? 1 : 0);
        item.set('sendTicket', this.item.sendTicket ? 1 : 0);

        let url = 'users/group/create';
        if (this.mode === 'edit') {
          item.set('id', this.item.id);
          url = 'users/group/edit';
        }

        this.$axios.post(url, item)
          .then((response) => {
            const { data } = response;
            this.$coresnackbars.success(data.message);

            if (data.success) {
              if (mode === 'saveClose') {
                this.$router.push({
                  name: 'users.group.index',
                  params: {refresh: true}
                });
              } else {
                this.setEmptyItem();
              }
            }

            this.loading = false;
          })
          .catch((error) => {
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
