<template>
  <v-container fluid class="pa-5">
    <v-card flat>
      <v-card-title>
        <v-btn rounded small
          v-if="$can('create', $route.meta.module)"
          color="primary"
          :to="{name: 'users.group.create'}">
          <v-icon left>add</v-icon>
          {{$t('btn::create')}}
        </v-btn>
      </v-card-title>

      <v-data-table
        sort-by="name"
        :sort-desc="false"
        :headers="headers"
        :items="items"
        :server-items-length="totalItems"
        :options.sync="tableOptions"
        :loading="loading">
        <template v-slot:item.id="{ item }">
          <core-more-menu 
            :edit-btn="$can('edit', $route.meta.module)"
            :edit-url="{
              name: 'users.group.edit', params: {id: item.id}
            }"
            :remove-btn="$can('remove', $route.meta.module)"
            @remove-action="removeAction(item)">
            <!-- <v-list-item
              v-if="$can('edit', $route.meta.module)"
              @click="setAsDefaultAction">
              <v-list-item-icon class="mr-3">
                <v-icon>flag</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{$t('users::group:set_as_default')}}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item
              v-if="$can('edit', $route.meta.module)"
              @click="setAsAdminAction">
              <v-list-item-icon class="mr-3">
                <v-icon>flag</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{$t('users::group:set_as_admin')}}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-divider /> -->
            <v-list-item
              v-if="$can('change_permission', $route.meta.module)"
              :to="{
                name: 'users.group.permission',
                params: {id: item.id}
              }">
              <v-list-item-icon class="mr-3">
                <v-icon>lock_open</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{$t('users::group:permissions')}}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-divider />
          </core-more-menu>
        </template>

        <template v-slot:item.isDefault="{ item }">
          <v-checkbox 
            class="mt-0"
            v-model="item.isDefault" 
            color="primary" 
            :ripple="false" 
            hide-details readonly 
          />
        </template>

        <template v-slot:item.viewCp="{ item }">
          <v-checkbox 
            class="mt-0"
            v-model="item.viewCp" 
            color="primary" 
            :ripple="false" 
            hide-details readonly 
          />
        </template>

        <template v-slot:item.isHelpdesk="{ item }">
          <v-checkbox 
            class="mt-0"
            v-model="item.isHelpdesk" 
            color="primary" 
            :ripple="false" 
            hide-details readonly 
          />
        </template>

        <template v-slot:item.isAdmin="{ item }">
          <v-checkbox 
            class="mt-0"
            v-model="item.isAdmin" 
            color="primary" 
            :ripple="false" 
            hide-details readonly 
          />
        </template>

        <template v-slot:item.sendTicket="{ item }">
          <v-checkbox 
            class="mt-0"
            v-model="item.sendTicket" 
            color="primary" 
            :ripple="false" 
            hide-details readonly 
          />
        </template>

        <template v-slot:item.updatedAt="{ item }">
          <span title="DD/MM/YYYY HH:mm">
            {{ $moment(item.updaatedAt).format('DD/MM/YYYY HH:mm') }}
          </span>
        </template>
      </v-data-table>
    </v-card>

    <router-view></router-view>
  </v-container>
</template>

<script>
import { fetchDtRows } from '../../../utils/helpers';
export default {
  name: 'users-group-page',
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
          text: this.$t('users::group:name'), 
          value: 'name', 
        },
        { 
          text: this.$t('users::group:is_default'), 
          value: 'isDefault', 
          width: '90px'
        },
        { 
          text: this.$t('users::group:view_cp'), 
          value: 'viewCp', 
          width: '90px'
        },
        { 
          text: this.$t('users::group:is_helpdesk'), 
          value: 'isHelpdesk', 
          width: '90px'
        },
        { 
          text: this.$t('users::group:is_admin'), 
          value: 'isAdmin', 
          width: '90px'
        },
        { 
          text: this.$t('users::group:send_ticket'), 
          value: 'sendTicket', 
          width: '90px'
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
    tableOptions() {
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
      fetchDtRows('users/group', this.tableOptions, this.searchText).then(data => {
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

      const confirmText = this.$sprintf(this.$t('confirm:remove_text'), item.name);
      if (confirm(confirmText)) {
        this.loading = true;
        const that = this;
        this.$axios.get('users/group/remove/', {
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
    },
    setAsDefaultAction(item) {

    },
    setAsAdminAction(item) {

    }
  }
}
</script>
