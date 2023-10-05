<template>
    <v-card outlined>
        <v-card-title>
            <span>Account Info</span>
            <v-spacer></v-spacer>
            <v-btn icon small class="ma-0" @click="editAction">
                <v-icon>edit</v-icon>
            </v-btn>
        </v-card-title>
        <v-list dense>
            <v-list-item>
                <v-list-item-icon style="width: 150px">
                    <span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::email')}}</span>
                </v-list-item-icon>
                <v-list-item-content>
                    <v-list-item-title>{{item.email}}</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-divider></v-divider>
            <v-list-item>
                <v-list-item-icon style="width: 150px">
                    <span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::username')}}</span>
                </v-list-item-icon>
                <v-list-item-content>
                    <v-list-item-title>{{item.username}}</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-divider></v-divider>
            <v-list-item>
                <v-list-item-icon style="width: 150px">
                    <span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::last_login')}}</span>
                </v-list-item-icon>
                <v-list-item-content>
                    <v-list-item-title>{{item.lastLogin}}</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <!-- <v-list-item>
                <v-list-item-icon style="width: 150px">
                    <span class="subtitle-2 font-weight-regular grey--text text--darken-1">{{$t('profile::username_telegram')}}</span>
                </v-list-item-icon>
                <v-list-item-content>
                    <v-list-item-title>@{{item.telegramUser}}</v-list-item-title>
                </v-list-item-content>
            </v-list-item> -->
        </v-list>

        <template v-if="showModal">
            <v-dialog scrollable persistent
                v-model="showModal"
                max-width="500">
                <v-card :loading="loading" :disabled="loading">
                    <v-card-title>Edit account info</v-card-title>
                    <v-card-text>
                        <v-alert small type="warning" :value="true">
                            <strong>Warning</strong><br>Changing your email address will direct you to the login page to log in again
                        </v-alert>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="row.email"
                                    :disabled="loading"
                                    :error-messages="errorMsg.email"
                                    :hide-details="!errorMsg.email"
                                    :label="$t('profile::email')"
                                ></v-text-field>
                            </v-col>

                            <!-- <v-col cols="12">
                                <v-text-field
                                    v-model="row.telegramUser"
                                    :disabled="loading"
                                    :error-messages="errorMsg.telegramUser"
                                    :label="$t('profile::username_telegram')"
                                ></v-text-field>
                            </v-col> -->

                            <v-col cols="12">
                                <v-checkbox hide-details
                                    class="mt-0 pt-0"
                                    color="primary"
                                    v-model="row.passwordChange"
                                    label="Change password"
                                ></v-checkbox>
                            </v-col>

                            <v-col cols="12" v-if="row.passwordChange">
                                <v-text-field
                                    type="password"
                                    v-model="row.oldPassword"
                                    :disabled="loading"
                                    :error-messages="errorMsg.oldPassword"
                                    :hide-details="!errorMsg.oldPassword"
                                    label="Current password"
                                    placeholder="Enter current password"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6" v-if="row.passwordChange">
                                <v-text-field
                                    type="password"
                                    v-model="row.newPassword"
                                    :disabled="loading"
                                    :error-messages="errorMsg.newPassword"
                                    :hide-details="!errorMsg.newPassword"
                                    label="New password"
                                    placeholder="Enter new password"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6" v-if="row.passwordChange">
                                <v-text-field
                                    type="password"
                                    v-model="row.newPasswordConfirm"
                                    :disabled="loading"
                                    :error-messages="errorMsg.newPasswordConfirm"
                                    :hide-details="!errorMsg.newPasswordConfirm"
                                    label="Confirm new password"
                                    placeholder="Enter new password again"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-card-actions>
                        <v-btn color="primary" small depressed @click="saveChangeAction">
                            Save changes
                        </v-btn>
                        <v-btn color="grey darken-1" small outlined @click="showModal = false">
                            Cancel
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </template>
    </v-card>
</template>

<script>
export default {
    name: 'profile-account-card',
    props: {
        item: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            loading: false,
            showModal: false,
            row: {
                email: '',
                telegramUser: '',
                oldPassword: '',
                newPassword: '',
                newPasswordConfirm: '',
                passwordChange: false,
            },
            errorMsg: {
                email: '',
                telegramUser: '',
                oldPassword: '',
                newPassword: '',
                newPasswordConfirm: '',
            }
        }
    },
    methods: {
        editAction() {
            this.row = {
                email: this.item.email,
                telegramUser: this.item.telegramUser,
            };
            this.showModal = true;
        },
        clearErrorMsg() {
            this.errorMsg = {
                email: '',
                telegramUser: '',
                oldPassword: '',
                newPassword: '',
                newPasswordConfirm: '',
            }
        },
        saveChangeAction() {
            if (!this.showModal) {
                return false;
            }

            if (this.loading) {
                return false;
            }

            this.clearErrorMsg();
            this.loading = true;

            const item = new FormData();
            item.set('email', this.row.email);
            item.set('telegramUser', this.row.telegramUser);
            item.set('changePassword', this.row.passwordChange ? '1' : '0');
            item.set('oldPassword', this.row.oldPassword ? this.row.oldPassword : '');
            item.set('newPassword', this.row.newPassword ? this.row.newPassword : '');
            item.set('newPasswordConfirm', this.row.newPasswordConfirm ? this.row.newPasswordConfirm : '');

            this.$axios.post('profile/saveaccount', item)
                .then((response) => {
                    const { data } = response;
                    this.$coresnackbars.success(data.message);

                    if (data.success) {
                        this.$emit('save-success');
                        this.showModal = false;
                    }

                    this.loading = false;
                })
                .catch((error) => {
                    console.log(error);
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
