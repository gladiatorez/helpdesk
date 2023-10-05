<template>
    <v-avatar v-bind="$attrs">
        <span :class="textColor">{{ initial }}</span>
    </v-avatar>
</template>

<script>
import { unicode_slice } from '../../utils/helpers'
export default {
    name: 'CoreAvatarInitial',
    props: {
        title: {
            type: String,
            default: '',
        },
        charCount: {
            type: Number,
            default: 2
        },
        textColor: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            initial: ''
        }
    },
    watch: {
        title(value) {
            this.initial = unicode_slice(value, 0, this.charCount).toUpperCase();
        }
    },
    created() {
        this.initial = unicode_slice(this.title, 0, this.charCount).toUpperCase();
    }
}
</script>
