<template>
    <v-select 
        v-bind="$attrs"
        v-model="selected"
        :items="sourceOptions"
    ></v-select>
</template>

<script>
export default {
    name: 'source-select-input',
    props: ['value'],
    data() {
        return {
            sourceOptions: ['EMAIL', 'PHONE', 'CHAT'],
        }
    },
    computed: {
        selected: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        }
    },
    methods: {
        
    }
}
</script>
