<template>
    <v-dialog v-model="opened" max-width="500px" persistent>
        <v-card :disabled="loading" :loading="loading">
            <v-card-title>
                Delegation
            </v-card-title>
            <v-card-text>
                <p>Delegation, will remove yourself as staff PIC and adding some staff you have been choose</p>
                <v-select 
                    background-color="white"
                    v-model="categorySubIdRow"
                    :items="categorySubOptions"
                    :disabled="loading"
                    :error-messages="errorMsg.categorySubId"
                    label="Category sub"
                ></v-select>
                <v-text-field
                    label="Reason"
                    placeholder="Enter reason"
                    v-model="reasonRow"
                    :error-messages="errorMsg.reason"
                ></v-text-field>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" small depressed @click="addCloseAction">
                    Save changes
                </v-btn>
                <v-btn color="grey darken-1" small outlined @click="closeAction">
                    Cancel
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'delegation-modal',
    props: ['value', 'ticketId', 'categoryId'],
    data() {
        return {
            loading: false,
            reasonRow: '',
            categorySubIdRow: '',
            errorMsg: {
                reason: '',
                categorySubId: '',
            },
            staffOptions: [],
            categorySubOptions: []
        }
    },
    watch: {
        categoryId(category) {
            this.fetchCategorySub();
        }
    },
    computed: {
        opened: {
            set(value) {
                this.$emit('input', value);
            },
            get() {
                return this.value;
            }
        }
    },
    created() {
        this.fetchStaff();
        this.fetchCategorySub();
    },
    methods: {
        fetchStaff() {
            const that = this;
            that.staffOptions = [];
            that.$axios.get('queues/assignment/staff_list').then(response => {
                const { data } = response;

                that.staffOptions = [];
                data.staffOptions.forEach(element => {
                    that.staffOptions.push({
                        value: element.id,
                        text: element.fullName
                    })
                });
            }).catch(error => {
                console.log(error);
            });
        },
        fetchCategorySub() {
            if (!this.categoryId) {
                return false;
            }

            this.categorySubIdRow = '';
            this.categorySubOptions = [];
            this.$axios.get('queues/assignment/categorysub', {
                params: {parent_id: this.categoryId}
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
        closeAction() {
            this.opened = false;
        },
        clearErrorMsg() {
            this.errorMsg = {
                'staff[]': '',
                reason: ''
            };
        },
        addCloseAction() {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.set('ticketId', this.ticketId);
            item.set('reason', this.reasonRow);
            item.set('categorySubId', this.categorySubIdRow ? this.categorySubIdRow : '');

            this.$axios.post('queues/assignment/delegation', item).then((response) => {
                const { data } = response;

                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.categorySubIdRow = '';
                    this.reasonRow = '';
                    this.opened = false;
                    this.$emit('save-success');
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
        }
    }
}
</script>
