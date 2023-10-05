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

                <v-card class="mb-5">
                    <v-tabs centered>
                        <v-tab style="letter-spacing: normal">Manage</v-tab>
                        <v-tab style="letter-spacing: normal">Sender</v-tab>
                        <v-tab style="letter-spacing: normal">Note</v-tab>
                        <v-tab style="letter-spacing: normal">Unit Device</v-tab>
                        <v-tab style="letter-spacing: normal">Work result</v-tab>

                        <v-tab-item>
                            <manage-ticket 
                            :item="item" 
                            :flag="item.flag"
                            @approve-success="onApproveSuccess"
                            @return-it-success="closeAction"></manage-ticket>
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

                        <v-tab-item>
                            <v-row class="mx-0">
                                <v-col cols="12" md="6">
                                    <part-list
                                        :ticket-id="item.id"
                                        :items="item.part_list"
                                        hide-form
                                        @delete="fetchItem"
                                    />
                                </v-col>

                                <v-col cols="12" md="6">
                                    <part-photo-list
                                        :ticket-id="item.id"
                                        :photos="item.part_photos"
                                        hide-form
                                        @uploaded="fetchItem"
                                        @delete="fetchItem"
                                    />
                                </v-col>
                            </v-row>
                        </v-tab-item>

                        <v-tab-item>
                            <assignment-photo
                                :ticket-id="item.id"
                                :photos="item.work_result ? item.work_result : []"
                                :hide-form="item.flag === 'CLOSED'"
                                @uploaded="fetchItem"
                                @delete="fetchItem"
                            />
                        </v-tab-item>
                    </v-tabs>
                </v-card>

                <!-- <div class="v-dialog--scrollable" style="max-height: 650px; min-height: 200px;"> -->
                    <v-card>
                        <template v-if="item.flag !== 'CLOSED' && item.flag !== 'CANCEL'">
                            <v-card-title class="align-start">
                                <v-textarea
                                    solo rounded flat auto-grow dense
                                    background-color="blue-grey lighten-5"
                                    placeholder="Say something..." 
                                    class="font-weight-regular mr-2"
                                    hint="Press enter for send"
                                    rows="1"
                                    v-model.trim="comments"
                                    :loading="commentSending"
                                    v-on:keyup.enter="sendCommentAction"
                                />
                                <v-btn icon 
                                    color="primary"
                                    :disabled="commentSending"
                                    @click="sendCommentAction">
                                    <v-icon>send</v-icon>
                                </v-btn>
                            </v-card-title>
                            <v-divider />
                        </template>
                        <v-card-text class="pa-0 grey lighten-4" style="min-height: 200px;">
                            <ticket-comment-list 
                                class="pt-5" 
                                url-more="queues/assignment/comments"
                                :ticket-id="item.id"
                                :items="item.comments.rows"
                                :total-rows.sync="item.comments.total"
                            />
                        </v-card-text>
                    </v-card>
                <!-- </div> -->
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
import ManageTicket from './includes/ManageTicket';
import PartList from "../../tickets/all/includes/PartList.vue";
import PartPhotoList from "../../tickets/all/includes/PartPhotoList.vue";
import AssignmentPhoto from "./includes/AssignmentPhoto.vue";
import { TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline, TicketCommentList } from '../../../components/Ticket';
export default {
    name: 'queues-assignment-detail-page',
    components: {
        ManageTicket,
        TicketDetailCard, TicketDetailSender, TicketDetailNote, TicketDetailTimeline, TicketCommentList,
        PartList,
        PartPhotoList,
        AssignmentPhoto,
    },
    data() {
        return {
            loading: false,
            item: {
                id: '',
                comments: {
                    rows: [],
                    total: 0,
                },
                part_list: [],
                part_photos: [],
                work_result: [],
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
            staffOptions: [],
            comments: '',
            commentSending: false,
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
                name: 'queues.assignment.index',
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
            this.$axios.get('queues/assignment/item', { params: {id: this.item.id} }).then(response => {
                const { data } = response;
                const row = data.data;

                this.item = row;
                this.item.part_list = row.part_list ? row.part_list : []
                this.item.part_photos = row.part_photos ? row.part_photos : []
                this.item.work_result = row.work_result ? row.work_result : []

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
        },
        onApproveSuccess() {
            this.fetchItem();
        },
        sendCommentAction() {
            if (this.commentSending) {
                return false;
            }

            if (!this.comments) {
                return false;
            }

            this.commentSending = true;
            const form = new FormData();
            form.set('ticketId', this.item.id);
            form.set('comments', this.comments);
            
            this.$axios.post('queues/assignment/add_comment', form).then((response) => {
                const { data } = response;

                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.comments = '';
                } else {
                    this.$coresnackbars.error(data.message);
                }

                this.commentSending = false;
            }).catch((error) => {
                const {statusText, data} = error;
                this.$coresnackbars.error(statusText);

                this.commentSending = false;
            }).then(() => {
                this.commentSending = false;
            })
        }
    }
}
</script>
