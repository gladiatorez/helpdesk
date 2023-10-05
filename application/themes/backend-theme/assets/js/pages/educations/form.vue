<template>
    <v-container fluid class="pa-5">
        <v-row justify="center">
            <v-col cols="12" md="6">
                <v-card :loading="loading" :disabled="loading" outlined>
                    <v-card-title class="font-weight-bold">Education Image</v-card-title>
                    <v-divider/>
                    <v-card-text>
                        <v-checkbox
                            color="primary"
                            :label="$t('educations::is_headline')"
                            v-model="item.isHeadline"
                            :false-value="false"
                            :true-value="true"
                        ></v-checkbox>

                        <v-text-field
                            v-model="item.title"
                            :disabled="loading"
                            :error-messages="errorMsg.title"
                            :label="$t('educations::title')"
                        ></v-text-field>

                        <v-card flat class="mt-6" color="grey lighten-4" style="height: 200px;">
                            <template v-if="imagePreview">
                                <v-hover>
                                    <template v-slot:default="{hover}">
                                        <v-img
                                            width="100%"
                                            height="100%"
                                            contain
                                            :src="imagePreview">
                                                <div v-if="hover" class="fill-height d-flex align-center justify-center">
                                                    <v-btn color="error" @click="clearImage">
                                                        Clear
                                                    </v-btn>
                                                </div>
                                        </v-img>
                                    </template>
                                </v-hover>
                            </template>
                            <template v-else>
                                <div class="d-flex align-center justify-center" style="height: 100%; position: relative">
                                    <v-btn large rounded depressed outlined color="grey lighten-1">
                                        <span class="primary--text">Add Image</span>
                                    </v-btn>
                                    <input type="file" style="font-size: 100px; position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0;" @change="onImageChanged" />
                                </div>
                            </template>
                        </v-card>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: 'education-form',
    data() {
        return {
            loading: false,
            imagePreview: '',
            item: {
                id: '',
                title: '',
                image: null,
                isHeadline: true,
            },
            errorMsg: {
                id: '',
                title: '',
                image: '',
                isHeadline: '',
            },
        }
    },
    computed: {
        title() {
            return this.$t('educations::heading_new');
        },
    },
    created() {
        this.$root.$on('page-header:back-action', this.closeAction);
        this.$root.$on('page-header:save-close-action', this.saveChanges);
    },
    destroyed() {
        this.$root.$off('page-header:back-action', this.closeAction);
        this.$root.$off('page-header:save-close-action', this.saveChanges);
    },
    methods: {
        closeAction() {
            return this.$router.push({
                name: 'educations.index',
                params: {
                    refresh: true
                }
            });
        },
        clearErrorMsg() {
            this.errorMsg = {
                id: '',
                title: '',
                isHeadline: '',
            };
        },
        setEmptyItem() {
            this.item = {
                id: '',
                title: '',
                isHeadline: true,
            };
        },
        onImageChanged(evt) {
            const file = evt.target.files ? evt.target.files[0] : null

            if (file) {
                this.item.image = file

                const reader = new FileReader();
                const that = this;
                reader.addEventListener('load', () => {
                    that.imagePreview = reader.result
                }, false)

                reader.readAsDataURL(file)
            }
        },
        clearImage() {
            this.item.image = null;
            this.imagePreview = ''
        },
        saveChanges() {
            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.append('title', this.item.title);
            item.append('image', this.item.image || '')
            item.append('isHeadline', this.item.isHeadline ? '1' : '0');

            const url = 'educations/create';
            this.$axios.post(url, item)
                .then((response) => {
                    const {data} = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        this.$router.push({
                            name: 'educations.index',
                            params: {refresh: true}
                        });
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
