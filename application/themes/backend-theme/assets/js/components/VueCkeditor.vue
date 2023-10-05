<template>
    <div>
        <v-progress-linear v-if="loading" indeterminate color="yellow darken-2" />
        <ckeditor 
            v-model="content" 
            :config="editorConfig"
            @ready="prefill"
        ></ckeditor>
    </div>
</template>

<script>
import CKEditor from 'ckeditor4-vue';

export default {
    name: 'vue-ckeditor',
    props: ['value','toolbarGroup','height'],
    components: {
        ckeditor: CKEditor.component,
    },
    data() {
        return {
            loading: false,
            content: ''
        }
    },
    created() {
        this.loading = true;
        this.content = this.value;
    },
    mounted() {
        this.content = this.value;
    },
    computed: {
        editorConfig() {
            return {
                height: this.height ? this.height : 500,
                filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
                filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
                ...this.toolbarGroup 
                    ? { toolbarGroups: this.toolbarGroup}
                    : {}
            }
        }
    },
    watch: {
        content(val) {
            this.$emit('input', val);
        }
    },
    methods: {
        prefill(editor) {
            this.content = this.value;
            this.loading = false;
        }
    }
}
</script>

<style lang="scss">
.cke_chrome {
    border-color: transparent!important;
}
.cke_top, .cke_bottom {
    background: #ffffff!important;
}
</style>