<template>
    <v-dialog :value="opened" max-width="500" persistent>
        <v-card :disabled="loading" :loading="loading">
            <v-card-title>
                Add note
            </v-card-title>
            <v-card-text>
                <v-textarea
                    :loading="loading"
                    v-model="note"
                    label="Note"
                    placeholder="Enter some note"
                    :error-message="errorMsg.note"
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
    name: 'add-note-modal',
    props: {
        value: Boolean,
        ticketId: String
    },
    data() {
        return {
            loading: false,
            note: '',
            errorMsg: {
                note: ''
            }
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
        this.opened = this.value;
    },
    methods: {
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
            item.set('note', this.note);

            this.$axios.post('queues/assignment/add_note', item).then((response) => {
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
