<template>
    <div class="v-dialog--scrollable">
        <v-card>
            <v-card-title>
                Dicoba
            </v-card-title>
            <!-- <v-card-text class="pa-0 d-flex flex-column"
                style="overflow:hidden; flex: 1 1 100%; max-height: 100%;"> -->
                <v-simple-table fixed-header
                    class="v-card__text pa-0 d-flex flex-column"
                    style="overflow-y:hidden">
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th>Field 1</th>
                                <th>Field 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                            <tr><td>Body 1</td><td>Body 2</td></tr>
                        </tbody>
                    </template>
                </v-simple-table>
            <!-- </v-card-text> -->
        </v-card>
    </div>
</template>

<script>
export default {
    name: 'TestCard'
}
</script>