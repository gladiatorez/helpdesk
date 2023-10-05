<template>
    <v-container fluid class="pl-5 pr-5 pb-5">
        <v-row row wrap>
            <v-col cols="12" lg="9" md="7">
                <ticket-detail-card 
                    class="mb-5"
                    :subject="item.subject"
                    :flag="item.flag"
                    :no="item.number"
                    :informer-full-name="item.informer_full_name"
                    :informer-email="item.informer_email"
                    :company-name="item.company_name"
                    :company-branch-name="item.company_branch_name"
                    :created-at="item.created_at"
                    :description="item.description"
                    :attachment="item.attachment"
                />

                <v-card outlined>
                    <v-tabs centered>
                        <v-tab>Manage</v-tab>
                        <v-tab>Sender</v-tab>
                        <v-tab>Note</v-tab>

                        <v-tab-item>
                            <v-card flat>
                                <v-card-text class="pa-3">
                                    <v-row align="center" justify="start">
                                        <v-col cols="12" md="3" class="text-md-right">
                                            Flag
                                        </v-col>
                                        <v-col cols="12" md="9" class="black--text">
                                            {{item.flag}}
                                        </v-col>
                                    </v-row>
                                    <v-divider />
                                    <v-row align="center" justify="start">
                                        <v-col cols="12" md="3" class="text-md-right">
                                            Staff
                                        </v-col>
                                        <v-col cols="12" md="9" class="black--text">
                                            <template v-if="item.staffs">
                                                <template v-for="(staff, index) in item.staffs">
                                                    <v-chip class="mr-1"
                                                        :key="index" 
                                                        :color="parseInt(staff.is_claimed) > 0 ? 'primary' : 'warning'"
                                                        primary dark small>
                                                        {{staff.full_name}}
                                                    </v-chip>
                                                </template>
                                            </template>
                                        </v-col>
                                    </v-row>
                                    <v-divider />
                                    <v-row align="center" justify="start">
                                        <v-col cols="12" md="3" class="text-md-right">
                                            Category
                                        </v-col>
                                        <v-col cols="12" md="9" class="black--text">
                                            {{item.category_name}} / {{item.category_sub_name}}
                                        </v-col>
                                    </v-row>
                                    <v-divider />
                                    <v-row align="center" justify="start">
                                        <v-col cols="12" md="3" class="text-md-right">
                                            Priority
                                        </v-col>
                                        <v-col cols="12" md="9" class="black--text">
                                            {{item.priority_name}}
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-tab-item>

                        <v-tab-item>
                            <ticket-detail-sender 
                                :full-name="item.informer_full_name"
                                :nik="item.informer_nik"
                                :email="item.informer_email"
                                :phone="item.informer_phone"
                                :company="item.company_name"
                                :location="item.company_branch_name"
                                :department="item.department_name"
                            />
                        </v-tab-item>

                        <v-tab-item>
                            <ticket-detail-note 
                                :notes="item.notes"
                            />
                        </v-tab-item>
                    </v-tabs>
                </v-card>
            </v-col>

            <v-col cols="12" lg="3" md="5">
                <ticket-detail-timeline 
                    :logs="item.logs" 
                />
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import { TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline } from '../../../components/Ticket';
export default {
  name: 'queues-personil-detail-page',
  components: {
    TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline
  },
  data() {
    return {
      loading: false,
      item: {
        id: '',
      },
      row: {
        categoryId: '',
        categorySubId: '',
        estimate: 0,
        priorityId: '',
        keywords: [],
        staff: [],
        note: '',
      },
      errorMsg: {
        id: '',
        categoryId: '',
        categorySubId: '',
        estimate: '',
        priorityId: '',
        keywords: '',
        'staff[]': '',
        note: '',
      },
      categoryOptions: [],
      categorySubOptions: [],
      priorityOptions: [],
      keywordSearch: null,
      keywordOptions: [],
      staffOptions: []
    }
  },
  created() {
    this.$root.$on('page-header:back-action', this.closeAction);

    if (this.$route.params.id) {
      this.item.id = this.$route.params.id;
      this.fetchItem();
    }
  },
  destroyed() {
    this.$root.$off('page-header:back-action', this.closeAction);
  },
  methods: {
    closeAction() {
      return this.$router.push({
        name: 'queues.personil.index',
        params: {
          refresh: true
        }
      });
    },
    fetchItem() {
      if (this.loading) {
        return false;
      }

      this.loading = true;
      this.$axios.get('queues/personil/item', { params: {id: this.item.id} })
        .then(response => {
          const { data } = response;
          const row = data.data;

          this.item = row;

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
  }
}
</script>
