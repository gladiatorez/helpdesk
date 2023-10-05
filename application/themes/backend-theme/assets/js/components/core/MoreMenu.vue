<template>
    <v-menu offset-y min-width="150px">
        <template v-slot:activator="{ on }">
            <v-btn icon small v-on="on" color="primary" style="height:27px">
                <v-icon>more_vert</v-icon>
            </v-btn>
        </template>

        <v-list dense>
            <slot></slot>
            <template v-if="editUrl">
                <v-list-item 
                    v-if="editBtn"
                    :to="editUrl">
                    <v-list-item-icon class="mr-3">
                        <v-icon>edit</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>Edit</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </template>
            <template v-else>
                <v-list-item 
                    v-if="editBtn"
                    @click="editAction">
                    <v-list-item-icon class="mr-3">
                        <v-icon>edit</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>Edit</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </template>
            
            <v-list-item
                v-if="removeBtn"
                @click="removeAction">
                <v-list-item-icon class="mr-3">
                    <v-icon color="error">delete</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                    <v-list-item-title class="error--text">Delete</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
        </v-list>
    </v-menu>
</template>

<script>
export default {
    name: 'CoreMoreMenu',
    props: {
        editBtn: Boolean,
        editUrl: Object | String,
        removeBtn: Boolean,
    },
    methods: {
        removeAction() {
            this.$emit('remove-action');
        },
        editAction() {
            this.$emit('edit-action');
        }
    }
}
</script>