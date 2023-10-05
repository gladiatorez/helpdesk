<script>
export default {
    name: 'AppAutocompleteAjax',
    props: {
        value: {
            type: [String, Object]
        },
        url: {
            type: String,
            required: true,
        },
        itemText: {
            type: String,
            default: 'text'
        },
        itemValue: {
            type: String,
            default: 'value'
        },
        itemExtend: Array,
        dataKey: {
            type: String,
            default: 'rows'
        },
    },
    data() {
        return {
            loading: false,
            searchInput: '',
            items: [],
            defaultItems: null
        }
    },
    computed: {
        selected: {
            get() {
                return this.value
            },
            set(val) {
                this.$emit('input', val)
            }
        },
    },
    watch: {
        value(val) {
            if (val) {
                this.addDefaultItem(val);
            }
        },
        searchInput(value) {
            if (this.$lodash.isObject(this.selected)) {
                value && value !== this.selected[this.itemValue] && this.fetchOptions(value)
            } else {

                value && value !== this.selected && this.fetchOptions(value)
            }
        },
    },
    mounted() {
        if (this.value) {
            this.selected = this.value; 
            this.addDefaultItem(this.value);
        }
    },
    methods: {
        addDefaultItem(defaultItem) {
            if (defaultItem) {
                const find = this.$lodash.find(this.items, defaultItem);
                this.items.push(defaultItem);
            }
        },
        fetchOptions(searchInput) {
            const that = this

            if (!this.url) {
                return false;
            }

            if (that.loading) {
                return false
            }

            that.loading = true
            that.$axios.get(this.url, {params: { q: searchInput }}).then(response => {
                const { data } = response
                const rows = data[that.dataKey]
                
                let items = []
                rows.forEach(row => {
                    let obj = {}
                    obj[that.itemValue] = row[that.itemValue]
                    obj[that.itemText] = row[that.itemText]
                    if (that.itemExtend && that.itemExtend.length > 0) {
                        that.itemExtend.forEach(itemExtend => {
                            obj[itemExtend] = row[itemExtend];
                        });
                    }
                    items.push(obj)
                })
                that.items = items;

                this.addDefaultItem(this.selected);
            }).catch(error => {
                console.warn(error)
            }).then(() => {
                that.loading = false
            })
        },
        forceClearList() {
            this.defaultItems = null
            this.items = []
        },
        onChange(payload) {
            this.$emit('change', payload)
        }
    }
}
</script>

<template>
    <v-autocomplete 
        autocomplete="off"
        v-bind="$attrs"
        v-model="selected"
        :items="items"
        :loading="loading"
        :search-input.sync="searchInput"
        :item-text="itemText"
        :item-value="itemValue"
        @change="onChange"
        return-object
    ></v-autocomplete>
</template>
