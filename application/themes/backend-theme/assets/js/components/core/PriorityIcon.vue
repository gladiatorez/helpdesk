<template>
    <v-icon :class="color" style="font-size:18px;">ms-Icon ms-Icon--StarburstSolid</v-icon>
</template>

<script>
export default {
    name: 'CorePriorityIcon',
    props: {
        priorityName: String,
    },
    computed: {
        color() {
            if (this.priorityName === 'High') {
                return 'error--text';
            } else if (this.priorityName === 'Normal') {
                return 'blue--text';
            } else {
                return 'warning--text';
            }
        }
    },
}
</script>

