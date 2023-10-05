<template>
    <v-container fluid class="pa-5">
        <v-card flat>
            <v-card-title>
                <v-btn rounded small
                       v-if="$can('create', $route.meta.module)"
                       color="primary"
                       :to="{name: 'educations.create'}">
                    <v-icon left>add</v-icon>
                    {{ $t('btn::create') }}
                </v-btn>
            </v-card-title>

            <v-data-table
                sort-by="isHeadline"
                :sort-desc="true"
                :headers="headers"
                :items="items"
                :server-items-length="totalItems"
                :options.sync="tableOptions"
                :loading="loading">
                <template v-slot:item.id="{ item }">
                    <core-more-menu
                        :remove-btn="$can('remove', $route.meta.module)"
                        @remove-action="removeAction(item)">
                        <template v-if="$can('edit', $route.meta.module)">
                            <v-list-item @click="showEditTitleDialog(item)">
                                <v-list-item-icon class="mr-3" />
                                <v-list-item-content>
                                    <v-list-item-title>Edit title</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <v-list-item v-if="item.isHeadline" @click="disableAsHeadline(item)">
                                <v-list-item-icon class="mr-3" />
                                <v-list-item-content>
                                    <v-list-item-title>Disable as headline</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <v-list-item v-if="!item.isHeadline" @click="markAsHeadline(item)">
                                <v-list-item-icon class="mr-3" />
                                <v-list-item-content>
                                    <v-list-item-title>Mark as headline</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider />
                        </template>
                    </core-more-menu>
                </template>

                <template v-slot:item.title="{ item }">
                    <div class="d-flex align-center">
                        <v-img
                            width="50px"
                            max-width="50px"
                            :src="baseUrl + 'files/thumb/' + item.file"
                            @click="openImage(item.file, item.title)"
                        />
                        <span class="d-inline-block ml-6 text-truncate">{{ item.title }}</span>
                    </div>
                </template>

                <template v-slot:item.isHeadline="{ item }">
                    <v-checkbox v-model="item.isHeadline" color="primary" class="mt-0" readonly hide-details></v-checkbox>
                </template>

                <template v-slot:item.updatedAt="{ item }">
                  <span title="DD/MM/YYYY HH:mm">
                    {{ $moment(item.updaatedAt).format('DD/MM/YYYY HH:mm') }}
                  </span>
                </template>
            </v-data-table>
        </v-card>

        <v-dialog
            v-model="dialogImage.show"
            scrollable
            content-class="elevation-0"
            max-width="800">
            <v-img
                width="100%"
                max-width="100%"
                contain
                style="position: relative"
                :src="dialogImage.src">
                <div class="pa-3">
                    <v-btn small color="error" @click="dialogImage.show = false">
                        Close
                    </v-btn>
                </div>
                <div style="position: absolute; bottom: 0; width: 100%; background: rgba(0, 0, 0, 0.5)" class="pa-3 white--text">
                    <span class="body-2 font-weight-medium">{{ dialogImage.title }}</span>
                </div>
            </v-img>
        </v-dialog>

        <v-dialog
            v-model="dialogEditTitle.show"
            max-width="350"
            persistent>
            <v-card :loading="loading" :disabled="loading">
                <v-card-title>
                    Edit title
                </v-card-title>
                <v-card-text>
                    <v-text-field
                        v-model="dialogEditTitle.title"
                        label="Title"
                        :error-messages="dialogEditTitle.errorTitle"
                    />
                </v-card-text>
                <v-card-actions>
                    <v-btn color="primary" @click="saveEditTitleAction">
                        Save
                    </v-btn>
                    <v-btn text color="primary" @click="dialogEditTitle.show = false">
                        Cancel
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import {fetchDtRows} from '../../utils/helpers';

export default {
    name: 'education-index-page',
    data() {
        return {
            headers: [
                {
                    text: "",
                    value: 'id',
                    sortable: false,
                    width: '40px'
                },
                {
                    text: this.$t('educations::title'),
                    value: 'title',
                },
                {
                    text: this.$t('educations::is_headline'),
                    value: 'isHeadline',
                },
                {
                    text: this.$t('lb::updated_at'),
                    value: 'updatedAt',
                    width: '160px'
                },
            ],
            tableOptions: {},
            items: [],
            totalItems: 0,
            loading: false,
            searchText: '',
            dialogImage: {
                show: false,
                src: '',
                title: ''
            },
            dialogEditTitle: {
                show: false,
                id: '',
                title: '',
                errorTitle: '',
                loading: false
            }
        }
    },
    computed: {
        baseUrl() {
            return BASE_URL;
        }
    },
    watch: {
        $route: function (route) {
            if (route.params.refresh) {
                this.refreshAction();
            }
        },
        tableOptions() {
            this.refreshAction();
        }
    },
    created() {
        this.$root.$on('page-header:refresh-action', this.refreshAction);
        this.$root.$on('page-header:search-action', this.searchAction);
        this.$root.$on('page-header:search-cancel-action', this.searchClearAction);
    },
    destroyed() {
        this.$root.$off('page-header:refresh-action', this.refreshAction);
        this.$root.$off('page-header:search-action', this.searchAction);
        this.$root.$off('page-header:search-cancel-action', this.searchClearAction);
    },
    methods: {
        searchAction(payload) {
            this.searchText = payload;
            this.refreshAction();
        },
        searchClearAction() {
            this.searchText = '';
            this.refreshAction();
        },
        openImage(file, title) {
            this.dialogImage.src = this.baseUrl + 'files/large/' + file
            this.dialogImage.title = title
            this.dialogImage.show = true
        },
        refreshAction() {
            this.loading = true;
            fetchDtRows('educations', this.tableOptions, this.searchText).then(data => {
                this.items = data.items
                this.totalItems = data.total
            }).catch(error => {
                this.loading = false;
            }).then(() => {
                this.loading = false;
            })
        },
        disableAsHeadline(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = 'Are you absolutely sure';
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                const form = new FormData();
                form.append('id', item.id);
                this.$axios.post('educations/disabled_as_headline/', form)
                    .then(response => {
                        const {data} = response;

                        that.$coresnackbars.success(data.message);

                        if (data.success) {
                            that.refreshAction();
                        }
                    })
                    .catch(function (error) {
                        const {statusText, status} = error;
                        that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
                    })
                    .then(() => {
                        that.loading = false;
                    });
            }
        },
        markAsHeadline(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = 'Are you absolutely sure';
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                const form = new FormData();
                form.append('id', item.id);
                this.$axios.post('educations/mark_as_headline/', form)
                    .then(response => {
                        const {data} = response;

                        that.$coresnackbars.success(data.message);

                        if (data.success) {
                            that.refreshAction();
                        }
                    })
                    .catch(function (error) {
                        const {statusText, status} = error;
                        that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
                    })
                    .then(() => {
                        that.loading = false;
                    });
            }
        },
        showEditTitleDialog(item) {
            if (this.loading) {
                return false;
            }

            this.dialogEditTitle.loading = false
            this.dialogEditTitle.id = item.id
            this.dialogEditTitle.title = item.title
            this.dialogEditTitle.errorTitle = ''
            this.dialogEditTitle.show = true
        },
        saveEditTitleAction() {
            if (this.loading || this.dialogEditTitle.loading) {
                return false;
            }

            this.dialogEditTitle.errorTitle = ''
            const confirmText = 'Are you absolutely sure';
            if (confirm(confirmText)) {
                this.loading = true;
                this.dialogEditTitle.loading = true
                const that = this;
                const form = new FormData();
                form.append('id', this.dialogEditTitle.id);
                form.append('title', this.dialogEditTitle.title);
                this.$axios.post('educations/edit_title/', form)
                    .then(response => {
                        const {data} = response;

                        that.$coresnackbars.success(data.message);

                        if (data.success) {
                            this.dialogEditTitle.id = ''
                            this.dialogEditTitle.title = ''
                            this.dialogEditTitle.errorTitle = ''
                            this.dialogEditTitle.show = false

                            that.refreshAction();
                        }
                    })
                    .catch(function (error) {
                        const {statusText, status, data} = error;
                        that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
                        
                        if (typeof data !== "undefined" && typeof data.message !== "undefined") {
                            if (typeof data.message === 'object') {
                                that.dialogEditTitle.errorTitle = data.message.title
                                    ? data.message.title
                                    : data.message.id ? data.message.id : '';
                            }
                        }
                    })
                    .then(() => {
                        that.loading = false;
                        that.dialogEditTitle.loading = false;
                    });
            }
        },
        removeAction(item) {
            if (this.loading) {
                return false;
            }

            const confirmText = this.$sprintf(this.$t('confirm:remove_text'), item.title);
            if (confirm(confirmText)) {
                this.loading = true;
                const that = this;
                this.$axios.get('educations/remove/', {
                    params: {id: item.id}
                })
                    .then(response => {
                        const {data} = response;

                        that.$coresnackbars.success(data.message);

                        if (data.success) {
                            that.refreshAction();
                        }
                    })
                    .catch(function (error) {
                        const {statusText, status} = error;
                        that.$coresnackbars.error('Code: ' + status + ' ' + statusText);
                    })
                    .then(() => {
                        that.loading = false;
                    });
            }
        }
    }
}
</script>