<template>
    <v-container fluid>
        <v-stepper v-model="stepper">
            <v-stepper-header>
                <v-divider></v-divider>
          <v-stepper-step :complete="stepper > 1" step="1" color="success">
                    {{$t('tickets::lb:step_informer')}}
                </v-stepper-step>
                <v-divider></v-divider>
          <v-stepper-step :complete="stepper > 2" step="2" color="success">
                    {{$t('tickets::lb:step_problem')}}
                </v-stepper-step>
                <v-divider></v-divider>
          <v-stepper-step :complete="stepper > 3" step="3" color="success">
                    {{$t('tickets::lb:step_complete')}}
                </v-stepper-step>
                <v-divider></v-divider>
            </v-stepper-header>

            <v-stepper-items>
                <v-stepper-content step="1">
                    <v-card>
                        <form data-vv-scope="formInformer">
                            <v-container fluid grid-list-lg class="pa-0">
                                <v-subheader class="pl-0 pr-0">{{$t('tickets::lb:general_info')}}</v-subheader>
                                <v-layout row wrap>
                                    <v-flex xs12 md7>
                                        <v-text-field
                                            :label="$t('tickets::lb:full_name')"
                                            :placeholder="$t('tickets::lb:full_name_placeholder')"
                                            v-model.trim="item.informerFullName"
                                            class="mt-0 pt-0"
                                            v-validate="'required|max:50'"
                                            data-vv-name="informerFullName"
                                            :error-messages="errors.collect('formInformer.informerFullName')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md5>
                                        <v-text-field
                                            :label="$t('tickets::lb:phone')"
                                            :placeholder="$t('tickets::lb:phone_placeholder')"
                                            v-model.trim="item.informerPhone"
                                            class="mt-0 pt-0"
                                            v-validate="'required|max:15'"
                                            data-vv-name="informerPhone"
                                            :error-messages="errors.collect('formInformer.informerPhone')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md4>
                                        <v-text-field
                                            :label="$t('tickets::lb:email')"
                                            :placeholder="$t('tickets::lb:email_placeholder')"
                                            v-model="item.informerEmail"
                                            class="mt-0 pt-0"
                                            v-validate="'required|email|email_kalla'"
                                            data-vv-name="informerEmail"
                                            :error-messages="errors.collect('formInformer.informerEmail')"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>

                                <v-subheader class="pl-0 pr-0">{{$t('tickets::lb:company_info')}}</v-subheader>
                                <v-layout row wrap>
                                    <v-flex xs12 md4>
                                        <v-text-field
                                            :label="$t('tickets::lb:nik')"
                                            :placeholder="$t('tickets::lb:nik_placeholder')"
                                            v-model.trim="item.informerNik"
                                            class="mt-0 pt-0"
                                            v-validate="'required|max:20'"
                                            data-vv-name="informerNik"
                                            :error-messages="errors.collect('formInformer.informerNik')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md4>
                                        <v-autocomplete-ajax
                                            url="tickets/company-list"
                                            :label="$t('tickets::lb:company')"
                                            :placeholder="$t('tickets::lb:company_placeholder')"
                                            v-model="item.informerCompanyId"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="informerCompanyId"
                                            :error-messages="errors.collect('formInformer.informerCompanyId')"
                                        ></v-autocomplete-ajax>
                                    </v-flex>
                                    <v-flex xs12 md4>
                                        <v-autocomplete-ajax
                                            url="tickets/department-list"
                                            :label="$t('tickets::lb:department')"
                                            :placeholder="$t('tickets::lb:department_placeholder')"
                                            v-model="item.informerDepartmentId"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="informerDepartmentId"
                                            :error-messages="errors.collect('formInformer.informerDepartmentId')"
                                        ></v-autocomplete-ajax>
                                    </v-flex>
                                    <v-flex xs12 md5>
                                        <v-text-field
                                            :label="$t('tickets::lb:position')"
                                            :placeholder="$t('tickets::lb:position_placeholder')"
                                            v-model.trim="item.informerPosition"
                                            class="mt-0 pt-0"
                                            v-validate="'required|max:255'"
                                            data-vv-name="informerPosition"
                                            :error-messages="errors.collect('formInformer.informerPosition')"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>

                            </v-container>
                            <v-card-actions class="pa-0">
                                <v-spacer></v-spacer>
                                <v-btn depressed color="success" @click="nextStep('formInformer', 2)">
                                    {{$t('btn::continue')}}
                                    <v-icon right>ms-Icon--arrowRight</v-icon>
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card>
                </v-stepper-content>

                <v-stepper-content step="2">
                    <v-card>
                        <form data-vv-scope="formProblem">
                            <v-container fluid grid-list-lg class="pl-0 pr-0 pb-0">
                                <v-layout row wrap>
                                    <v-flex xs12 md9>
                                        <v-text-field 
                                            :label="$t('tickets::lb:subject')"
                                            :placeholder="$t('tickets::lb:subject_placeholder')"
                                            v-model.trim="item.ticketSubject"
                                            class="mt-0 pt-0"
                                            v-validate="'required|max:100'"
                                            data-vv-name="ticketSubject"
                                            :error-messages="errors.collect('formProblem.ticketSubject')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md3>
                                        <v-text-field persistent-hint
                                            type="file"
                                            accept="image/*"
                                            :label="$t('tickets::lb:attachment')"
                                            :placeholder="$t('tickets::lb:attachment_placeholder')"
                                            hint="Only accept image file with size max: 2MB"
                                            @change.native="onFilePicked"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md12>
                                        <v-textarea 
                                            :label="$t('tickets::lb:descr')"
                                            :placeholder="$t('tickets::lb:descr_placeholder')"
                                            v-model.trim="item.ticketDescr"
                                            class="mt-0 pt-0"
                                            v-validate="'required|min:5'"
                                            data-vv-name="ticketDescr"
                                            :error-messages="errors.collect('formProblem.ticketDescr')"
                                        ></v-textarea>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </form>

                        <v-card-actions class="pa-0">
                            <v-spacer></v-spacer>
                            <v-btn depressed @click="stepper = 1">
                                <v-icon left>ms-Icon--arrowLeft</v-icon>
                                {{$t('btn::back')}}
                            </v-btn>
                            <v-btn depressed color="success" @click="nextStep('formProblem', 3)">
                                {{$t('btn::continue')}}
                                <v-icon right>ms-Icon--arrowRight</v-icon>
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-stepper-content>

                <v-stepper-content step="3">
                    <v-card>
                        <form data-vv-scope="formComplete">
                            <v-container fluid grid-list-lg class="pa-0">
                                <v-subheader class="pl-0 pr-0">{{$t('tickets::lb:general_info')}}</v-subheader>
                                <v-layout row wrap>
                                    <v-flex xs12 md3>
                                        <source-select-input 
                                            :label="$t('tickets::lb:source')"
                                            :placeholder="$t('tickets::lb:source_placeholder')"
                                            v-model="item.ticketSource"
                                            :items="sourceOptions"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketSource"
                                            :error-messages="errors.collect('formComplete.ticketSource')"
                                        ></source-select-input>
                                    </v-flex>
                                </v-layout>
                                <v-layout row wrap>
                                    <v-flex xs12 md6>
                                        <v-autocomplete clearable return-object
                                            :label="$t('tickets::lb:category')"
                                            :placeholder="$t('tickets::lb:category_placeholder')"
                                            :search-input.sync="categoryInputSearch"
                                            :loading="categoryInputLoading"
                                            :items="categoryOptions"
                                            v-model="item.ticketCategory"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketCategory"
                                            :error-messages="errors.collect('formComplete.ticketCategory')"
                                        ></v-autocomplete>
                                    </v-flex>
                                    <v-flex xs12 md6>
                                        <v-autocomplete clearable
                                            :disabled="!item.ticketCategory"
                                            :label="$t('tickets::lb:category_sub')"
                                            :placeholder="$t('tickets::lb:category_sub_placeholder')"
                                            :loading="categoryInputLoading"
                                            :items="categorySubOptions"
                                            v-model="item.ticketCategorySub"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketCategorySub"
                                            :error-messages="errors.collect('formComplete.ticketCategorySub')"
                                        ></v-autocomplete>
                                    </v-flex>
                                    <v-flex xs12 md12>
                                        <v-combobox small-chips multiple hide-selected
                                            :search-input.sync="keywordSearch"
                                            :items="keywordOptions"
                                            v-model="item.ticketKeywords"
                                            :disabled="keywordLoading"
                                            :label="$t('tickets::lb:keywords')"
                                            :placeholder="$t('tickets::lb:keywords_placeholder')"
                                            @input.native="keywordsChange"
                                        ></v-combobox>
                                    </v-flex>
                                </v-layout>

                                <v-subheader class="pl-0 pr-0">{{$t('tickets::lb:scale_info')}}</v-subheader>
                                <v-layout row wrap>
                                    <v-flex xs12 md3>
                                        <v-text-field
                                            type="number" 
                                            :label="$t('tickets::lb:scale_position')"
                                            :placeholder="$t('tickets::lb:scale_position_placeholder')"
                                            v-model="item.ticketScalePosition"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketScalePosition"
                                            :error-messages="errors.collect('formComplete.ticketScalePosition')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md3>
                                        <v-text-field 
                                        type="number"
                                            :label="$t('tickets::lb:scale_job')"
                                            :placeholder="$t('tickets::lb:scale_job_placeholder')"
                                            v-model.number="item.ticketScaleJob"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketScaleJob"
                                            :error-messages="errors.collect('formComplete.ticketScaleJob')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md3>
                                        <v-text-field 
                                        type="number"
                                            :label="$t('tickets::lb:scale_availability')"
                                            :placeholder="$t('tickets::lb:scale_availability_placeholder')"
                                            v-model.number="item.ticketScaleAvailability"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketScaleAvailability"
                                            :error-messages="errors.collect('formComplete.ticketScaleAvailability')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs12 md3>
                                        <v-text-field 
                                            type="number"
                                            :label="$t('tickets::lb:scale_operation')"
                                            :placeholder="$t('tickets::lb:scale_operation_placeholder')"
                                            v-model.number="item.ticketScaleOperation"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketScaleOperation"
                                            :error-messages="errors.collect('formComplete.ticketScaleOperation')"
                                        ></v-text-field>
                                    </v-flex>

                                    <v-flex xs12 md3>
                                        <priority-select-input 
                                            :label="$t('tickets::lb:priority')"
                                            :placeholder="$t('tickets::lb:priority_placeholder')"
                                            v-model="item.ticketPriorityId"
                                            class="mt-0 pt-0"
                                            v-validate="'required'"
                                            data-vv-name="ticketPriorityId"
                                            :error-messages="errors.collect('formComplete.ticketPriorityId')"
                                        ></priority-select-input>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </form>

                        <v-card-actions class="pa-0">
                            <v-spacer></v-spacer>
                            <v-btn depressed @click="stepper = 2">
                                <v-icon left>ms-Icon--arrowLeft</v-icon>
                                {{$t('btn::back')}}
                            </v-btn>
                            <v-btn depressed color="primary" @click="stepper = 3">
                                {{$t('btn::submit')}}
                                <v-icon right>ms-Icon--mailSend</v-icon>
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-stepper-content>

            </v-stepper-items>
        </v-stepper>
    </v-container>
</template>

<script>
import VeeValdidate, { Validator } from 'vee-validate';
Vue.use(VeeValdidate);

import AppAutocompleteAjax from '../../components/AppAutocompleteAjax.vue';
import SourceSelectInput from '../../components/SourceSelectInput';
import PrioritySelectInput from '../../components/PrioritySelectInput';
import { validEmailKalla } from '../../utils/helpers.js';

Validator.extend('email_kalla', {
    getMessage: field => 'Email address must be in kallagroup domain',
    validate(value) {
        return validEmailKalla(value);
    }
})

export default {
    name: 'tickets-create-page',
    components: {
        AppAutocompleteAjax, SourceSelectInput, PrioritySelectInput
    },
    data() {
        return {
            stepper: 1,
            item: {
                informerFullName: '',
                informerEmail: '',
                informerPhone: '',
                informerNik: '',
                informerCompanyId: null,
                informerDepartmentId: null,
                informerPosition: '',
                ticketSubject: '',
                ticketAttachment: null,
                ticketDescr: '',
                ticketSource: null,
                ticketCategory: null,
                ticketCategorySub: null,
                ticketKeywords: [],
                ticketScalePosition: 0,
                ticketScaleJob: 0,
                ticketScaleAvailability: 0,
                ticketScaleOperation: 0,
                ticketPriorityId: null,
            },
            dictionary: {
                attributes: {
                    informerFullName: this.$t('tickets::lb:full_name'),
                    informerEmail: this.$t('tickets::lb:email'),
                    informerPhone: this.$t('tickets::lb:phone'),
                    informerNik: this.$t('tickets::lb:nik'),
                    informerCompanyId: this.$t('tickets::lb:company'),
                    informerDepartmentId: this.$t('tickets::lb:department'),
                    informerPosition: this.$t('tickets::lb:position'),
                    ticketSubject: this.$t('tickets::lb:subject'),
                    ticketDescr: this.$t('tickets::lb:descr'),
                    ticketSource: this.$t('tickets::lb:source'),
                    ticketCategory: this.$t('tickets::lb:category'),
                    ticketCategorySub: this.$t('tickets::lb:category_sub'),
                    ticketKeywords: this.$t('tickets::lb:keywords'),
                    ticketScalePosition: this.$t('tickets::lb:scale_position'),
                    ticketScaleJob: this.$t('tickets::lb:scale_job'),
                    ticketScaleAvailability: this.$t('tickets::lb:scale_availability'),
                    ticketScaleOperation: this.$t('tickets::lb:scale_operation'),
                    ticketPriorityId: this.$t('tickets::lb:priority'),
                },
            },
            sourceOptions: ['EMAIL', 'PHONE', 'CHAT'],
            categoryInputSearch: '',
            categoryInputLoading: false,
            categoryList: [],
            keywordSearch: '',
            keywordOptions: [],
            keywordLoading: false
        }
    },
    computed: {
        ticketAttachmentText() {
            if (this.item.ticketAttachment) {
                if (this.item.ticketAttachment[0] !== undefined) {
                    return this.item.ticketAttachment[0].name;
                }
            }
            return '';
        },
        categoryOptions() {
            let results = [];
            if (_.isArray(this.categoryList)) {
                const that = this;
                _.forEach(this.categoryList, function(item) {
                    results.push({
                        text: item.name,
                        value: item.id
                    });
                });
            }

            return results
        },
        categorySubOptions() {
            if (!this.item.ticketCategory) {
                return [];
            }

            let results = [];
            const that = this;
            if (that.item.ticketCategory && typeof that.item.ticketCategory === "object" && 
                    that.item.ticketCategory.hasOwnProperty('value') && _.isArray(that.categoryList)) {
                const findIndex = _.findIndex(that.categoryList, {id: that.item.ticketCategory.value});
                if (findIndex >= 0) {
                    const category = that.categoryList[findIndex];
                    if (category.childs && _.isArray(category.childs)) {
                        _.forEach(category.childs, function(child) {
                            results.push({
                                text: child.name,
                                value: child.id
                            });
                        });
                    }
                }
            }

            return results;
        }
    },
    watch: {
        categoryInputSearch(val) {
            if (this.categoryInputLoading) {
                return false;
            }

            if (this.item.ticketCategory && typeof this.item.ticketCategory === "object" && this.item.ticketCategory.hasOwnProperty('text')) {
                if (this.item.ticketCategory.text == val) {
                    return false;
                }
            }

            let search = '';
            if (typeof val === "string") {
                search = val;
            }

            const that = this;
            this.loading = true;
            this.$axios.get('tickets/category-list', {
                params: {search}
            })
            .then(response => {
                const { data } = response;
                if (_.isArray(data)) {
                    that.categoryList = data;
                }
            })
            .catch(error => {
                console.error(error);
            })
            .then(() => {
                this.loading = false
            });
        }
    },
    created() {
    this.$root.$on('page-header:back-action', this.closeAction);
    this.$root.$on('page-header:save-close-action', this.addCloseAction);
  },
  destroyed() {
    this.$root.$off('page-header:back-action', this.closeAction);
    this.$root.$off('page-header:save-close-action', this.addCloseAction);
    },
    mounted() {
        this.$validator.localize('en', this.dictionary);
    },
    methods: {
        nextStep(scope, nextStep) {
            this.$validator.validateAll(scope)
                .then(valid => {
                    if (valid) {
                        this.stepper = nextStep;
                    }
                });
        },
        onFilePicked(evt) {
            const files = evt.target.files;
            if (files[0] !== undefined) {
                this.item.ticketAttachment = files;
            } else {
                this.item.ticketAttachment = null;
            }
        },
        keywordsChange: _.debounce(function (e) {
      const that = this;
      this.$axios.get('tickets/keywords-list', {
        params: {q: e.target.value}
      }).then(response => {
        const { data } = response;
        that.keywordOptions = [];
        data.forEach(element => {
          that.keywordOptions.push(element.name);
        });
      })
    }, 100),
        closeAction() {
            return this.$router.push({
        name: 'tickets.list.index',
      });
        },
        addCloseAction() {

        },
    }
}
</script>
