<template>
    <v-dialog v-model="opened" max-width="500px">
        <v-card :disabled="loading" :loading="loading">
            <v-card-title>
                Add staff
            </v-card-title>
            <v-card-text>
                <v-select small-chips multiple deletable-chips
                    v-model="staff"
                    :items="staffOptions"
                    :disabled="loading"
                    :error-messages="errorStaff"
                    label="Staff"
                ></v-select>
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
    name: 'add-staff-modal',
    props: {
        value: Boolean,
        ticketId: String
    },
    data: function() {
        return {
            loading: false,
            staff: [],
            errorStaff: '',
            staffOptions: []
        }
    },
    computed: {
        opened: {
            get() {
                return this.value
            },
            set(payload) {
                this.$emit('input', payload)
            }
        }
    },
    created() {
        this.fetchStaff();
        this.opened = this.value;
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
        closeAction() {
            this.opened = false;
        },
        clearErrorMsg() {
            this.errorMsg = {
                note: ''
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
            if (this.staff.length > 0) {
                this.staff.forEach(element => {
                    item.append('staff[]', element);
                });
            }

            this.$axios.post('queues/assignment/add_staff', item).then((response) => {
                const { data } = response;

                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.staff = [];
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
