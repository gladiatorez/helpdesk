<template>
    <div>
        <v-card flat class="pt-3" v-if="item">
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
                            <v-text-field persistent-hint
                                type="number"
                                v-model.number="row.estimate"
                                label="Estimate"
                                placeholder="Enter estimate"
                                hint="in seconds"
                                :error-messages="errorMsg.estimate"
                                :hide-details="!errorMsg.estimate"
                            ></v-text-field>
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
                        <v-col cols="12">
                            <v-combobox small-chips multiple hide-selected
                                :search-input.sync="keywordSearch"
                                :items="keywordOptions"
                                v-model="row.keywords"
                                :disabled="loading"
                                :error-messages="errorMsg.keywords"
                                :hide-details="!errorMsg.keywords"
                                label="Keywords"
                                @input.native="keywordsChange"
                            ></v-combobox>
                        </v-col>
                        <v-col cols="12">
                            <v-textarea
                                v-model="row.note"
                                :error-messages="errorMsg.note"
                                :hide-details="!errorMsg.note"
                                label="Note"
                                placeholder="Enter some note"></v-textarea>
                        </v-col>
                    </v-row>
                </v-card-text>
            </template>
            <template v-else>
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
            </template>
            <v-card-actions>
                <template v-if="!item.is_claimed">
                    <v-btn depressed color="primary" @click="acceptAction">
                        Accept
                    </v-btn>
                    <v-btn depressed outlined color="primary" @click="modalDelegation = true">
                        Delegation
                    </v-btn>
                </template>
                <template v-else>
                    <template v-if="item.flag !== 'CLOSED' && item.flag !== 'CANCEL'">
                        <v-menu>
                            <template v-slot:activator="{ on }">
                                <v-btn outlined dark small
                                    v-on="on"
                                    color="primary">
                                    <v-icon>more_horiz</v-icon>
                                </v-btn>
                            </template>
                            <v-list>
                                <v-list-item @click="modalNote = true">
                                    <v-list-item-title>Add note</v-list-item-title>
                                </v-list-item>
                                <!-- <v-list-item @click="modalStaff = true">
                                    <v-list-item-title>Add staff</v-list-item-title>
                                </v-list-item> -->
                                <v-list-item @click="modalFlag = true">
                                    <v-list-item-title>Change flag</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </template>
                </template>
            </v-card-actions>
        </v-card>

        <template v-if="item.is_claimed">
            <add-note-modal v-if="modalNote" v-model="modalNote" @save-success="onNoteAdded" :ticket-id="item.id"></add-note-modal>
            <!-- <add-staff-modal v-if="modalStaff" v-model="modalStaff" @save-success="onStaffAdded" :ticket-id="item.id"></add-staff-modal> -->
            <change-flag-modal v-if="modalFlag" v-model="modalFlag" @save-success="onStaffAdded" :ticket-id="item.id" :flag="item.flag"></change-flag-modal>
        </template>
        <template v-else>
            <delegation-modal
                v-if="modalDelegation"
                v-model="modalDelegation"
                @save-success="onDelegation"
                :ticket-id="item.id"
                :category-id="item.category_id"
            ></delegation-modal>
            <!-- <closed-ticket-modal v-model="modalClosedTicket" @save-success="onClosedTicket" :ticket-id="item.id"></closed-ticket-modal> -->
            <!-- <return-it-modal v-model="modalReturnIt" @save-success="onReturnIt" :ticket-id="item.id"></return-it-modal> -->
        </template>
    </div>
</template>

<script>
import AddNoteModal from './AddNoteModal';
import DelegationModal from './DelegationModal';
import ChangeFlagModal from './ChangeFlagModal';
// import AddStaffModal from './AddStaffModal';
// import ClosedTicketModal from './ClosedTicketModal';
// import ReturnItModal from './ReturnItModal';
export default {
    name: 'manage-queue',
    components: {
        AddNoteModal, ChangeFlagModal, DelegationModal,
        // AddStaffModal,
        // ClosedTicketModal, ReturnItModal
    },
    props: {
        item: {
            type: Object,
            required: true
        },
        flag: {
            type: String,
            required: true,
            default: ''
        }
    },
    data() {
        return {
            loading: false,
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
            modalNote: false,
            modalStaff: false,
            modalFlag: false,
            modalDelegation: false,
            modalClosedTicket: false,
            modalReturnIt: false,
        }
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
                    that.row.staff.push(element.staff_id)
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
                id: '',
                categoryId: '',
                categorySubId: '',
                estimate: '',
                priorityId: '',
                keywords: '',
                note: '',
                'staff[]': ''
            };
        },
        keywordsChange: _.debounce(function (e) {
            const that = this;
            this.$axios.get('queues/assignment/keywords', {
                params: {q: e.target.value}
            }).then(response => {
                const { data } = response;
                that.keywordOptions = [];
                data.forEach(element => {
                that.keywordOptions.push(element.name);
                });
            })
        }, 100),
        fetchCategory() {
            this.categoryOptions = [];
            this.$axios.get('queues/assignment/category')
                .then(response => {
                const {data} = response;
                if (!data.success) {
                    this.$coresnackbars.error(data.message);
                } else {
                    this.categoryOptions = data.rows;
                }
            }).catch((error) => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
            });
        },
        fetchCategorySub() {
            if (!this.row.categoryId) {
                return false;
            }

            this.row.categorySubId = '';
            this.categorySubOptions = [];
            this.$axios.get('queues/assignment/categorysub', {
                params: {parent_id: this.row.categoryId}
            })
            .then(response => {
                const {data} = response;
                if (!data.success) {
                    this.$coresnackbars.error(data.message);
                } else {
                    this.categorySubOptions = data.rows;
                }
            }).catch((error) => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
            });
        },
        fetchPriority() {
            this.row.categorySubId = '';
            this.categorySubOptions = [];
            this.$axios.get('queues/assignment/priority')
            .then(response => {
                const {data} = response;
                if (!data.success) {
                    this.$coresnackbars.error(data.message);
                } else {
                    this.priorityOptions = data.rows;
                }
            }).catch((error) => {
                    console.log(error);
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);
            });
        },
        acceptAction() {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.set('id', this.item.id);

            this.$axios.post('queues/assignment/approve', item).then((response) => {
                const { data } = response;

                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.$emit('approve-success');
                } else {
                    this.$coresnackbars.error(data.message);
                }

                this.loading = false;
            }).catch((error) => {
                const {statusText, data} = error;
                this.$coresnackbars.error(statusText);

                if (typeof data !== "undefined" && typeof data.message !== "undefined") {
                    if (typeof data.message === 'object') {
                    this.errorMsg = Object.assign({}, this.errorMsg, data.message);
                    }
                }

                this.loading = false;
            });
        },
        onNoteAdded() {
            this.$emit('approve-success');
        },
        onStaffAdded() {
            this.$emit('approve-success');
        },
        onDelegation() {
            this.$emit('delegation-success');
            this.$router.push({
                name: 'queues.list.index'
            });
        },
        onClosedTicket() {
            this.$emit('approve-success');
        },
        onReturnIt() {
            this.$emit('return-it-success');
        }
    }
}
</script>
