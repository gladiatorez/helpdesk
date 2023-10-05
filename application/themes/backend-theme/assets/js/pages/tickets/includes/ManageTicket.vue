<template>
    <div>
        <v-card flat class="pt-3">
            <template v-if="flag === 'REQUESTED'">
                <v-card-text class="pa-3">
                    <v-row>
                        <v-col cols="12" md="6">
                            <v-select
                                :disabled="loading"
                                label="Category"
                                placeholder="Search category"
                                v-model="row.categoryId"
                                :items="categoryOptions"
                                :error-messages="errorMsg.categoryId"
                                :hide-details="!errorMsg.categoryId"
                                @change="fetchCategorySub"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-select
                                :disabled="loading"
                                label="Category sub"
                                placeholder="Search category sub"
                                v-model="row.categorySubId"
                                :items="categorySubOptions"
                                :error-messages="errorMsg.categorySubId"
                                :hide-details="!errorMsg.categorySubId"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                persistent-hint
                                type="number"
                                v-model.number="row.estimate"
                                label="Estimate"
                                placeholder="Enter estimate"
                                hint="in seconds"
                                :error-messages="errorMsg.estimate"
                                :hide-details="!errorMsg.estimate"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-select
                                :disabled="loading"
                                label="Priority"
                                placeholder="Choose priority"
                                v-model="row.priorityId"
                                :items="priorityOptions"
                                :error-messages="errorMsg.priorityId"
                                :hide-details="!errorMsg.priorityId"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-combobox
                                small-chips
                                multiple
                                hide-selected
                                :search-input.sync="keywordSearch"
                                :items="keywordOptions"
                                v-model="row.keywords"
                                :disabled="loading"
                                :error-messages="errorMsg['keywords[]']"
                                :hide-details="!errorMsg['keywords[]']"
                                label="Keywords"
                                @input.native="keywordsChange"
                            ></v-combobox>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-textarea
                                v-model="row.note"
                                :error-messages="errorMsg.note"
                                :hide-details="!errorMsg.note"
                                label="Note"
                                placeholder="Enter some note"
                            ></v-textarea>
                        </v-col>
                    </v-row>
                </v-card-text>
            </template>
            <template v-else>
                <v-card-text>
                    <v-row align="center" justify="start">
                        <v-col cols="12" md="3" class="text-md-right">Flag</v-col>
                        <v-col cols="12" md="9" class="black--text">{{item.flag}}</v-col>
                    </v-row>
                    <v-divider />
                    <v-row align="center" justify="start">
                        <v-col cols="12" md="3" class="text-md-right">Staff</v-col>
                        <v-col cols="12" md="9" class="black--text">
                            <template v-if="item.staffs">
                                <template v-for="(staff, index) in item.staffs">
                                    <v-chip
                                        class="mr-1"
                                        :key="index"
                                        :color="parseInt(staff.is_claimed) > 0 ? 'primary' : 'warning'"
                                        primary dark small
                                        :close="$can('manage','tickets') && flag !== 'CLOSED'"
                                        @click:close="removeStaffShow(staff)"
                                    >{{staff.full_name}}</v-chip>
                                </template>
                            </template>
                        </v-col>
                    </v-row>
                    <v-divider />
                    <v-row align="center" justify="start">
                        <v-col cols="12" md="3" class="text-md-right">Category</v-col>
                        <v-col
                            cols="12"
                            md="9"
                            class="black--text"
                        >{{item.category_name}} / {{item.category_sub_name}}</v-col>
                    </v-row>
                    <v-divider />
                    <v-row align="center" justify="start">
                        <v-col cols="12" md="3" class="text-md-right">Priority</v-col>
                        <v-col cols="12" md="9" class="black--text">{{item.priority_name}}</v-col>
                    </v-row>
                </v-card-text>
            </template>
            <v-card-actions>
                <template v-if="flag === 'REQUESTED'  && $can('manage','tickets')">
                    <v-btn depressed outlined color="error" @click="modalReject = true">Reject</v-btn>
                    <v-btn depressed color="primary" @click="acceptAction">Accept</v-btn>
                </template>
                <template v-if-else>
                    <template v-if="flag !== 'CLOSED' && flag !== 'REQUESTED' && flag !== 'CANCEL' && $can('manage','tickets')">
                        <v-menu top offset-y>
                            <template v-slot:activator="{ on }">
                                <v-btn outlined dark small v-on="on" color="primary">
                                    <v-icon>more_horiz</v-icon>
                                </v-btn>
                            </template>
                            <v-list>
                                <v-list-item @click="modalNote = true">
                                    <v-list-item-title>Add note</v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="modalStaff = true">
                                    <v-list-item-title>Add staff</v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="modalFlag = true">
                                    <v-list-item-title>Change flag</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </template>
                </template>
            </v-card-actions>
        </v-card>

        <template v-if="flag !== 'REQUESTED'">
            <add-note-modal
                v-if="modalNote"
                v-model="modalNote"
                @save-success="onNoteAdded"
                :ticket-id="item.id"
            ></add-note-modal>
            <add-staff-modal
                v-if="modalStaff"
                v-model="modalStaff"
                @save-success="onStaffAdded"
                :ticket-id="item.id"
            ></add-staff-modal>
            <change-flag-modal
                v-if="modalFlag"
                v-model="modalFlag"
                @save-success="onStaffAdded"
                :ticket-id="item.id"
                :flag="item.flag"
            ></change-flag-modal>
        </template>
        <template v-else>
            <reject-ticket-modal
                v-if="modalReject"
                v-model="modalReject"
                @save-success="onRejected"
                :ticket-id="item.id"
            ></reject-ticket-modal>
        </template>

        <v-dialog
            v-if="modalRemoveStaff.show"
            v-model="modalRemoveStaff.show"
            max-width="500px"
            persistent
        >
            <v-card :loading="modalRemoveStaff.loading" :disabled="modalRemoveStaff.loading">
                <v-card-title>
                    <span class="font-weight-bold">Remove staff</span>
                </v-card-title>
                <v-card-text>
                    <p class="error--text">You will delete the ticket staff</p>
                    <v-text-field
                        readonly
                        :disabled="modalRemoveStaff.loading"
                        label="Staff"
                        :value="modalRemoveStaff.row.staff.full_name"
                    ></v-text-field>
                    <v-textarea
                        background-color="white"
                        v-model="modalRemoveStaff.row.reason"
                        :disabled="modalRemoveStaff.loading"
                        label="Reason"
                        placeholder="Enter some reason"
                    ></v-textarea>
                </v-card-text>
                <v-card-actions>
                    <v-btn
                        color="primary"
                        small
                        depressed
                        @click="removeStaffAction">
                        Submit
                    </v-btn>
                    <v-btn color="grey darken-1" small outlined @click="removeStaffClose">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import AddNoteModal from "./AddNoteModal.vue";
import RejectTicketModal from "./RejectTicketModal.vue";
import AddStaffModal from "./AddStaffModal.vue";
import ChangeFlagModal from "./ChangeFlagModal.vue";
export default {
    name: "manage-ticket",
    components: {
        AddNoteModal,
        AddStaffModal,
        ChangeFlagModal,
        RejectTicketModal
    },
    props: {
        item: {
            type: Object,
            required: true
        },
        flag: {
            type: String,
            required: true,
            default: ""
        }
    },
    data() {
        return {
            loading: false,
            row: {
                categoryId: "",
                categorySubId: "",
                estimate: 0,
                priorityId: "",
                keywords: [],
                staff: [],
                note: ""
            },
            errorMsg: {
                id: "",
                categoryId: "",
                categorySubId: "",
                estimate: "",
                priorityId: "",
                "keywords[]": "",
                "staff[]": "",
                note: ""
            },
            categoryOptions: [],
            categorySubOptions: [],
            priorityOptions: [],
            keywordSearch: null,
            keywordOptions: [],
            staffOptions: [],
            modalNote: false,
            modalReject: false,
            modalStaff: false,
            modalFlag: false,
            modalRemoveStaff: {
                show: false,
                loading: false,
                row: {
                    staff: null,
                    reason: ""
                }
            }
        };
    },
    watch: {
        item(row) {
            this.row.categoryId = row.category_id;
            this.row.estimate = row.estimate;
            this.row.priorityId = row.priority_id;

            if (row.staffs) {
                const that = this;
                that.row.staff = [];
                row.staffs.forEach(element => {
                    that.row.staff.push(element.staff_id);
                });
            }

            this.fetchCategorySub();
            this.row.categorySubId = row.category_sub_id;
        }
    },
    created() {
        this.initDetail();
    },
    methods: {
        async initDetail() {
            await this.fetchCategory();
            await this.fetchPriority();
            // await this.fetchStaff();
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: "",
                categoryId: "",
                categorySubId: "",
                estimate: "",
                priorityId: "",
                keywords: "",
                note: "",
                "staff[]": ""
            };
        },
        keywordsChange: _.debounce(function(e) {
            const that = this;
            this.$axios
                .get("faq/keywords", {
                    params: { q: e.target.value }
                })
                .then(response => {
                    const { data } = response;
                    that.keywordOptions = [];
                    data.forEach(element => {
                        that.keywordOptions.push(element.name);
                    });
                });
        }, 100),
        fetchCategory() {
            this.categoryOptions = [];
            this.$axios
                .get("tickets/category")
                .then(response => {
                    const { data } = response;
                    if (!data.success) {
                        this.$coresnackbars.error(data.message);
                    } else {
                        this.categoryOptions = data.rows;
                    }
                })
                .catch(error => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
                });
        },
        fetchCategorySub() {
            if (!this.row.categoryId) {
                return false;
            }

            this.row.categorySubId = "";
            this.categorySubOptions = [];
            this.$axios
                .get("tickets/categorysub", {
                    params: { parent_id: this.row.categoryId }
                })
                .then(response => {
                    const { data } = response;
                    if (!data.success) {
                        this.$coresnackbars.error(data.message);
                    } else {
                        this.categorySubOptions = data.rows;
                    }
                })
                .catch(error => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
                });
        },
        fetchPriority() {
            this.row.categorySubId = "";
            this.categorySubOptions = [];
            this.$axios
                .get("tickets/priority")
                .then(response => {
                    const { data } = response;
                    if (!data.success) {
                        this.$coresnackbars.error(data.message);
                    } else {
                        this.priorityOptions = data.rows;
                    }
                })
                .catch(error => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
                });
        },
        /*fetchStaff() {
      const that = this;
            that.staffOptions = [];
      that.$axios.get('tickets/staff_list')
        .then(response => {
          const { data } = response;

          that.staffOptions = [];
          data.staffOptions.forEach(element => {
            that.staffOptions.push({
              value: element.id,
              text: element.fullName
            })
          });
        })
        .catch(error => {
          console.log(error);
        });
        },*/
        // rejectAction() {
        //     if (this.loading) {
        //         return false;
        //     }

        //     const confirmText = sprintf(
        //         "Are you sure want to reject %s",
        //         this.item.number
        //     );
        //     if (confirm(confirmText)) {
        //         this.loading = true;
        //         const that = this;
        //         this.$axios
        //             .get("tickets/request_reject", {
        //                 params: { id: this.item.id }
        //             })
        //             .then(response => {
        //                 const { data } = response;

        //                 if (data.success) {
        //                     that.$coresnackbars.success(data.message);
        //                     this.$router.push({
        //                         name: "tickets.list.index"
        //                     });
        //                 } else {
        //                     that.$coresnackbars.error(data.message);
        //                 }
        //                 that.loading = false;
        //             })
        //             .catch(function(error) {
        //                 const { statusText, status } = error;
        //                 that.$coresnackbars.error(
        //                     "Code: " + status + " " + statusText
        //                 );
        //             })
        //             .then(() => {
        //                 that.loading = false;
        //             });
        //     }
        // },
        acceptAction() {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.set("id", this.item.id);
            item.set(
                "categoryId",
                this.row.categoryId ? this.row.categoryId : ""
            );
            item.set(
                "categorySubId",
                this.row.categorySubId ? this.row.categorySubId : ""
            );
            item.set("estimate", this.row.estimate ? this.row.estimate : "0");
            item.set(
                "priorityId",
                this.row.priorityId ? this.row.priorityId : ""
            );
            item.set("note", this.row.note ? this.row.note : "");

            if (this.row.staff.length > 0) {
                this.row.staff.forEach(element => {
                    item.append("staff[]", element);
                });
            }

            if (this.row.keywords.length > 0) {
                this.row.keywords.forEach(element => {
                    item.append("keywords[]", element);
                });
            }

            this.$axios
                .post("tickets/request_approve", item)
                .then(response => {
                    const { data } = response;

                    if (data.success) {
                        this.$coresnackbars.success(data.message);
                        this.$emit("approve-success");
                    } else {
                        this.$coresnackbars.error(data.message);
                    }

                    this.loading = false;
                })
                .catch(error => {
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);

                    if (
                        typeof data !== "undefined" &&
                        typeof data.message !== "undefined"
                    ) {
                        if (typeof data.message === "object") {
                            this.errorMsg = Object.assign(
                                {},
                                this.errorMsg,
                                data.message
                            );
                        }
                    }

                    this.loading = false;
                });
        },
        onRejected() {
            this.$emit("rejected-success");
        },
        onNoteAdded() {
            this.$emit("approve-success");
        },
        onStaffAdded() {
            this.$emit("approve-success");
        },
        removeStaffShow(staff) {
            this.modalRemoveStaff.loading = false;
            this.modalRemoveStaff.row.staff = staff;
            this.modalRemoveStaff.row.reason = "";
            this.modalRemoveStaff.show = true;
        },
        removeStaffClose() {
            this.modalRemoveStaff.loading = false;
            this.modalRemoveStaff.row.staff = null;
            this.modalRemoveStaff.row.reason = "";
            this.modalRemoveStaff.show = false;
        },
        removeStaffAction() {
            if (this.modalRemoveStaff.loading) {
                return false;
            }

            this.modalRemoveStaff.loading = true;

            const item = new FormData();
            item.set("ticketId", this.item.id);
            item.set(
                "staffId",
                this.modalRemoveStaff.row.staff
                    ? this.modalRemoveStaff.row.staff.staff_id
                    : ""
            );
            item.set("reason", this.modalRemoveStaff.row.reason);

            this.$axios
                .post("tickets/staff_remove", item)
                .then(response => {
                    const { data } = response;

                    if (data.success) {
                        this.$coresnackbars.success(data.message);
                        this.$emit("remove-staff-success");
                        this.removeStaffClose();
                    } else {
                        this.$coresnackbars.error(data.message);
                    }

                    this.modalRemoveStaff.loading = false;
                })
                .catch(error => {
                    this.modalRemoveStaff.loading = false;

                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);

                    if (
                        typeof data !== "undefined" &&
                        typeof data.message !== "undefined"
                    ) {
                        if (typeof data.message === "object") {
                            this.errorMsg = Object.assign(
                                {},
                                this.errorMsg,
                                data.message
                            );
                        }
                    }
                });
        }
    }
};
</script>
