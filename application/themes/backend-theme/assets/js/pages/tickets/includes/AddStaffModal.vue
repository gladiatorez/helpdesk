<template>
    <v-dialog v-model="opened" max-width="500px" persistent>
        <v-card :disabled="loading" :loading="loading">
            <v-card-title>
                Add staff
            </v-card-title>
            <v-card-text>
                <v-autocomplete small-chips multiple deletable-chips
                    background-color="white"
                    v-model="staff"
                    :items="staffOptions"
                    :error-messages="errorStaff"
                    label="Staff">
                    <template v-slot:item="data">
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ data.item.text }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                <v-badge 
                                    dot inline
                                    :color="data.item.hasTicket ? 'red' : 'cictGreen'" 
                                />
                                <span :class="{
                                  'red--text': data.item.hasTicket,
                                  'cictGreen--text': !data.item.hasTicket
                                }">
                                    {{ data.item.hasTicket ? 'Busy' : 'Ready' }}
                                </span>
                              <span class="d-inline-block mx-2 text--secondary">{{ data.item.progressCount }} Progress</span>
                              <span class="d-inline-block text--secondary">{{ data.item.holdCount }} Progress</span>
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </template>
                </v-autocomplete>
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
    props: ['value', 'ticketId'],
    data() {
        return {
            loading: false,
            staff: [],
            errorStaff: '',
            staffOptions: []
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
    },
    methods: {
        fetchStaff() {
            const that = this;
            that.staffOptions = [];
            that.$axios.get('tickets/staff_list').then(response => {
                const { data } = response;

                that.staffOptions = [];
                data.staffOptions.forEach(element => {
                    that.staffOptions.push({
                        value: element.id,
                        text: element.fullName,
                        hasTicket: element.hasTicket,
                        progressCount: element.progressCount,
                        holdCount: element.holdCount,
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

            this.$axios.post('tickets/add_staff', item).then((response) => {
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
