<template>
    <v-dialog
        scrollable
        transition="slide-x-transition"
        v-model="show"
        v-bind="$attrs"
        :persistent="persistent"
        :retain-focus="false"
        :max-width="maxWidth"
        :fullscreen="$vuetify.breakpoint.xsOnly">
        <v-card :loading="loading" :disabled="loading">
            <v-card-title>
                {{ title }}
                <v-spacer />
                <slot name="title-tools"></slot>
                <template v-if="urlClose">
                    <v-btn icon small 
                        :to="urlClose">
                        <v-icon>close</v-icon>
                    </v-btn>
                </template>
                <template v-else>
                    <v-btn icon small @click="closeAction">
                        <v-icon>close</v-icon>
                    </v-btn>
                </template>
            </v-card-title>

            <v-card-text :class="contentClass">
                <slot></slot>
            </v-card-text>

            <template v-if="hideFormAction">
                <slot name="form-action"></slot>
            </template>
            <template v-else>
                <v-card-actions>
                    <v-btn v-if="btnSaveClose" color="primary" small depressed @click="saveCloseAction">
                        {{$t('btn::save_close')}}
                    </v-btn>
                    <v-btn v-if="btnSave" color="primary" small depressed @click="saveAction">
                        {{$t('btn::save')}}
                    </v-btn>
                    <template v-if="urlClose">
                        <v-btn v-if="btnCancel" small depressed :to="urlClose">
                            {{$t('btn::cancel')}}
                        </v-btn>
                    </template>
                    <template v-else>
                        <v-btn v-if="btnCancel" small depressed @click="closeAction">
                            {{$t('btn::cancel')}}
                        </v-btn>
                    </template>
                </v-card-actions>
            </template>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'AppFormDialog',
    props: {
        value: Boolean,
        persistent: {
            type: Boolean,
            default: true
        },
        maxWidth: {
            type: String,
            default: '700px'
        },
        loading: Boolean,
        urlClose: Object,
        title: String,
        btnSaveClose: {
            type: Boolean,
            default: true,
        },
        btnSave: {
            type: Boolean,
            default: true,
        },
        btnCancel: {
            type: Boolean,
            default: true,
        },
        hideFormAction: {
            type: Boolean,
            default: false
        },
        contentClass: {
            type: String,
            default: 'pt-5'
        }
    },
    computed: {
        show: {
            get() {
                return this.value
            },
            set(value) {
                this.$emit('input', value)
            }
        }
    },
    methods: {
        closeAction() {
            this.$emit('close')
        },
        saveAction() {
            this.$emit('save')
        },
        saveCloseAction() {
            this.$emit('save-close')
        },
    }
}
</script>