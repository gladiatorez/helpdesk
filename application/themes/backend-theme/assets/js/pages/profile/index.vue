<template>
  <v-row class="fill-height ma-0"
    align="center"
    justify="center">
    <v-col cols="12" md="6">
      <general-card class="mb-4" :item="item" @save-success="saveSuccess"></general-card>
      <company-card class="mb-4" :item="item" @save-success="saveSuccess"></company-card>
      <account-card class="mb-4" :item="item" @save-success="saveSuccess"></account-card>
    </v-col>
  </v-row>
</template>

<script>
import GeneralCard from './includes/GeneralCard';
import CompanyCard from './includes/CompanyCard';
import AccountCard from './includes/AccountCard';

export default {
  name: 'profile-page',
  components: {
    GeneralCard, CompanyCard, AccountCard
  },
  data() {
    return {
      loading: false,
      item: {
        fullName: '',
        phone: '',
        nik: '',
        position: '',
        companyId: '',
        companyName: '',
        email: '',
        username: '',
        telegramUser: '',
        lastLogin: '',
        oldPassword: '',
        newPassword: '',
        confirNewPassword: '',
        changePassword: false,
      },
      errorMsg: {
        fullName: '',
        phone: '',
        nik: '',
        position: '',
        companyId: '',
        companyName: '',
        email: '',
        username: '',
        telegramUser: '',
        oldPassword: '',
        newPassword: '',
        confirNewPassword: '',
      },
    }
  },
  created() {
    this.fetchItem();
  },
  methods: {
    fetchItem() {
      if (this.loading) {
        return false;
      }

      this.loading = true;
      this.$axios.get('profile/index')
        .then(response => {
          const { data } = response;
          const row = data.row;

          this.item = {
            fullName: row.fullName,
            phone: row.phone ? row.phone : '',
            nik: row.nik ? row.nik : '',
            position: row.position ? row.position :'',
            companyId: row.companyId ? row.companyId : '',
            companyName: row.companyName ? row.companyName : '',
            email: row.email ? row.email : '',
            username: row.username ? row.username : '',
            telegramUser: row.telegramUser ? row.telegramUser : '',
            lastLogin: row.lastLogin ? row.lastLogin : '',
            oldPassword: '',
            newPassword: '',
            confirNewPassword: '',
            changePassword: false
          };

        })
        .catch(error => {
          const {statusText, data} = error;
          if (typeof data.message !== "undefined") {
            this.$toasted.error(data.message, {
              fullWidth: true,
              fitToScreen: true,
            });
          } else {
            this.$toasted.error(statusText, {
              fullWidth: true,
              fitToScreen: true,
            });
          }
        })
        .then(() => {
          this.loading = false;
        });
    },
    saveSuccess() {
      this.fetchItem();
    }
  }
}
</script>

<style lang="scss">
.profile-card-list {
  .v-list__tile__avatar {
    flex-basis: 156px;
  }
  .v-list__tile__title {
    font-weight: 700;
  }
}
</style>
