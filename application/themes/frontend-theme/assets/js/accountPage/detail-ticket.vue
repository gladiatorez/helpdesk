<template>
    <v-container>
        <h3 class="mb-4 mt-4">Detail ticket</h3>

        <v-row row wrap>
            <v-col cols="12" lg="9" md="7">
                <v-card class="mb-5" outlined>
                    <v-card-title>
                        <div class="font-weight-bold">{{item.subject}}</div>
                        <v-spacer></v-spacer>
                        <ticket-flag-chip :flag="item.flag"></ticket-flag-chip>
                        <v-chip color="primary" outlined small dark class="ml-2">#{{item.number}}</v-chip>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text class="black--text">{{item.description}}</v-card-text>
                    <v-divider></v-divider>
                    <v-card-text>
                        <div class="ticket-card-content">
                            <div v-if="item.attacment" class="ticket-card-section">
                                <div class="subheading font-weight-black">Attachment:</div>
                                <template v-for="(attachment, indexAttachment) in item.attachment">
                                    <a :href="siteUrl + 'files/download/' + attachment.file_id"
                                        :key="indexAttachment"
                                        style="display:inline-block; text-decoration: none"
                                        target="_blank">
                                        <v-chip small>
                                            <v-avatar class="blue-grey">
                                                <v-icon class="white--text">ms-Icon--attachment</v-icon>
                                            </v-avatar>
                                            Lampiran {{indexAttachment + 1}}
                                        </v-chip>
                                    </a>
                                </template>
                            </div>

                            <div v-if="item.category_name" class="ticket-card-section">
                                <div class="subheading font-weight-black">Category:</div>
                                {{item.category_name}} / {{item.category_sub_name}}
                            </div>

                            <div v-if="item.staffs" class="ticket-card-section">
                                <div class="subheading font-weight-black">PIC Person:</div>
                                <template v-for="(staff, indexStaff) in item.staffs">
                                    <v-chip :key="indexStaff" color="teal lighten-2" dark class="mr-3">
                                        <v-avatar left>
                                            <v-icon>account_circle</v-icon>
                                        </v-avatar><a :href="'https://wa.me/62' + staff.phone.replace('+62','').replace(' ','').replace('-','').replace('0', '')"  style="display:inline-block; text-decoration: none" target="_blank">
                                        <span class="subtitle-2">{{staff.full_name}} - {{staff.phone}}</span></a>
                                    </v-chip>
                                </template>
                            </div>

                            <div v-if="item.cause_descr" class="ticket-card-section">
                                <div class="subheading font-weight-black">Cause:</div>
                                {{item.cause_descr}}
                            </div>

                            <div v-if="item.solution_descr" class="ticket-card-section">
                                <div class="subheading font-weight-black">Solution:</div>
                                {{item.solution_descr}}
                            </div>

                            <div v-if="item.keywords" class="ticket-card-section">
                                <template v-for="(keyword, indexKeyword) in item.keywords">
                                    <v-chip small :key="indexKeyword" class="mr-2">{{keyword}}</v-chip>
                                </template>
                            </div>
                        </div>

                        <v-tabs v-if="item.part_list || item.part_photos || item.work_result" height="30">
                            <v-tab v-if="item.part_list || item.part_photos">Unit Device</v-tab>
                            <v-tab v-if="item.work_result">Work Result</v-tab>

                            <v-tab-item v-if="item.part_list || item.part_photos">
                                <v-row>
                                    <v-col v-if="item.part_list" cols="12" md="6">
                                        <v-card outlined>
                                            <v-card-title>Check list part</v-card-title>
                                            <template v-if="item.part_list.length <= 0">
                                                <v-card-text>
                                                    <v-alert :value="true" type="warning" dense text class="mb-0 mt-4">
                                                        Empty results
                                                    </v-alert>
                                                </v-card-text>
                                            </template>
                                            <template v-else>
                                                <v-list>
                                                    <template v-for="(part, index) in item.part_list">
                                                        <v-hover v-slot:default="{hover}">
                                                            <v-list-item :key="index">
                                                                <v-list-item-icon class="mr-2">
                                                                    <v-icon color="primary">check_circle</v-icon>
                                                                </v-list-item-icon>
                                                                <v-list-item-content>
                                                                    <v-list-item-title class="body-2">{{ part.name }}</v-list-item-title>
                                                                    <v-list-item-subtitle class="caption">
                                                                        Added at {{ $moment(part.created_at).format('DD/MM/YYYY HH:mm') }}
                                                                    </v-list-item-subtitle>
                                                                </v-list-item-content>
                                                            </v-list-item>
                                                        </v-hover>
                                                        <v-divider v-if="index < item.length - 1" />
                                                    </template>
                                                </v-list>
                                            </template>
                                        </v-card>
                                    </v-col>
                                    <v-col v-if="item.part_photos" cols="12" md="6">
                                        <v-card outlined>
                                            <v-card-title>Check list part</v-card-title>
                                            <template v-if="item.part_photos <= 0">
                                                <v-card-text>
                                                    <v-alert :value="true" type="warning" dense text class="mb-0 mt-4">
                                                        Empty results
                                                    </v-alert>
                                                </v-card-text>
                                            </template>
                                            <template v-else>
                                                <v-row class="mx-0">
                                                    <template v-for="(photo, index) in item.part_photos">
                                                        <v-col cols="12" md="4" :key="index">
                                                            <v-card outlined flat>
                                                                <a :href="siteUrl + 'files/download/' + photo.file_id" target="_blank" class="d-block">
                                                                    <v-img
                                                                        :src="siteUrl + 'files/thumb/' + photo.file_id + '/400/400'"
                                                                        height="100"
                                                                    />
                                                                </a>
                                                            </v-card>
                                                        </v-col>
                                                    </template>
                                                </v-row>
                                            </template>
                                        </v-card>
                                    </v-col>
                                </v-row>
                            </v-tab-item>

                            <v-tab-item v-if="item.work_result">
                                <v-card outlined>
                                    <template v-if="item.work_result.length <= 0">
                                        <v-card-text>
                                            <v-alert :value="true" type="warning" dense text class="mb-0 mt-4">
                                                Empty results
                                            </v-alert>
                                        </v-card-text>
                                    </template>
                                    <template v-else>
                                        <v-row class="mx-0">
                                            <template v-for="(photo, index) in item.work_result">
                                                <v-col cols="12" md="4" :key="index">
                                                    <v-card outlined flat>
                                                        <a :href="siteUrl + 'files/download/' + photo.file_id" target="_blank" class="d-block">
                                                            <v-img
                                                                :src="siteUrl + 'files/thumb/' + photo.file_id + '/400/400' "
                                                                height="200"
                                                            />
                                                        </a>
                                                    </v-card>
                                                </v-col>
                                            </template>
                                        </v-row>
                                    </template>
                                </v-card>
                            </v-tab-item>
                        </v-tabs>
                    </v-card-text>
                </v-card>

                <v-card outlined>
                    <template v-if="item.flag !== 'CLOSED'">
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
                            url-more="profile/personalinfo/ticket_comments"
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
</template>

<script>
import TicketFlagChip from "../../../../backend-theme/assets/js/components/core/FlagChip";
import TicketDetailTimeline from "../../../../backend-theme/assets/js/components/Ticket/DetailTimeline.vue";
import TicketCommentList from "../../../../backend-theme/assets/js/components/Ticket/TicketComment.vue";
export default {
    name: "detail-ticket",
    components: {
        TicketFlagChip, TicketDetailTimeline,
        TicketCommentList
    },
    data() {
        return {
            loading: false,
            item: {
                id: "",
                comments: {
                    rows: [],
                    total: 0,
                },
                part_list: null,
                part_photos: null,
                work_result: null
            },
            comments: '',
            commentSending: false,
        };
    },
    computed: {
        siteUrl() {
            return SITE_URL;
        }
    },
    created() {
        if (this.$route.params.id) {
            this.item.id = this.$route.params.id;
            this.fetchItem();
        }
    },
    methods: {
        fetchItem() {
            if (this.loading) {
                return false;
            }

            this.loading = true;
            this.$axios
                .get("profile/personalinfo/ticket", {
                    params: { id: this.item.id }
                })
                .then(response => {
                    const { data } = response;
                    const row = data.data;

                    this.item = row;

                    this.loading = false;
                })
                .catch(error => {
                    const { statusText, data } = error;
                    if (typeof data.message !== "undefined") {
                        this.$snackbars.error(data.message);
                    } else {
                        this.$snackbars.error(statusText);
                    }
                    this.loading = false;
                });
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
            
            this.$axios.post('profile/personalinfo/ticket_add_comment', form).then((response) => {
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
};
</script>

<style lang="scss" scoped>
.ticket-card-content {
    .ticket-card-section {
        margin-bottom: 24px;
    }
}
</style>
