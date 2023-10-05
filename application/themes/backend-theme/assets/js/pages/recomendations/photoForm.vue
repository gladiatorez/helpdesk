<template>
  <v-container fluid class="pa-5">
    <span class="error--text caption">** All fields is required</span>
    <v-row>
      <v-col cols="12" md="3">
        <v-card 
          :loading="loading" 
          :disabled="loading" 
          class="mb-6">
          <v-card-title class="primary--text">Heading</v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12">
                <v-text-field outlined disabled
                  v-model="item.letter_no"
                  :error-messages="errorMsg.letter_no"
                  :hide-details="!errorMsg.letter_no"
                  :label="$t('recomendations::letter_no')"
                  :suffix="letterNoSuffix"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <app-datepicker outlined  disabled
                  v-model="item.letter_date"
                  :error-messages="errorMsg.letter_date"
                  :hide-details="!errorMsg.letter_date"
                  :label="$t('recomendations::letter_date')"
                />
              </v-col>
              <v-col cols="12">
                <v-text-field outlined  disabled
                  v-model="item.letter_subject"
                  :error-messages="errorMsg.letter_subject"
                  :hide-details="!errorMsg.letter_subject"
                  :label="$t('recomendations::letter_subject')"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <app-autocomplete-ajax outlined disabled
                  v-model="item.ticket"
                  url="recomendations/tickets"
                  item-text="number"
                  item-value="id"
                  :item-extend="['informer_full_name','company_name','company_branch_name','department_name','subject']"
                  :error-messages="errorMsg.ticket"
                  :hide-details="!errorMsg.ticket"
                  :label="$t('recomendations::ticket_no')" 
                />
                <v-slide-x-transition>
                  <div v-show="item.ticket" class="mt-4">
                    <template v-if="item.ticket">
                      <div>Subject ticket</div>
                      <div class="black--text font-weight-bold">{{ item.ticket.subject }}</div>
                    </template>
                    <template v-if="item.ticket">
                      <div>User</div>
                      <div class="black--text font-weight-bold">{{ item.ticket.informer_full_name }}</div>
                    </template>
                    <template v-if="ticketSbu">
                      <div>SBU</div>
                      <div class="black--text font-weight-bold">
                        {{ ticketSbu }}
                      </div>
                    </template>
                  </div>
                </v-slide-x-transition>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <v-card 
          :loading="loading" 
          :disabled="loading" 
          class="mb-6">
          <v-list subheader dense>
            <v-subheader>Maker</v-subheader>
            <v-list-item>
              <core-avatar-initial 
                class="mr-3"
                color="blue-grey darken-3"
                text-color="white--text headline"
                :title="maker.fullName"
              />
              <v-list-item-content>
                <v-list-item-title>{{ maker.fullName }}</v-list-item-title>
                <v-list-item-subtitle>{{ maker.position }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>

            <v-subheader>Approved by</v-subheader>
            <v-list-item>
              <core-avatar-initial 
                class="mr-3"
                color="blue-grey darken-3"
                text-color="white--text headline"
                :title="approvedBy.fullName"
              />
              <v-list-item-content>
                <v-list-item-title>{{ approvedBy.fullName }}</v-list-item-title>
                <v-list-item-subtitle>{{ approvedBy.position }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="9">
        <v-card>
          <v-card-title>
            <v-btn small depressed color="primary" class="mr-2" @click="addPhoto">Add</v-btn>
            <v-btn small depressed color="error" @click="clearPhoto">Clear</v-btn>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col 
                v-for="(photo, index) in photos"
                cols="12" md="6"
                :key="index">
                <v-card outlined flat>
                  <v-card-title class="font-weight-normal">
                    <v-file-input 
                      dense hide-details single-line outlined
                      accept="image/png, image/jpeg, image/jpg"
                      placeholder="Choose a photo" 
                      class="mr-1"
                      prepend-icon=""
                      prepend-inner-icon="image"
                      v-model="photo.file"
                      @change="photoSelected(index, $event)"
                    />
                    <v-btn icon small
                      color="error"
                      @click="removePhoto(index)">
                      <v-icon>delete</v-icon>
                    </v-btn>
                  </v-card-title>
                  <v-card-text>
                    <v-text-field 
                      dense single-line outlined hide-details
                      placeholder="Enter Description"
                      prepend-inner-icon="description"
                      v-model="photo.descr"
                    />
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
import AppDatepicker from '../../components/AppDatepicker.vue';
import AppAutocompleteAjax from '../../components/AppAutocompleteAjax.vue';

export default {
  name: 'recomendations-form',
  components: {
    AppDatepicker,
    AppAutocompleteAjax
  },
  data() {
    return {
      loading: false,
      item: {
        id: '',
        letter_no: '',
        letter_date: '',
        letter_subject: '',
        letter_attach: 0,
        descr_background: '',
        descr_examination: '',
        descr_handling: '',
        descr_results: '',
        descr_recomendation: '',
        ticket: null,
      },
      errorMsg: {
        id: '',
        letter_no: '',
        letter_date: '',
        letter_subject: '',
        letter_attach: '',
        descr_background: '',
        descr_examination: '',
        descr_handling: '',
        descr_results: '',
        descr_recomendation: '',
        ticket: '',
      },
      maker: {
        fullName: '',
        position: ''
      },
      approvedBy: {
        fullName: '',
        position: ''
      },
      photos: [{
        file: null,
        descr: ''
      }], 
    }
  },
  computed: {
    letterNoSuffix() {
      if (this.item.letter_date) {
        const month = this.$moment(this.item.letter_date).format('M');
        const year = this.$moment(this.item.letter_date).format('YYYY');

        return `/HSI/DIV-ID/${this.translateRomanNumeral(month)}/${year}`
      }

      return '/HSI/DIV-ID/__/____';
    },
    ticketSbu() {
      if (!this.item.ticket) {
        return '';
      }

      let arr = [];
      if (this.item.ticket.company_name) {
        arr.push(this.item.ticket.company_name);
      }
      if (this.item.ticket.company_branch_name) {
        arr.push(this.item.ticket.company_branch_name);
      }
      if (this.item.ticket.department_name) {
        arr.push(this.item.ticket.department_name);
      }
      return arr.join(' - ');
    }
  },
  created() {
    this.$root.$on('page-header:back-action', this.closeAction);
    this.$root.$on('page-header:save-close-action', this.addCloseAction);

    this.item.letter_date = this.$moment().format('YYYY-MM-DD');
    if (this.$route.params.id) {
      this.item.id = this.$route.params.id;
    }

    this.initForm();
  },
  destroyed() {
    this.$root.$off('page-header:back-action', this.closeAction);
    this.$root.$off('page-header:save-close-action', this.addCloseAction);
  },
  methods: {
    async initForm() {
      this.loading = true;
      await this.fetchApproved();
      await this.fetchItem();
      this.loading = false;
    },
    translateRomanNumeral(numeral) {
      const DIGIT_VALUES = {
        1: 'I',
        2: 'II',
        3: 'III',
        4: 'IV',
        5: 'V',
        6: 'VI',
        7: 'VII',
        8: 'VIII',
        9: 'IX',
        10: 'X',
        11: 'XI',
        12: 'XII',
      };

      if (!DIGIT_VALUES[numeral]) {
        return null
      }

      return DIGIT_VALUES[numeral];
    },
    closeAction() {
      return this.$router.push({
        name: 'recomendations.index',
        params: {
          refresh: true
        }
      });
    },
    clearErrorMsg() {
      this.errorMsg = {
        id: '',
        letter_no: '',
        letter_date: '',
        letter_subject: '',
        letter_attach: '',
        descr_background: '',
        descr_examination: '',
        descr_handling: '',
        descr_results: '',
        descr_recomendation: '',
        ticket: '',
      };
    },
    setEmptyItem() {
      this.item = {
        id: '',
        letter_no: '',
        letter_date: '',
        letter_subject: '',
        letter_attach: '',
        descr_background: '',
        descr_examination: '',
        descr_handling: '',
        descr_results: '',
        descr_recomendation: '',
        ticket: null,
      };
    },
    fetchApproved() {
      const that = this;
      that.categoryOptions = [];
      that.$axios.get('recomendations/approved_by')
        .then(response => {
          const { data } = response;
          if (data.maker) {
            this.maker.fullName = data.maker.full_name;
            this.maker.position = data.maker.position;
          }

          if (data.approved) {
            this.approvedBy.fullName = data.approved.full_name;
            this.approvedBy.position = data.approved.position;
          }
        })
        .catch(error => {
          console.error(error);
        });
    },
    fetchItem() {
      return this.$axios.get('recomendations/item', { params: {id: this.item.id} })
        .then(response => {
          const { data } = response;
          const { row } = data;

          this.item = {
            id: row.id,
            letter_no: row.letter_no,
            letter_date: row.letter_date,
            letter_subject: row.letter_subject,
            letter_attach: parseInt(row.letter_attach),
            descr_background: row.descr_background,
            descr_examination: row.descr_examination,
            descr_handling: row.descr_handling,
            descr_results: row.descr_results,
            descr_recomendation: row.descr_recomendation,
            ticket: {
              id: row.ticket_id,
              number: row.ticket_number,
              informer_full_name: row.ticket_informer_full_name,
              company_name: row.ticket_company_name,
              company_branch_name: row.ticket_company_branch_name,
              department_name: row.ticket_department_name,
              subject: row.ticket_subject
            },
          };

          this.maker.fullName = row.maker_full_name;
          this.maker.position = row.maker_position;
          this.approvedBy.fullName = row.approve_full_name;
          this.approvedBy.position = row.approve_position;
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
    addCloseAction() {
      this.saveChanges('saveClose')
    },
    saveChanges(mode) {
      if (this.loading) {
        return false;
      }

      this.clearErrorMsg();
      this.loading = true;

      const formData = new FormData();
      formData.append('id', this.item.id);
      const that = this;
      let fileCount = 0
      for (let index = 0; index < that.photos.length; index++) {
        const photo = that.photos[index];
        if (photo.file) {
          formData.append('photos_'+index+'_file', photo.file);
          formData.append('photos_'+index+'_descr', photo.descr);
          fileCount = fileCount + 1;
        }
      }
      formData.append('fileCount', fileCount);

      this.$axios.post('recomendations/add_photos', formData)
        .then((response) => {
          const { data } = response;

          if (data.success) {
            this.$coresnackbars.success(data.message);
            if (mode === 'saveClose') {
              this.$router.push({
                name: 'recomendations.index',
                params: {refresh: true}
              });
            } else {
              this.setEmptyItem();
            }
          } else {
            this.$coresnackbars.error(data.message);
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
    },

    addPhoto() {
      this.photos.push({
        file: null,
        descr: ''
      })
    },
    clearPhoto() {
      this.photos = [{
        file: null,
        descr: ''
      }];
    },
    removePhoto(index) {
      this.photos.splice(index, 1);
    },
    photoSelected(index, payload) {
      if (payload) {
        const that = this;
        const reader = new FileReader();
        reader.onload = function (e) {
          that.photos[index].img = e.target.result;
        }
        reader.readAsDataURL(payload);
      } else {
        this.photos[index].file = null;
        this.photos[index].img = ''
        this.photos[index].descr = ''
      }
    }
  }
}
</script>
