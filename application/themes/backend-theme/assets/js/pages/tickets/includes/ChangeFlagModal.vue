<template>
    <v-dialog v-model="opened" max-width="500px" persistent scrollable>
        <v-card :disabled="loading" :loading="loading">
            <v-card-title>
                Change flag
            </v-card-title>
            <v-card-text>
                <v-select
                    background-color="white"
                    v-model="flagRow"
                    :items="flagfOptions"
                    :error-messages="errorMsg.flag"
                    label="Flag"
                ></v-select>

                <v-textarea
                    background-color="white"
                    v-model="reasonRow"
                    :error-messages="errorMsg.reason"
                    label="Reason"
                ></v-textarea>

                <v-textarea
                    v-if="flagRow === 'FINISHED'"
                    background-color="white"
                    v-model="causeRow"
                    :error-messages="errorMsg.cause"
                    label="Cause problem"
                ></v-textarea>

                <v-textarea
                    v-if="flagRow === 'FINISHED'"
                    background-color="white"
                    v-model="solutionRow"
                    :error-messages="errorMsg.solution"
                    label="Solution"
                ></v-textarea>

                <v-combobox small-chips multiple hide-selected persistent-hint
                    v-if="flagRow === 'CLOSED'"
                    :search-input.sync="keywordSearch"
                    :items="keywordOptions"
                    v-model="keywordsRow"
                    :disabled="loading"
                    :error-messages="errorMsg.keywords"
                    label="Keywords"
                    @input.native="keywordsChange"
                    hint="keywords are used to make it easier for users to search"
                ></v-combobox>
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
    name: 'change-flag-modal',
    props: ['value', 'ticketId', 'flag'],
    data() {
        return {
            loading: false,
            flagRow: '',
            reasonRow: '',
            causeRow: '',
            solutionRow: '',
            keywordsRow: [],
            errorMsg: {
                flag: '',
                reason: '',
                cause: '',
                solution: '',
                keywords: '',
            },
            keywordSearch: null,
            keywordOptions: []
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
        },
        flagfOptions() {
            let flags = [
                {value: 'PROGRESS', text: 'Progress'},
                {value: 'HOLD', text: 'Hold'},
                {value: 'FINISHED', text: 'Finished'},
            ]
            if (this.flag === 'FINISHED') {
                flags.push({value: 'CLOSED', text: 'Closed'})
            }
            return flags
        },
    },
    created() {
        this.flagRow = '';
        this.reasonRow = '';
        this.causeRow = '';
        this.solutionRow = '';
        this.keywordsRow = [];
    },
    methods: {
        closeAction() {
            this.opened = false;
        },
        clearErrorMsg() {
            this.errorMsg = {
                flag: '',
                reason: '',
                cause: '',
                solution: '',
                keywords: '',
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
            item.set('flag', this.flagRow);
            item.set('reason', this.reasonRow);
            item.set('cause', this.causeRow ? this.causeRow : '');
            item.set('solution', this.solutionRow ? this.solutionRow : '');
            if (this.keywordsRow.length > 0) {
                this.keywordsRow.forEach(element => {
                    item.append('keywords[]', element);
                });
            }

            this.$axios.post('tickets/change_flag', item).then((response) => {
                const { data } = response;
                
                if (data.success) {
                    this.$coresnackbars.success(data.message);
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
        },
        keywordsChange: _.debounce(function (e) {
            const that = this;
            this.$axios.get('tickets/keywords', {
                params: {q: e.target.value}
            }).then(response => {
                const { data } = response;
                that.keywordOptions = [];
                data.forEach(element => {
                    that.keywordOptions.push(element.name);
                });
            })
        }, 100),
    }
}
</script>
