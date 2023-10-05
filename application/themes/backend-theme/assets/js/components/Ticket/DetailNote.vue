<template>
    <div>
        <template v-if="notes">
            <template v-for="(note, index) in notes">
                <v-card flat :key="index">
                    <v-card-title class="pb-0">
                        <div class="body-2">
                            {{note.full_name}}
                            <small class="d-block text--secondary">{{ $moment(note.created_at).format('DD/MM/YYYY HH:mm') }}</small>
                        </div>
                    </v-card-title>
                    <v-card-text>
                        {{note.description}}
                    </v-card-text>
                </v-card>
                <v-divider :key="'divider_a' + index"></v-divider>
            </template>
        </template>
        <template v-else>
            <v-card flat>
                <v-card-text>
                    <v-alert :value="true" type="warning" dense text>
                        Empty results
                    </v-alert>
                </v-card-text>
            </v-card>
        </template>
    </div>
</template>

<script>
export default {
    name: 'DetailNote',
    props: {
        notes: Array,
    },
}
</script>