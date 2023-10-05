<template>
  <v-card>
    <v-card-title>
      <span class="font-weight-bold body-2">{{ getTitle }}</span>
      <v-spacer />
      <v-text-field 
        dense hide-details clearable
        prepend-inner-icon="search"
        placeholder="Search name"
        v-model="searchText"
      />
    </v-card-title>
    <v-data-table fixed-header 
      class="v-card__text pa-0 d-flex flex-column"
      style="overflow-y:hidden"
      sort-by="cnt"
      :sort-desc="true"
      :headers="headers"
      :items="items"
      :loading="loading"
      :search="searchText"
      :items-per-page.sync="itemPerPage"
      :footer-props="{
        itemsPerPageOptions: [10,25,50]
      }">
      <template v-slot:item.full_name="{item}">
        <span class="grey--text text--darken-3">{{ item.full_name }}</span>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
export default {
  name: 'ticket-staff-table',
  data() {
    return {
      title: 'Staff',
      year: '',
      month: '',
      items: [],
      headers: [
        {
          value: 'full_name', 
          text: 'Name', 
          sortable: true
        },
        {
          value: 'cnt', 
          text: 'Count', 
          sortable: true, 
          align: 'end'
        }
      ],
      loading: false,
      searchText: '',
      itemPerPage: 25,
    }
  },
  computed: {
    getTitle() {
      return this.title + ' - ' + this.month + ' ' + this.year
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      const that = this;
      this.loading = true;
      this.$axios.get('dashboard/ticket_staff').then(({ data }) => {
        this.year = data.year;
        this.month = data.month;
        this.items = data.rows;
      }).catch(error => {
        console.log(error);
      }).then(() => {
        this.loading = false;
      })
    },
  }
}
</script>
