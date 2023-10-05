<template>
    <v-chip small :color="color">
        <span class="overline font-weight-bold">{{flag}}</span>
    </v-chip>
</template>

<script>
export default {
    name: 'CoreFlagChip',
    props: {
        flag: String,
    },
    computed: {
        color() {
            // 'REQUESTED','OPENED','PROGRESS','FINISHED','CLOSED'
            if (this.flag === 'OPENED') {
                return 'teal lighten-4';
            } else if (this.flag === 'PROGRESS') {
                return 'blue lighten-4';
            } else if (this.flag === 'FINISHED') {
                return 'green lighten-4';
            } else if (this.flag === 'CLOSED') {
                return 'blue-grey lighten-4';
            } else {
                return 'amber lighten-4';
            }
        }
    },
}
</script>

