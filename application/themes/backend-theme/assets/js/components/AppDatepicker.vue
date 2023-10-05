<template>
    <v-menu
        v-model="menu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px">
        <template v-slot:activator="{ on }">
            <v-text-field 
                readonly
                :disabled="disabled"
                :solo="solo" 
                :dense="dense" 
                :outlined="outlined"
                :hide-details="hideDetails"
                :label="label"
                :class="inputClass"
                :placeholder="placeholder"
                v-model="dateFormatted"
                v-on="on"
                append-icon="date_range"
            />
        </template>
        <v-date-picker 
            no-title 
            color="primary"
            v-model="date" 
            :range="range"
            :allowed-dates="allowedDates"
            @input="menu = false"
        /> 
    </v-menu>
</template>


<script>
export default {
    name: 'AppDatepicker',
    props: {
        value: String|Array,
        label: String,
        dense: Boolean,
        solo: Boolean,
        outlined: Boolean,
        hideDetails: Boolean,
        range: Boolean,
        inputClass: String,
        placeholder: String,
        allowedDates: Function,
        disabled: Boolean,
    },
    data() {
        return {
            menu: false,
        }
    },
    computed: {
        date: {
            get() {
                return this.value
            },
            set(value) {
                this.$emit('input', value);
            }
        },
        dateFormatted() {
            if (this.date) {
                return this.$moment(this.date, 'YYYY-MM-DD').format('DD/MM/YYYY');
            }
            return '';
        }
    },
}
</script>
