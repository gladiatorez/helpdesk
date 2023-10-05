<template>
    <v-select 
        v-bind="$attrs"
        v-model="selected"
        :items="priorityOptions"
    ></v-select>
</template>

<script>
export default {
    name: 'priority-select-input',
    props: ['value'],
    data() {
        return {
            priorityOptions: [
                {value: '1', text: 'High'},
                {value: '2', text: 'Normal'},
                {value: '3', text: 'Low'},
            ],
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
