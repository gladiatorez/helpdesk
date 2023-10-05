<template>
    <v-container fluid class="pa-5">
        <v-card flat>
            <v-card-title>
                <v-btn
                    rounded  small
                    color="primary"
                    class="white--text"
                    @click="dialogExcel = true">
                    <v-icon left>ms-Icon ms-Icon--ExcelDocument</v-icon>
                    Excel
                </v-btn>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="items"
                :server-items-length="totalItems"
                :options.sync="tableOptions"
                :loading="loading">
                <template v-slot:item.category_name="{ item }">
                    {{ item.category_name }} /
                    {{ item.category_sub_name }}
                </template>
                <template v-slot:item.staff="{ item }">
                    <template v-if="item.staffs">
                        <template v-for="(staff, index) in item.staffs">
                            <v-chip small
                                :key="index"
                                color="teal lighten-4">
                                {{ staff }}
                            </v-chip>
                        </template>
                    </template>
                </template>
            </v-data-table>
        </v-card>

        <app-form-dialog 
            hide-form-action
            v-model="dialogExcel"
            title="Export to excel"
            :persistent="false"
            :loading="dialogExcelLoading"
            @close="dialogExcel = false">
            <v-menu
                offset-y
                ref="pickerMonth"
                v-model="pickerMonth"
                :close-on-content-click="false"
                :nudge-right="40"
                :return-value.sync="pickerMonthValue"
                transition="scale-transition"
                max-width="290px"
                min-width="290px">
                <template v-slot:activator="{ on }">
                    <v-text-field readonly
                        v-model="pickerMonthValue"
                        label="Month"
                        v-on="on"
                    />
                </template>
                <v-date-picker
                    v-model="pickerMonthValue"
                    type="month"
                    no-title scrollable>
                    <v-spacer></v-spacer>
                    <v-btn small depressed @click="pickerMonth = false">
                        Cancel
                    </v-btn>
                    <v-btn
                        small depressed
                        color="primary"
                        @click="$refs.pickerMonth.save(pickerMonthValue)">
                        OK
                    </v-btn>
                </v-date-picker>
            </v-menu>

            <v-autocomplete
                :loading="dialogExcelLoading"
                :items="dialogExcelCategories"
                v-model="dialogExcelCategoryId"
                label="Category"
                placeholder="Choose category"
                @change="dialogExcelCategoriesSubId = null"
            />

            <v-autocomplete
                :loading="dialogExcelLoading"
                :items="dialogExcelCategoriesSub"
                v-model="dialogExcelCategoriesSubId"
                label="Category sub"
                placeholder="Choose category sub"
            />

            <template v-slot:form-action>
                <v-card-actions>
                    <v-btn color="primary" @click="showReport" depressed small>
                        OK
                    </v-btn>
                    <v-btn @click="dialogExcel = false" depressed small>
                        Cancel
                    </v-btn>
                </v-card-actions>
            </template>
        </app-form-dialog>
    </v-container>
</template>

<script>
import { fetchDtRows } from "../../../utils/helpers";
export default {
    name: "reports-page",
    components: {
        AppFormDialog: () => import('../../../components/AppFormDialog.vue'),
    },
    data() {
        return {
            headers: [
                {
                    text: "Subject",
                    value: "subject"
                },
                {
                    text: "Category",
                    value: "category_name"
                },
                {
                    text: "Staff",
                    value: "staff",
                    sortable: false
                },
                {
                    text: "Times",
                    value: "times",
                    sortable: false
                }
            ],
            loading: false,
            tableOptions: {},
            items: [],
            totalItems: 0,
            loading: false,
            searchText: "",
            dialogExcel: false,
            dialogExcelLoading: false,
            dialogExcelCategories: [],
            dialogExcelCategoryId: null,
            dialogExcelCategoriesSubId: null,
            pickerMonth: false,
            pickerMonthValue: new Date().toISOString().substr(0, 7)
        };
    },
    computed: {
        dialogExcelCategoriesSub() {
            if (this.dialogExcelCategories.length <= 0) {
                return [];
            }
            if (!this.dialogExcelCategoryId) {
                return [];
            }

            const findIndex = _.findIndex(this.dialogExcelCategories, {
                value: this.dialogExcelCategoryId
            });
            if (findIndex < 0) {
                return [];
            }

            const category = this.dialogExcelCategories[findIndex];
            let childrens = [
                {
                    value: "ALL",
                    text: "ALL"
                }
            ];
            category.childrens.forEach(element => {
                childrens.push({
                    value: element.id,
                    text: element.name
                });
            });

            return childrens;
        }
    },
    watch: {
        tableOptions() {
            this.refreshAction();
        },
        dialogExcel(value) {
            if (value) {
                this.fetchCategories();
            }
        }
    },
    created() {
        this.$root.$on("page-header:refresh-action", this.refreshAction);
        this.$root.$on("page-header:search-action", this.searchAction);
        this.$root.$on(
            "page-header:search-cancel-action",
            this.searchClearAction
        );
    },
    destroyed() {
        this.$root.$off("page-header:refresh-action", this.refreshAction);
        this.$root.$off("page-header:search-action", this.searchAction);
        this.$root.$off(
            "page-header:search-cancel-action",
            this.searchClearAction
        );
    },
    methods: {
        getDataFromApi() {
            this.loading = true;
            const that = this;
            return new Promise((resolve, reject) => {
                const {
                    sortBy,
                    descending,
                    page,
                    rowsPerPage
                } = this.pagination;
                that.$axios
                    .get("reports/general", {
                        params: {
                            sort: sortBy,
                            order: descending ? "desc" : "asc",
                            page: page,
                            limit: rowsPerPage,
                            search: this.searchText
                        }
                    })
                    .then(response => {
                        that.loading = false;
                        resolve({
                            items: response.data.rows,
                            total: response.data.total
                        });
                    });
            });
        },
        refreshAction() {
            this.loading = true;
            fetchDtRows( "reports/general", this.tableOptions, this.searchText).then(data => {
                this.items = data.items;
                this.totalItems = data.total;
            }).catch(error => {
                this.loading = false;
            }).then(() => {
                this.loading = false;
            });
        },
        searchAction(payload) {
            this.searchText = payload;
            this.refreshAction();
        },
        searchClearAction() {
            this.searchText = "";
            this.refreshAction();
        },
        fetchCategories() {
            const that = this;
            that.dialogExcelLoading = true;
            that.dialogExcelCategories = [];
            that.$axios
                .get("reports/general/categories")
                .then(response => {
                    const { data } = response;
                    if (data) {
                        that.dialogExcelCategories = data;
                    }

                    that.dialogExcelLoading = false;
                })
                .catch(error => {
                    console.log(error);
                    that.dialogExcelLoading = false;
                });
        },
        showReport() {
            if (this.dialogExcelCategoryId) {
                const monthSelected = this.$moment(
                    this.pickerMonthValue,
                    "YYYY-MM"
                );
                window.open(
                    SITE_URL +
                        "/reports/by_category?id=" +
                        this.dialogExcelCategoryId +
                        "&sub=" +
                        this.dialogExcelCategoriesSubId +
                        "&year=" +
                        monthSelected.format("YYYY") +
                        "&month=" +
                        monthSelected.format("MM")
                );
            }
        }
    }
};
</script>
