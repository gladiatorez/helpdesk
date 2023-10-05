<template>
    <v-dialog :value="opened" max-width="500" persistent>
        <v-card :disabled="loading" :loading="loading">
            <v-card-title class="error--text">
                Close Ticket
            </v-card-title>
            <v-card-text>
                <v-textarea
                    :loading="loading"
                    v-model="reasonRow"
                    label="Reason"
                    placeholder="Enter some reason"
                    :error-message="errorMsg.reason"
                ></v-textarea>
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
    name: 'closed-ticket-modal',
    props: ['value', 'ticketId'],
    data() {
        return {
            loading: false,
            reasonRow: '',
            errorMsg: {
                reason: ''
            }
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
    methods: {
        closeAction() {
            this.opened = false;
        },
        clearErrorMsg() {
            this.errorMsg = {
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

            this.$axios.post('queues/assignment/closed_ticket', item).then((response) => {
                const { data } = response;

                if (data.success) {
                    this.$coresnackbars.success(data.message);
                    this.note = '';
                    this.opened = false;
                    this.$emit('save-success');
                } else {
                    this.$coresnackbars.error(data.message);
                }

                this.loading = false;
            })
            .catch((error) => {
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
