<template>
  <v-container fluid class="pa-5">
    <v-card flat>
      <v-card-title>
        <v-btn rounded small
          v-if="$can('create', $route.meta.module)"
          color="primary"
          :to="{name: 'faq.create'}">
          <v-icon left>add</v-icon>
          {{$t('btn::create')}}
        </v-btn>
      </v-card-title>

      <v-data-table
        sort-by="isHeadline"
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
              name: 'faq.edit', params: {id: item.id}
            }"
            :remove-btn="$can('remove', $route.meta.module)"
            @remove-action="removeAction(item)" 
          />
        </template>

        <template v-slot:item.rated="{ item }">
          <div class="d-flex align-items-center">
            <div class="success--text">
              <v-icon dense color="success">thumb_up</v-icon>
              {{item.rated.Y}}
            </div>
            <div class="error--text ml-3">
              <v-icon dense color="error">thumb_down</v-icon>
              {{item.rated.N}}
            </div>
          </div>
        </template>

        <template v-slot:item.isHeadline="{ item }">
          <v-checkbox v-model="item.isHeadline" color="primary" class="mt-0" readonly hide-details></v-checkbox>
        </template>

        <template v-slot:item.active="{ item }">
          <core-status-value v-model="item.active" />
        </template>

        <template v-slot:item.updatedAt="{ item }">
          <span title="DD/MM/YYYY HH:mm">
            {{ $moment(item.updaatedAt).format('DD/MM/YYYY HH:mm') }}
          </span>
        </template>

        <template slot="items" slot-scope="props">
          <td>
            <v-menu>
                <v-btn icon small slot="activator" color="primary--text darken-1">
                  <i class="ms-Icon--ellipsis" aria-hidden="true"></i>
                </v-btn>
                <v-list dense>
                  <v-list-tile 
                    :to="{
                      name: 'faq.edit',
                      params: {id: props.item.id}
                    }">
                    <v-list-tile-action>
                      <v-icon>ms-Icon--editBox</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-title>Edit</v-list-tile-title>
                  </v-list-tile>
                  <v-list-tile class="red--text" @click="removeAction(props.item)">
                    <v-list-tile-action>
                      <v-icon class="red--text">ms-Icon--trash</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-title>Remove</v-list-tile-title>
                  </v-list-tile>
                </v-list>
            </v-menu>
          </td>
          <td><span class="text-truncate">{{ props.item.title }}</span></td>
          <td>
            <div class="d-flex">
              <div class="success--text">
                <i class="ms-Icon--thumbUp"></i> {{props.item.rated.Y}}
              </div>
              <div class="error--text ml-2">
                <i class="ms-Icon--thumbDown"></i> {{props.item.rated.N}}
              </div>
            </div>
          </td>
          <td>
            <v-checkbox v-model="props.item.isHeadline" color="primary" hide-details readonly></v-checkbox>
          </td>
          <td>
            <core-status-value v-model="props.item.active" />
          </td>
          <td><span class="text-truncate">{{ props.item.updatedAt }}</span></td>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import { fetchDtRows } from '../../utils/helpers';

export default {
  name: 'faq-index-page',
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
          text: this.$t('faq::title'), 
          value: 'title', 
        },
        { 
          text: this.$t('faq::rate'), 
          value: 'rated', 
          sortable: false,
        },
        { 
          text: this.$t('faq::is_headline'), 
          value: 'isHeadline', 
        },
        { 
          text: this.$t('lb::status'), 
          value: 'active', 
          width: '120px'
        },
        { 
          text: this.$t('lb::updated_at'), 
          value: 'updatedAt', 
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
      fetchDtRows('faq', this.tableOptions, this.searchText).then(data => {
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

      const confirmText = this.$sprintf(this.$t('confirm:remove_text'), item.title);
      if (confirm(confirmText)) {
        this.loading = true;
        const that = this;
        this.$axios.get('faq/remove/', {
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