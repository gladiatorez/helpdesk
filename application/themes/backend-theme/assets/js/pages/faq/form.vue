<template>
  <v-container fluid class="pa-5">
    <v-row>
      <v-col cols="12" md="4">
        <v-card :loading="loading" :disabled="loading" outlined>
          <v-card-title class="font-weight-bold">Properties</v-card-title>
          <v-divider />
          <v-card-text>
            <v-text-field
              v-model="item.title"
              :disabled="loading"
              :error-messages="errorMsg.title"
              :label="$t('faq::title')"
              @change="slugifyTitle"
            ></v-text-field>

            <v-text-field
              v-model="item.slug"
              :disabled="loading"
              :error-messages="errorMsg.slug"
              :label="$t('faq::slug')"
            ></v-text-field>

            <v-autocomplete
              v-model="item.categoryId"
              :disabled="loading"
              :items="categoryOptions"
              :error-messages="errorMsg.categoryId"
              :label="$t('faq::category')"
            ></v-autocomplete>

            <v-combobox small-chips multiple hide-selected
              :search-input.sync="keywordSearch"
              :items="keywordOptions"
              v-model="item.keywords"
              :disabled="loading"
              :error-messages="errorMsg.keywords"
              :label="$t('faq::keywords')"
              @input.native="keywordsChange"
            ></v-combobox>

            <v-checkbox hide-details
              color="primary"
              :label="$t('faq::is_headline')"
              v-model="item.isHeadline"
              :false-value="false"
              :true-value="true"
            ></v-checkbox>

            <v-checkbox
              color="primary"
              :label="$t('lb::active')"
              v-model="item.active"
              false-value="D"
              true-value="A"
            ></v-checkbox>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="8">
        <v-card outlined>
          <v-card-title>Content</v-card-title>
          <vue-ckeditor v-model="item.descr"></vue-ckeditor>
        </v-card>
      </v-col>
    </v-row>

    <div id="editor"></div>
  </v-container>
</template>

<script>
import slugify from 'slugify';
import VueCkeditor from '../../components/VueCkeditor';

export default {
  name: 'faq-form',
  components: {
    VueCkeditor
  },
  data() {
    return {
      loading: false,
      item: {
        id: '',
        title: '',
        slug: '',
        descr: '',
        categoryId: null,
        keywords: [],
        isHeadline: false,
        active: 'A',
      },
      errorMsg: {
        id: '',
        title: '',
        slug: '',
        descr: '',
        categoryId: '',
        keywords: '',
        isHeadline: '',
        active: '',
      },
      categoryOptions: [],
      keywordSearch: null,
      keywordOptions: []
    }
  },
  computed: {
    title() {
      let title = this.$t('faq::heading_new');
      if (this.$route.params.id) {
        title = this.$t('faq::heading_edit');
      }
      return title;
    },
    mode() {
      let mode = 'add';
      if (this.$route.params.id) {
        mode = 'edit';
      }
      return mode;
    }
  },
  created() {
    this.$root.$on('page-header:back-action', this.closeAction);
    this.$root.$on('page-header:save-close-action', this.addCloseAction);

    if (this.$route.params.id) {
      this.item.id = this.$route.params.id;
      this.fetchItem();
    }
    this.initForm();
  },
  destroyed() {
    this.$root.$off('page-header:back-action', this.closeAction);
    this.$root.$off('page-header:save-close-action', this.addCloseAction);
  },
  methods: {
    async initForm() {
      await this.fetchOptions();
      if (this.mode === 'edit') {
        this.fetchItem();
      }
    },
    keywordsChange: _.debounce(function (e) {
      const that = this;
      this.$axios.get('faq/keywords', {
        params: {q: e.target.value}
      }).then(response => {
        const { data } = response;
        that.keywordOptions = [];
        data.forEach(element => {
          that.keywordOptions.push(element.name);
        });
      })
    }, 100),
    slugifyTitle(title) {
      this.item.slug = slugify(title, {
        replacement: '-',
        remove: /[*+~.()'"!:@,?]/g,
        lower: true
      });
    },
    closeAction() {
      return this.$router.push({
        name: 'faq.index',
        params: {
          refresh: true
        }
      });
    },
    clearErrorMsg() {
      this.errorMsg = {
        id: '',
        title: '',
        slug: '',
        descr: '',
        categoryId: '',
        keywords: '',
        isHeadline: '',
        active: '',
      };
    },
    setEmptyItem() {
      this.item = {
        id: '',
        title: '',
        slug: '',
        descr: '',
        categoryId: null,
        keywords: [],
        isHeadline: false,
        active: 'A',
      };
    },
    fetchOptions() {
      const that = this;
      that.categoryOptions = [];
      that.$axios.get('faq/form-options')
        .then(response => {
          const { data } = response;
          data.categoryOptions.forEach(element => {
            that.categoryOptions.push({
              value: element.id,
              text: element.name
            });
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    fetchItem() {
      if (this.loading) {
        return false;
      }

      this.loading = true;
      this.$axios.get('faq/item', { params: {id: this.item.id} })
        .then(response => {
          const { data } = response;
          const row = data.data;

          this.item = {
            id: row.id,
            title: row.title,
            slug: row.slug,
            descr: row.descr,
            categoryId: row.categoryId,
            keywords: [],
            isHeadline: row.isHeadline,
            active: row.active,
          };

          if (typeof row.keywords === "string") {
            this.item.keywords = row.keywords.split(',');
          } else if (typeof row.keywords === "object") {
            this.item.keywords = row.keywords;
          }

          this.loading = false;
        })
        .catch(error => {
          const {statusText, data} = error;
          if (typeof data.message !== "undefined") {
            this.$coresnackbars.error(data.message);
          } else {
            this.$coresnackbars.error(statusText);
          }
          this.loading = false;
        });
    },
    addAction() {
      this.saveChanges('save')
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

      const item = new FormData();
      item.set('title', this.item.title);
      item.set('slug', this.item.slug);
      item.set('descr', this.item.descr);
      item.set('categoryId', this.item.categoryId ? this.item.categoryId : '');
      // item.set('keywords', this.item.keywords ? this.item.keywords : '');
      item.set('isHeadline', this.item.isHeadline ? '1' : '0');
      item.set('active', this.item.active);
      
      if (this.item.keywords.length > 0) {
        this.item.keywords.forEach(element => {
          item.append('keywords[]', element);
        });
      }

      let url = 'faq/create';
      if (this.mode === 'edit') {
        item.set('id', this.item.id);
        url = 'faq/edit';
      }

      this.$axios.post(url, item)
        .then((response) => {
          const { data } = response;
          this.$coresnackbars.success(data.message);

          if (data.success) {
            if (mode === 'saveClose') {
              this.$router.push({
                name: 'faq.index',
                params: {refresh: true}
              });
            } else {
              this.setEmptyItem();
            }
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

<style>
.ql-container.ql-snow, .ql-toolbar.ql-snow {
  border: none;
}
.ql-toolbar.ql-snow {
  border-bottom: 1px solid #ebedf2;
}

.ql-editor, .ql-container {
  height: 35rem;
}
</style>
