<template>
    <v-card flat>
        <v-card-text v-if="!hideForm" class="pb-0">
            <v-card outlined flat :loading="loading" :disabled="loading">
                <v-card-text class="d-flex">
                    <v-file-input
                        v-model="partListForm.userfile"
                        class="mt-0 pt-0 mr-4"
                        placeholder="Please choose a photo"
                        single-line hide-details>
                    </v-file-input>

                    <v-btn depressed small color="primary" @click="addAction">Upload</v-btn>
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
            <v-row class="mx-0">
                <template v-for="(photo, index) in items">
                    <v-col cols="12" md="4" :key="index">
                        <v-card outlined flat>
                            <a :href="baseUrl + 'files/download/' + photo.file_id" target="_blank" class="d-block">
                                <template v-if="photo.file && allowPreviews.includes(photo.file.extension.toLowerCase())">
                                    <v-img
                                        :src="baseUrl + 'files/thumb/' + photo.file_id + '/400/400' "
                                        height="200"
                                    />
                                </template>
                                <template v-else>
                                    <div class="font-weight-bold text-center py-8 grey lighten-4">{{ photo.file ? photo.file.extension : '' }}</div>
                                </template>
                            </a>
                            <v-card-actions v-if="!hideForm">
                                <v-btn small text color="error" @click="removeAction(photo)">
                                    Delete
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </template>
            </v-row>
        </template>
    </v-card>
</template>

<script>
export default {
    name: 'PartPhotoList',
    props: {
        ticketId: {
            required: true,
            type: String,
        },
        hideForm: Boolean,
        photos: Array,
    },
    computed: {
        items: {
            get() {
                return this.photos
            },
            set(payload) {
                this.$emit('update:photos', payload)
            }
        },
        baseUrl() {
            return BASE_URL;
        }
    },
    data() {
        return {
            loading: false,
            partListForm: {
                userfile: null,
            },
            allowPreviews: ['.jpeg','.jpg','.png']
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

            if (!this.partListForm.userfile) {
                return false;
            }

            this.loading = true;
            const form = new FormData();
            form.set("ticketId", this.ticketId);
            form.set('userfile', this.partListForm.userfile);

            this.$axios.post("tickets/add_workresult", form)
                .then(response => {
                    const { data } = response;

                    if (data.success) {
                        this.$coresnackbars.success(data.message);
                        this.partListForm.userfile = null;
                        this.$emit('uploaded')
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

            const confirmText = this.$sprintf(
                this.$t("confirm:remove_text"),
                item.name
            );
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios
                    .get("tickets/remove_workresult", {
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