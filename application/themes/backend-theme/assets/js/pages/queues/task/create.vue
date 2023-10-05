<template>
	<v-card class="elevation-base" :loading="loading" :disabled="loading">
		<v-card-text>
			

			<v-subheader class="pl-0 pr-0 grey--text text--darken-3 font-weight-bold">
				Task Information
			</v-subheader>
			<v-row>
				<v-col cols="12" md="4">
					<v-autocomplete
						:disabled="loading"
						label="Services"
						placeholder="Search services"
						v-model="row.servicesId"
						:items="servicesOptions"
						:error-messages="errorMsg.servicesId"
						@change="fetchCategory"
					></v-autocomplete>
          <v-alert v-if="servicesDescription" color="primary" text :class="{'mt-4': !!errorMsg.servicesId}">
            {{ servicesDescription }}
          </v-alert>
				</v-col>

				<v-col cols="12" md="4">
					<v-autocomplete
						:disabled="loading"
						label="Category"
						placeholder="Search category"
						v-model="row.categoryId"
						:items="categoryOptions"
						:error-messages="errorMsg.categoryId"
						@change="fetchCategorySub"
					></v-autocomplete>
          <v-alert v-if="categoryDescription" color="primary" text :class="{'mt-4': !!errorMsg.categoryId}">
            {{ categoryDescription }}
          </v-alert>
				</v-col>

				<v-col cols="12" md="4">
					<v-select 
						:disabled="loading"
						label="Category sub"
						placeholder="Search category sub"
						v-model="row.categorySubId"
						:items="categorySubOptions"
						:error-messages="errorMsg.categorySubId"
					></v-select>
          <v-alert v-if="categorySubDescription" color="primary" text :class="{'mt-4': !!errorMsg.categorySubId}">
            {{ categorySubDescription }}
          </v-alert>
				</v-col>

			</v-row>
			<v-row v-if="aplikasi">
				<v-col  cols="12" md="4">
					<v-combobox
              :disabled="loading"
              label="Jaringan"
              placeholder="Apakah jaringan terkoneksi?"
              v-model="row.network"
              class="mt-0 pt-0"
              :items="networkId"></v-combobox>
              
          
				</v-col>
        <v-col cols="12" md="8">
          <v-combobox
              :disabled="loading"
              label="Subject"
              placeholder="Enter subject"
              v-model="row.subject"
              class="mt-0 pt-0"
              :error-messages="errorMsg.subject"
              :search-input.sync="subjectSearch"
              :items="subjectOptions"
              @input.native="subjectChange($event, row.categoryId, row.categorySubId)">
              <template v-slot:prepend-item>
              <span class="px-3" style="font-size: 11px; color: #9CA3AF">Suggestions</span>
              </template>
          </v-combobox>
        </v-col></v-row>

		<v-row v-else>
				
        <v-col cols="12">
          <v-combobox
              :disabled="loading"
              label="Subject"
              placeholder="Enter subject"
              v-model="row.subject"
              class="mt-0 pt-0"
              :error-messages="errorMsg.subject"
              :search-input.sync="subjectSearch"
              :items="subjectOptions"
              @input.native="subjectChange($event, row.categoryId, row.categorySubId)">
              <template v-slot:prepend-item>
              <span class="px-3" style="font-size: 11px; color: #9CA3AF">Suggestions</span>
              </template>
          </v-combobox>
        </v-col></v-row>

		<v-row>
				<v-col cols="12">
					<v-textarea 
						:disabled="loading"
						label="Description"
						placeholder="Enter description of the problem"
						v-model="row.ticketDescr"
						:error-messages="errorMsg.ticketDescr"
					></v-textarea>
				</v-col>
				<v-col cols="12">
					<div class="dropzone" id="ticketDropzone"></div>
					<small 
						class="v-messages__message mt-2" 
						style="line-height:1;display:block;color:rgba(0,0,0,0.54)">
						Max number of files: 10 file; Max file size: 1.5 MB; Only accept files: jpg|jpeg|png|doc|docx|xls|xlsx|pdf;
					</small>
				</v-col>
			</v-row>
		</v-card-text>
		<v-card-actions class="pa-4">
			<v-btn color="success" @click="submitAction">
				<v-icon left>ms-Icon ms-Icon--Send</v-icon> Submit
			</v-btn>
		</v-card-actions>
	</v-card>
</template>

<script>
import _ from 'lodash'

export default {
	name: 'request-ticket-form',
	props: {
		isLogin: Boolean
	},
	data() {
		return {
			loading: false,
			networkId: [
        {
          value: '1',
          text: 'Dapat mengakses situs lain',
        },
        {
          value: '0',
          text: 'Tidak dapat mengakses situs lain',
        },
      ],
			row: {
				email: '',
				nik: '',
				phone: '',
				subject: '',
				ticketDescr: '',
				servicesId: '',
				categoryId: '',
				categorySubId: '',
				network: '',
			},
			errorMsg: {
				email: '',
				nik: '',
				phone: '',
				subject: '',
				ticketDescr: '',
				servicesId: '',
				categoryId: '',
				categorySubId: '',
				network: '',
			},
			categoryOptions: [],
			categorySubOptions: [],
			ticketDropzone: null,
      subjectSearch: null,
      subjectOptions: [],
		};
	},
  computed: {
	aplikasi() {
      const find = _.find(this.categoryOptions, {value: this.row.categoryId})
      if (find) {
		if (find.text.includes("Aplikasi")) {
        return find.text;}
      }
    },
	servicesDescription() {
      const find = _.find(this.servicesOptions, {value: this.row.servicesId})
      if (find) {
        return find.description;
      }
    },
	  categoryDescription() {
	    const find = _.find(this.categoryOptions, {value: this.row.categoryId})
      if (find) {
        return find.description;
      }
    },
    categorySubDescription() {
      const find = _.find(this.categorySubOptions, {value: this.row.categorySubId})
      if (find) {
        return find.description;
      }
    },
  },
	watch: {
		loading(value) {
			if (value) {
				if (this.ticketDropzone) {
					this.ticketDropzone.disable();
				}
			} else {
				if (this.ticketDropzone) {
					this.ticketDropzone.enable();
				}
			}
		}
	},
	created() {
		//this.fetchCategory();
		this.fetchServicesSub();
	},
	async mounted() {
		const that = this;
		Dropzone.autoDiscover = false;
		this.ticketDropzone = new Dropzone(this.$el.querySelector("#ticketDropzone"), {
			url: SITE_URL + 'request-ticket/uploadattachment',
			maxFilesize: 1.5, // MB
			// autoProcessQueue: false,
			addRemoveLinks: true,
			maxFiles: 10,
			accept: function(file, done) {
				if (that.loading) {
					done("this is not gona upload.");
				}
				else { 
					done(); 
				}
			}
		});
	},
	methods: {
		clearErrorMsg() {
			this.errorMsg = {
				email: '',
				subject: '',
				ticketDescr: '',
				servicesId: '',
				categoryId: '',
				categorySubId: '',
				network: '',
			};
		},
		fetchServicesSub() {
			this.servicesOptions  = [];
			this.$axios.get('queues/task/services')
				.then(response => {
				const {data} = response;
				if (!data.success) {
					this.$coresnackbars.error(data.message);
				} else {
					this.servicesOptions = data.rows;
				}
			}).catch((error) => {
					console.log(error);
					const { statusText, data } = error;
					this.$coresnackbars.error(statusText);
			});
		},
		fetchCategory() {
			this.categoryOptions = [];
			this.$axios.get('request-ticket/category', {
				params: {parent_id: this.row.servicesId}
			})
				.then(response => {
				const {data} = response;
				if (!data.success) {
					this.$coresnackbars.error(data.message);
				} else {
					this.categoryOptions = data.rows;
				}
			}).catch((error) => {
					console.log(error);
					const { statusText, data } = error;
					this.$coresnackbars.error(statusText);
			});
		},
		fetchCategorySub() {
			this.row.categorySubId = '';
			this.categorySubOptions = [];
			this.$axios.get('request-ticket/subcategory', {
				params: {parent_id: this.row.categoryId}
			})
			.then(response => {
				const {data} = response;
				if (!data.success) {
					this.$coresnackbars.error(data.message);
				} else {
					this.categorySubOptions = data.rows;
				}
			}).catch((error) => {
					console.log(error);
					const { statusText, data } = error;
					this.$coresnackbars.error(statusText);
			});
		},
		submitAction() {
			if (this.loading) {
				return false;
			}

			this.clearErrorMsg();
			this.loading = true;

			const files = this.ticketDropzone.files.filter(function(item) {
				return item.status === 'success';
			});
			
			const formItem = new FormData();
			formItem.set('email', this.row.email);
			formItem.set('nik', this.row.nik);
			formItem.set('phone', this.row.phone);
			formItem.set('subject', this.row.subject);
			formItem.set('servicesId', this.row.servicesId);
			formItem.set('categoryId', this.row.categoryId);
			formItem.set('categorySubId', this.row.categorySubId);
			formItem.set('ticketDescr', this.row.ticketDescr);
			formItem.set('network', this.row.network.value ? this.row.network.value : 'Null');
			formItem.set('fileCount', 0);
			if (files.length > 0) {
				formItem.set('fileCount', files.length);
				files.forEach((file, index) => {
					const indexFile = index + 1;
					formItem.set('userfiles_' + indexFile, file);
				});
			}

			this.$axios.post('request-ticket/send', formItem)
				.then(response => {
					this.loading = false;
					const { data } = response;
					if (data.success) {
						location.href = SITE_URL + 'request-ticket/success';
					} else {
						this.$coresnackbars.error(data.message);
					}
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
    subjectChange: _.debounce(function(e, category, subcategory) {
      const that = this;
      this.$axios
          .get("request-ticket/subjectsuggestion", {
            params: {
              q: e.target.value,
              category: category,
              subcategory: subcategory
            }
          })
          .then(response => {
            const { data } = response;
            that.subjectOptions = data.rows;
          });
    }, 100),
	}
}
</script>

<style>
.dropzone {
	border-width: 0;
	background: #F5F5F5;
}
.dropzone .dz-preview .dz-remove {
	position: absolute;
	top: -20px;
	left: 0;
	right: 0;
	text-align: center;
}
.dropzone .dz-preview.dz-error .dz-error-mark {
	border: 2px solid #f4516c;
	height: 58px;
	width: 58px;
	border-radius: 50%;
}
</style>
