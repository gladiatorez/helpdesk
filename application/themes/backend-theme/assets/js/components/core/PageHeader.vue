<template>
    <div class="app-page-header">
        <div class="page-header-title mr-3">
            {{title}}
        </div>

        <template v-if="shortcutBack">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon small
                        v-on="on"
                        @click="backAction"
                        class="mr-1">
                        <v-icon style="font-size: 16px;">ms-Icon ms-Icon--SkypeArrow</v-icon>
                    </v-btn>
                </template>
                <span class="caption">{{$t('btn::back')}}</span>
            </v-tooltip>
        </template>

        <template v-if="shortcutPrint">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon small
                        v-on="on"
                        @click="printAction"
                        class="mr-1">
                        <v-icon style="font-size: 16px;" class="font-weight-bold">ms-Icon ms-Icon--Print</v-icon>
                    </v-btn>
                </template>
                <span class="caption">{{$t('btn::print')}}</span>
            </v-tooltip>
        </template>

        <template v-if="shortcutRefresh">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon small
                        v-on="on"
                        @click="refreshAction"
                        class="mr-1">
                        <v-icon style="font-size:16px;">ms-Icon ms-Icon--Refresh</v-icon>
                    </v-btn>
                </template>
                <span class="caption">{{$t('btn::refresh')}}</span>
            </v-tooltip>
        </template>

        <template v-if="shortcutSearch">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon small
                        v-on="on"
                        @click="dialogSearchOpen"
                        class="mr-1">
                        <v-badge 
                            dot right 
                            v-model="dialogSearchActive" 
                            color="primary"
                            offset-x="10"
                            offset-y="10">
                            <span slot="badge">&nbsp;</span>
                            <v-icon style="font-size:16px;">ms-Icon ms-Icon--Search</v-icon>
                        </v-badge>
                    </v-btn>
                </template>
                <span class="caption">{{$t('btn::search')}}</span>
            </v-tooltip>
        </template>

        <v-dialog v-model="dialogSearch" v-if="shortcutSearch"
            width="500">
            <v-card>
                <v-card-title>
                    <v-text-field
                        hide-details single-line solo flat
                        prepend-icon="search"
                        placeholder="Search"
                        v-model="dialogSearchText"
                        v-on:keyup.enter="dialogSearchOk"
                        v-on:keyup.esc="dialogSearchEsc"
                    />
                    <v-btn small @click="dialogSearchOk">
                        OK
                    </v-btn>
                </v-card-title>
            </v-card>
        </v-dialog>

        <v-spacer></v-spacer>
        <template v-if="shortcutSaveClose">
            <v-btn small rounded  
                color="primary"
                @click="saveCloseAction">
                {{$t('btn::save_close')}}
            </v-btn>
        </template>
        <template v-if="shortcutCreate">
            <v-btn small rounded 
                color="primary"
                @click="createAction">
                {{$t('btn::create')}}
            </v-btn>
        </template>
    </div>
</template>

<script>
export default {
    name: 'CorePageHeader',
    data() {
        return {
            dialogSearch: false,
            dialogSearchText: '',
            dialogSearchActive: false,
        }
    },
    computed: {
        title() {
            return this.$t(this.$route.meta.title);
        },
        shortcutBack () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('back') >= 0) {
                    return true
                }
            }

            return false
        },
        shortcutPrint () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('print') >= 0) {
                    return true
                }
            }

            return false
        },
        shortcutSearch () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('search') >= 0) {
                    return true
                }
            }

            return false
        },
        shortcutRefresh () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('refresh') >= 0) {
                    return true
                }
            }

            return false
        },
        shortcutSaveClose () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('saveClose') >= 0) {
                    return true
                }
            }

            return false
        },
        shortcutCreate () {
            if (typeof this.$route.meta.shortcut !== 'undefined') {
                if (this.$route.meta.shortcut.indexOf('create') >= 0) {
                    return true
                }
            }

            return false
        },
    },
    methods: {
        dialogSearchOpen() {
            this.dialogSearch = true
        },
        dialogSearchOk() {
            if (this.dialogSearchText.length) {
                this.dialogSearchActive = true;
            } else {
                this.dialogSearchActive = false;
            }
            this.dialogSearch = false;
            this.$root.$emit('page-header:search-action', this.dialogSearchText);
        },
        dialogSearchEsc() {
            if (this.dialogSearchText.length) {
                this.dialogSearchText = '';
            } else {
                this.dialogSearch = false;
            }

            this.dialogSearchActive = false;
            this.$root.$emit('page-header:search-cancel-action');
        },
        refreshAction() {
            this.$root.$emit('page-header:refresh-action');
        },
        backAction() {
            this.$root.$emit('page-header:back-action');
        },
        saveCloseAction() {
            this.$root.$emit('page-header:save-close-action');
        },
        createAction() {
            this.$root.$emit('page-header:create-action');
        },
        printAction() {
            this.$root.$emit('page-header:print-action');
        }
    }
}
</script>
