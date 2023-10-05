<v-content>
    <v-container class="mb-5">
        <div class="headline font-weight-bold mb-4">
        <?php echo lang('search::search_result'); ?>
        </div>

        <v-row class="mb-4">
            <v-col cols="12">
                <v-list three-line class="search-result-list">
                    <v-subheader>
                        <?php echo sprintf(lang('search::search_result_count'), $total_result, $query); ?>
                    </v-subheader>

                    <?php foreach ($results as $key => $result) { ?>
                        <v-list-item>
                            <v-list-item-content>
                                <v-list-item-title>
                                    <?php echo anchor($result->uri, $result->title); ?>
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    <?php echo word_limiter(trim($result->description), 50); ?>
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                        <?php if ($key !== count($results) - 1) { ?>
                        <v-divider></v-divider>
                        <?php } ?>
                    <?php } ?>
                </v-list>
            </v-col>
        </v-row>

        <div id="paging">
            <?php echo $page_links; ?>
        </div>
    </v-container>
</v-content>