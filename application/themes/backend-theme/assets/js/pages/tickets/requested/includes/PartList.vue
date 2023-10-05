<template>
    <v-card flat>
        <v-card-title>Check list part</v-card-title>
        <v-card-text v-if="!hideForm" class="pb-0">
            <v-card class="elevation-2" :loading="loading" :disabled="loading">
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="7">
                            <v-text-field
                                v-model="partListForm.name"
                                class="mt-0 pt-0"
                                placeholder="Type some part"
                                single-line hide-details>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-text-field
                                v-model.number="partListForm.qty"
                                type="number"
                                class="mt-0 pt-0"
                                placeholder="Qty"
                                suffix="Unit"
                                single-line hide-details>
                            </v-text-field>
                        </v-col>
                        <v-col cols="12" md="2" class="d-flex align-center">
                            <v-btn depressed x-small color="primary" @click="addAction">Save</v-btn>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-card-text>
        <template v-if="items.length <= 0">
            <v-card-text>
                <v-alert :value="true" type="warning" dense text class="mb-0 mt-4">
                    Empty results
                </v-alert>
            </v-card-text>
        </template>
        <template v-else>
            <v-list>
                <template v-for="(part, index) in items">
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
                            <v-list-item-action v-if="!hideForm">
                                <v-btn
                                    color="error" x-small depressed outlined
                                    :style="hover ? 'visibility:visible' : 'visibility:hidden'"
                                    @click="removeAction(part)">
                                    <v-icon left x-small>ms-Icon ms-Icon--Delete</v-icon> Delete
                                </v-btn>
                            </v-list-item-action>
                        </v-list-item>
                    </v-hover>
                    <v-divider v-if="index < items.length - 1" />
                </template>
            </v-list>
        </template>
    </v-card>
</template>

<script>
export default {
    name: 'PartList',
    props: {
        ticketId: {
            required: true,
            type: String,
        },
        items: Array,
        hideForm: Boolean
    },
    data() {
        return {
            loading: false,
            partListForm: {
                name: '',
                qty: 1,
            },
        }
    },
    methods: {
        addAction() {
            if (this.loading) {
                return false;
            }
            if (this.hideForm) {
                return false
            }

            if (!this.partListForm.name) {
                return false;
            }

            this.loading = true;
            const form = new FormData();
            form.set("ticketId", this.ticketId);
            form.set("part", this.partListForm.name);
            form.set("qty", this.partListForm.qty);

            this.$axios
                .post("tickets/add_partlist", form)
                .then(response => {
                    const { data } = response;

                    if (data.success) {
                        this.$coresnackbars.success(data.message);
                        this.partListForm.name = "";
                        this.partListForm.qty = 1;

                        if (data.row) {
                            this.items.push(data.row)
                        }
                    } else {
                        this.$coresnackbars.error(data.message);
                    }

                    this.loading = false;
                })
                .catch(error => {
                    const { statusText, data } = error;
                    this.$coresnackbars.error(statusText);

                    this.loading = false;
                })
                .then(() => {
                    this.loading = false;
                });
        },
        removeAction(item) {
            if (this.loading) {
                return false;
            }
            if (this.hideForm) {
                return false
            }

            const confirmText = 'This action cannot be undone. This will permanently deleted';
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("tickets/remove_partlist", {
                        params: { id: item.id, ticket_id: item.ticket_id }
                    })
                    .then(response => {
                        const { data } = response;

                        that.$coresnackbars.success(data.message);
                    })
                    .catch(function(error) {
                        const { statusText, status } = error;
                        that.$coresnackbars.error(
                            "Code: " + status + " " + statusText
                        );
                    })
                    .then(() => {
                        that.loading = false;

                        this.$emit('delete')
                    });
            }
        }
    }
}
</script>