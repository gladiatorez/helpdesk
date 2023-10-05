<template>
  <v-container fluid class="pa-5">
    <v-row justify="center">
      <v-col cols="12" md="10" lg="10">
        <v-card outlined :loading="loading">
          <v-card-text>
            <v-list dense class="mb-6">
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">Nomor</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{ item.letter_no + item.letter_no_suffix }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">Tanggal</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{ item.letter_date }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">Subject</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{ item.letter_subject }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">User</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{ item.ticket_informer_full_name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ ticketSbu }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">No. Tiket</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{ `#${item.ticket_number}` }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-action class="mt-1 mb-0 mr-2 align-self-start" style="width: 120px">
                  <v-list-item-action-text class="subtitle-2">Serial Number</v-list-item-action-text>
                </v-list-item-action>
                <v-list-item-content>
                  <v-list-item-title>: &nbsp;&nbsp;{{  item.serial_number }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list>

            <v-card outlined flat color="grey lighten-4" class="mb-4">
              <v-card-title>A. Latar Belakang</v-card-title>
              <v-card-text class="black--text" v-html="item.descr_background"></v-card-text>
            </v-card>

            <v-card outlined flat color="grey lighten-4" class="mb-4">
              <v-card-title>B. Pemeriksaan</v-card-title>
              <v-card-text class="black--text" v-html="item.descr_examination"></v-card-text>
            </v-card>

            <v-card outlined flat color="grey lighten-4" class="mb-4">
              <v-card-title>C. Penanganan Masalah</v-card-title>
              <v-card-text class="black--text" v-html="item.descr_handling"></v-card-text>
            </v-card>

            <v-card outlined flat color="grey lighten-4" class="mb-4">
              <v-card-title>D. Hasil</v-card-title>
              <v-card-text class="black--text" v-html="item.descr_results"></v-card-text>
            </v-card>

            <v-card outlined flat color="grey lighten-4" class="mb-4">
              <v-card-title>E. Rekomendasi</v-card-title>
              <v-card-text class="black--text" v-html="item.descr_recomendation"></v-card-text>
            </v-card>

            <v-card outlined flat 
              v-if="item.photos.length > 0"
              color="grey lighten-4" 
              class="mb-4">
              <v-card-title>Photos</v-card-title>
              <v-card-text class="black--text">
                <v-row>
                  <v-col 
                    v-for="(photo,index) in item.photos"
                    cols="12" md="6"
                    :key="index">
                    <v-card flat outlined>
                      <div style="height: 200px; overflow: hidden" class="text-center blue-grey lighten-4">
                        <img 
                          :src="siteUrl + 'files/large/' + photo.file" 
                          style="height: 100%"
                        />
                      </div>
                      <v-card-text>
                        {{ photo.description }}
                      </v-card-text>
                      <v-card-actions>
                        <v-btn small depressed color="error" @click="removePhoto(photo)">
                          Delete
                        </v-btn>
                      </v-card-actions>
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <v-row>
              <v-col cols="12" md="4">
                <v-card outlined color="grey lighten-4">
                  <v-card-title class="body-2 white">Disetujui oleh</v-card-title>
                  <v-card-text class="white">
                    <div class="font-weight-bold black--text">{{ item.approve_full_name }}</div>
                    <div>{{ item.approve_position }}</div>
                  </v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" md="4">
                <v-card outlined color="grey lighten-4">
                  <v-card-title class="body-2 white">Dibuat oleh</v-card-title>
                  <v-card-text class="white">
                    <div class="font-weight-bold black--text">{{ item.maker_full_name }}</div>
                    <div>{{ item.maker_position }}</div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'recomendations-view',
  data() {
    return {
      loading: false,
      item: {
        id: '',
        letter_no: '',
        letter_no_suffix: '',
        letter_subject: '',
        letter_date: '',
        serial_number: '',
        ticket_id: '',
        ticket_number: '',
        ticket_subject: '',
        ticket_informer_full_name: '',
        ticket_company_name: '',
        ticket_department_name: '',
        ticket_company_branch_name: '',
        descr_background: '',
        descr_examination: '',
        descr_handling: '',
        descr_results: '',
        descr_recomendation: '',
        maker_full_name: '',
        maker_position: '',
        approve_full_name: '',
        approve_position: '',
        approve_user_id: '',
        photos: [],
      },
    }
  },
  computed: {
    ticketSbu() {
      let arr = [];
      if (this.item.ticket_company_name) {
        arr.push(this.item.ticket_company_name);
      }
      if (this.item.ticket_company_branch_name) {
        arr.push(this.item.ticket_company_branch_name);
      }
      if (this.item.ticket_department_name) {
        arr.push(this.item.ticket_department_name);
      }
      return arr.join(' - ');
    },
    siteUrl() {
      return window.BASE_URL
    }
  },
  created() {
    this.$root.$on('page-header:back-action', this.closeAction);
    this.$root.$on('page-header:print-action', this.printAction);

    if (this.$route.params.id) {
      this.item.id = this.$route.params.id;
    }

    this.initForm();
  },
  destroyed() {
    this.$root.$off('page-header:back-action', this.closeAction);
    this.$root.$off('page-header:print-action', this.printAction);
  },
  methods: {
    async initForm() {
      this.loading = true;
      await this.fetchItem();
      this.loading = false;
    },
    closeAction() {
      this.$router.push({
        name: 'recomendations.index',
        params: {
          refresh: true
        }
      });
    },
    printAction() {
      window.open(
        window.SITE_URL + '/recomendations/index?id=' + this.item.id, '_blank'
      );
    },
    fetchItem() {
      return this.$axios.get('recomendations/item', { params: {id: this.item.id} })
        .then(response => {
          const { data } = response;
          const { row } = data;

          this.item = {
            id: row.id,
            letter_no: row.letter_no,
            letter_no_suffix: row.letter_no_suffix,
            letter_subject: row.letter_subject,
            letter_date: row.letter_date,
            serial_number: row.serial_number,
            ticket_id: row.ticket_id,
            ticket_number: row.ticket_number,
            ticket_subject: row.ticket_subject,
            ticket_informer_full_name: row.ticket_informer_full_name,
            ticket_company_name: row.ticket_company_name,
            ticket_department_name: row.ticket_department_name,
            ticket_company_branch_name: row.ticket_company_branch_name,
            descr_background: row.descr_background,
            descr_examination: row.descr_examination,
            descr_handling: row.descr_handling,
            descr_results: row.descr_results,
            descr_recomendation: row.descr_recomendation,
            maker_full_name: row.maker_full_name,
            maker_position: row.maker_position,
            approve_full_name: row.approve_full_name,
            approve_position: row.approve_position,
            approve_user_id: row.approve_user_id,
            photos: row.photos ? row.photos : []
          };

          if (typeof row.keywords === "string") {
            this.item.keywords = row.keywords.split(',');
          } else if (typeof row.keywords === "object") {
            this.item.keywords = row.keywords;
          }
        })
        .catch(error => {
          const {statusText, data} = error;
          if (typeof data.message !== "undefined") {
            this.$coresnackbars.error(data.message);
          } else {
            this.$coresnackbars.error(statusText);
          }
        });
    },

    removePhoto(photo) {
      if (this.loading) {
        return false;
      }

      const confirmText = 'Are you sure wants to remove a photo?';
      if (confirm(confirmText)) {
        this.loading = true;
        const that = this;
        this.$axios.get('recomendations/remove_photo/', {
          params: {
            id: photo.id,
            recomendation: that.item.id
          }
        })
          .then(response => {
            const {data} = response;
            if (data.success) {
              that.$coresnackbars.success(data.message);
              this.initForm();
            }
            else {
              that.$coresnackbars.error(data.message);
            }
          })
          .catch(function (error) {
            const { statusText, status } = error;
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
