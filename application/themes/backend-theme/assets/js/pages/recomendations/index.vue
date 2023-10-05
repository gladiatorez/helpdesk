<template>
  <v-container fluid class="pa-5">
    <v-card flat>
      <v-card-title>
        <v-btn rounded small
          v-if="$can('create', $route.meta.module)"
          color="primary"
          :to="{name: 'recomendations.create'}">
          <v-icon left>add</v-icon>
          {{$t('btn::create')}}
        </v-btn>
      </v-card-title>

      <v-data-table
        sort-by="createdAt"
        :sort-desc="true"
        :headers="headers"
        :items="items"
        :server-items-length="totalItems"
        :options.sync="tableOptions"
        :loading="loading">
        <template v-slot:item.id="{ item }">
          <core-more-menu 
            :edit-btn="$can('edit', $route.meta.module)"
            :edit-url="{
              name: 'recomendations.edit', params: {id: item.id}
            }"
            :remove-btn="$can('remove', $route.meta.module)"
            @remove-action="removeAction(item)">
            <v-list-item 
              v-if="$can('edit', $route.meta.module)"
              :to="{
                name: 'recomendations.attachPhoto', 
                params: {id: item.id}
              }">
              <v-list-item-icon class="mr-3"></v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Attach photos</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-divider />
          </core-more-menu>
        </template>

        <template v-slot:item.ticketNumber="{ item }">
          <span>#{{ item.ticketNumber }}</span>
        </template>

        <template v-slot:item.letterNo="{ item }">
          <router-link 
            :to="{
              name: 'recomendations.view',
              params: {id: item.id}
            }">
            {{ item.letterNo + item.letterNoSuffix }}
          </router-link>
        </template>

        <template v-slot:item.createdAt="{ item }">
          <span title="DD/MM/YYYY HH:mm">
            {{ $moment(item.createdAt).format('DD/MM/YYYY HH:mm') }}
          </span>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import { fetchDtRows } from '../../utils/helpers';

export default {
  name: 'recomendations-index-page',
  data () {
    return {
      headers: [
        { 
          text: "", 
          value: 'id', 
          sortable: false,
          width: '40px'
        }, 
        { 
          text: this.$t('recomendations::letter_no'), 
          value: 'letterNo', 
        },
        { 
          text: this.$t('recomendations::letter_subject'), 
          value: 'letterSubject', 
        },
        { 
          text: this.$t('recomendations::ticket'), 
          value: 'ticketNumber', 
        },
        { 
          text: this.$t('recomendations::maker'), 
          value: 'makerFullname', 
        },
        { 
          text: this.$t('lb::updated_at'), 
          value: 'createdAt', 
          width: '160px'
        },
      ],
      tableOptions: {},
      items: [],
      totalItems: 0,
      loading: false,
      searchText: '',
    }
  },
  watch: {
    $route: function (route) {
      if (route.params.refresh) {
        this.refreshAction();
      }
    },
    tableOptions () {
      this.refreshAction();
    }
  },
  created() {
    this.$root.$on('page-header:refresh-action', this.refreshAction);
    this.$root.$on('page-header:search-action', this.searchAction);
    this.$root.$on('page-header:search-cancel-action', this.searchClearAction);
  },
  destroyed() {
    this.$root.$off('page-header:refresh-action', this.refreshAction);
    this.$root.$off('page-header:search-action', this.searchAction);
    this.$root.$off('page-header:search-cancel-action', this.searchClearAction);
  },
  methods: {
    searchAction(payload) {
      this.searchText = payload;
      this.refreshAction();
    },
    searchClearAction() {
      this.searchText = '';
      this.refreshAction();
    },
    refreshAction() {
      this.loading = true;
      fetchDtRows('recomendations', this.tableOptions, this.searchText).then(data => {
        this.items = data.items
        this.totalItems = data.total
      }).catch(error => {
          this.loading = false;
      }).then(() => {
          this.loading = false;
      })
    },
    removeAction(item) {
      if (this.loading) {
        return false;
      }

      const confirmText = this.$sprintf(this.$t('confirm:remove_text'), item.letterNo + item.letterNoSuffix);
      if (confirm(confirmText)) {
        this.loading = true;
        const that = this;
        this.$axios.get('recomendations/remove/', {
          params: {id: item.id}
        })
          .then(response => {
            const {data} = response;

            that.$coresnackbars.success(data.message);

            if (data.success) {
              that.refreshAction();
            }
          })
          .catch(function (error) {
            const { statusText, status } = error;
            that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
          })
          .then(() => {
            that.loading = false;
          });
      }
    }
  }
}
</script>