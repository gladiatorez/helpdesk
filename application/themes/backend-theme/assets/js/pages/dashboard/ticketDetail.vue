<template>
    <v-dialog 
        persistent scrollable fullscreen
        transition="slide-y-transition"
        v-model="show">
        <v-card tile
            :loading="loading"
            :disabled="loading">
            <v-app-bar dense tile
                class="backend-navbar"
                height="50"
                max-height="50"
                color="white">
                <v-container class="d-flex align-center">
                    <core-logo outlined
                        v-if="!$vuetify.breakpoint.mdAndDown"
                        class="mr-6"
                        style="width: 135px"
                    />
                    <v-spacer />
                    <v-btn exact small
                        color="error"
                        :to="{name: 'dashboard.index'}">
                        <v-icon left>close</v-icon>
                        <span>Close</span>
                    </v-btn>
                </v-container>
            </v-app-bar>
            <v-card-text class="pa-0" style="background:#f2f3f8">
                <v-container class="pt-5">
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

                            <v-card class="mb-5">
                                <v-tabs centered>
                                    <v-tab>Information</v-tab>
                                    <v-tab>Sender</v-tab>
                                    <v-tab>Note</v-tab>

                                    <v-tab-item>
                                        <v-card flat>
                                            <v-card-text>
                                                <v-row align="center" justify="start">
                                                    <v-col cols="12" md="3" class="text-md-right">
                                                        Flag
                                                    </v-col>
                                                    <v-col cols="12" md="9" class="black--text">
                                                        {{item.flag}}
                                                    </v-col>
                                                </v-row>
                                                <v-divider></v-divider>
                                                <v-row align="center" justify="start">
                                                    <v-col cols="12" md="3" class="text-md-right">
                                                        Staff
                                                    </v-col>
                                                    <v-col cols="12" md="9" class="black--text">
                                                        <template v-if="item.staffs">
                                                            <template v-for="(staff, index) in item.staffs">
                                                                <v-chip
                                                                    class="mr-1"
                                                                    :key="index" 
                                                                    :color="parseInt(staff.is_claimed) > 0 ? 'primary' : 'warning'"
                                                                    primary dark small>
                                                                    {{staff.full_name}}
                                                                </v-chip>
                                                            </template>
                                                        </template>
                                                    </v-col>
                                                </v-row>
                                                <v-divider></v-divider>
                                                <v-row align="center" justify="start">
                                                    <v-col cols="12" md="3" class="text-md-right">
                                                        Category
                                                    </v-col>
                                                    <v-col cols="12" md="9" class="black--text">
                                                        {{item.category_name}} / {{item.category_sub_name}}
                                                    </v-col>
                                                </v-row>
                                                <v-divider></v-divider>
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

                            <v-card>
                                <v-card-text class="pa-0 grey lighten-4" style="min-height: 200px;">
                                    <ticket-comment-list 
                                        class="pt-5" 
                                        url-more="dashboard/ticket_comments"
                                        :ticket-id="item.id"
                                        :items="item.comments.rows"
                                        :total-rows.sync="item.comments.total"
                                    />
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <v-col cols="12" lg="3" md="5">
                            <ticket-detail-timeline 
                                :logs="item.logs" 
                            />
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
import { TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline, TicketCommentList } from '../../components/Ticket';
export default {
    name: 'TicketDetail',
    components: {
        TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline, TicketCommentList
    },
    data() {
        return {
            show: false,
            loading: false,
            item: {
                id: '',
                comments: {
                    rows: [],
                    total: 0,
                },
                notes: []
            }
        }
    },
    created() {
        if (this.$route.params.id) {
            this.item.id = this.$route.params.id;
            this.fetchItem();
        }
    },
    mounted() {
        this.show = true
    },
    methods: {
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios.get('dashboard/ticket', { params: {id: this.item.id} }).then(response => {
                const { data } = response;
                const row = data.data;

                this.item = row;

                this.loading = false;
            }).catch(error => {
                const {statusText, data} = error;
                if (typeof data.message !== "undefined") {
                    this.$coresnackbars.error(data.message);
                } else {
                    this.$coresnackbars.error(statusText);
                }
                this.loading = false;
            });
        }
    }
}
</script>