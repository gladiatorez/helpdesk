<template>
  <v-dialog scrollable persistent lazy 
    v-model="showModal" 
    origin="top center" 
    max-width="700px" 
    :fullscreen="$vuetify.breakpoint.xsOnly">
    <v-card class="card-dialog">
      <v-card-title primary-title>{{title}}</v-card-title>
      
      <v-card-text class="grey lighten-5">
        <v-form :value="false">
          <v-container fluid grid-list-md class="grey lighten-5">
            <v-layout wrap>
              <v-flex xs12>
                <v-text-field required box
                  background-color="white"
                  v-model="item.name"
                  :disabled="loading"
                  :error-messages="errorMsg.name"
                  :label="$t('references::priority:name')"
                ></v-text-field>
              </v-flex>

              <v-flex xs12>
                <v-text-field required box
                  background-color="white"
                  type="number"
                  v-model.number="item.ord"
                  :disabled="loading"
                  :error-messages="errorMsg.ord"
                  :label="$t('references::priority:order')"
                ></v-text-field>
              </v-flex>
            </v-layout>

          </v-container>
        </v-form>
      </v-card-text>

      <v-card-actions class="pa-3">
        <v-btn color="primary" small depressed @click="addCloseAction" :loading="loading">
          {{$t('btn::save_close')}}
        </v-btn>
        <v-btn v-if="mode !== 'edit'" color="primary" small depressed @click="addAction" class="hidden-sm-and-down" :loading="loading">
          {{$t('btn::save')}}
        </v-btn>
        <v-btn color="grey darken-1" small outline @click="closeAction">
          {{$t('btn::cancel')}}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
  export default {
    name: 'references-priority-form',
    data() {
      return {
        showModal: false,
        title: this.$t('references::priority:heading_new'),
        loading: false,
        mode: 'add',
        item: {
          id: '',
          name: '',
          ord: '',
        },
        errorMsg: {
          id: '',
          name: '',
          ord: '',
        }
      }
    },
    created() {
      this.title = this.$t('references::priority:heading_new');
      if (this.$route.params.id) {
        this.mode = 'edit';
        this.title = this.$t('references::priority:heading_edit');
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
          name: 'references.priority.index',
          params: {
            refresh: true
          }
        });
      },
      clearErrorMsg() {
        this.errorMsg = {
          id: '',
          name: '',
          ord: '',
        };
      },
      setEmptyItem() {
        this.item = {
          id: '',
          name: '',
          ord: '',
        };
      },
      fetchItem() {
        if (this.loading) {
          return false;
        }

        this.loading = true;
        this.$axios.get('references/priority/item', { params: {id: this.item.id} })
          .then(response => {
            const { data } = response;
            const row = data.data;

            this.item = {
              id: row.id,
              name: row.name,
              ord: row.ord,
            };

            this.loading = false;
          })
          .catch(error => {
            const {statusText, data} = error;
            if (typeof data.message !== "undefined") {
              this.$snackbars.error(data.message);
            } else {
              this.$snackbars.error(statusText);
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
        item.set('ord', this.item.ord);

        let url = 'references/priority/create';
        if (this.mode === 'edit') {
          item.set('id', this.item.id);
          url = 'references/priority/edit';
        }

        this.$axios.post(url, item)
          .then((response) => {
            const { data } = response;
            this.$snackbars.success(data.message);

            if (data.success) {
              if (mode === 'saveClose') {
                this.$router.push({
                  name: 'references.priority.index',
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
            this.$snackbars.error(statusText);

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
