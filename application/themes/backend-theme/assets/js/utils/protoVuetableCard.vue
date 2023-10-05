<template>
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="p-3 d-md-flex">
        <div class="mb-3 mb-md-0">
          <b-button :to="{name: 'users.group.create'}" size="sm" variant="primary" class="btn-rounded btn-icon">
            <i class="ms-Icon--plus2 icon icon-left"></i>
            {{$t('btn::create')}}
          </b-button>
          <b-button size="sm" variant="light" class="btn-icon-only" @click="refreshAction">
            <i class="ms-Icon--refresh2 icon"></i>
          </b-button>
        </div>

        <div class="ml-auto">
          <div class="input-group input-group-icon input-group-sm">
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="ms-Icon--search2"></i>
              </span>
            </div>
            <input type="text" class="form-control form-control-rounded" placeholder="search">
          </div>
        </div>
      </div>

      <app-table-wrapp
        :pagination-data="tableOptions.paginationData"
        :loading="tableOptions.loading"
        @onChangePage="onChangePage">
        <vuetable ref="vuetable"
          :css="tableOptions.cssTable"
          :fields="tableOptions.fields"
          :api-url="tableOptions.url"
          :per-page="tableOptions.perPage"
          :sort-order="tableOptions.sortOrder"
          pagination-path=""
          :query-params="tableQueryParams"
          @vuetable:pagination-data="onPaginationData"
          @vuetable:loading="tableOptions.loading = true"
          @vuetable:loaded="tableOptions.loading = false"
        ></vuetable>
      </app-table-wrapp>
    </div>

    <router-view></router-view>
  </div>
</template>

<script>
const Vuetable = () => import('vuetable-2/src/components/Vuetable.vue');
import AppTableWrapp from '../../../components/AppTableWrapp.vue';
import AppPageHeader from '../../../components/AppPageHeader.vue';
import { apiUrl, cssTable } from '../../../utils/helpers.js';

export default {
  name: 'users-group-page',
  components: {
    Vuetable, AppTableWrapp, AppPageHeader
  },
  data() {
    return {
      tableOptions: {
        fields: [
          { name: 'name', title: 'Name', sortField: 'name' },
          { name: 'descr', title: 'Description', sortField: 'descr' },
          { name: 'isDefault-slot', title: 'Default', sortField: 'isDefault', width: '90px' },
          { name: 'isAdmin', title: 'Admin', sortField: 'isAdmin', width: '90px' },
        ],
        sortOrder: [{
          field: 'name',
          direction: 'asc'
        }],
        perPage: 3,
        url: apiUrl('users/group'),
        cssTable: cssTable,
        paginationData: {},
        loading: false,
      },
    }
  },
  methods: {
    tableQueryParams(sortOrder, currentPage, perPage) {
      return {
        sort: sortOrder[0].sortField,
        order: sortOrder[0].direction,
        currentPage: currentPage,
        perPage: perPage
      }
    },
    onPaginationData(paginationData) {
      this.tableOptions.paginationData = paginationData;
    },
    onChangePage(page) {
      this.$refs.vuetable.changePage(page)
    },
    refreshAction() {
      this.$refs.vuetable.refresh();
    }
  }
}
</script>