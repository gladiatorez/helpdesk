<faq-categories-drawer></faq-categories-drawer>

<v-content class="has-left-drawer">
    <faq-categories-menu class="hidden-md-and-up"></faq-categories-menu>

    <v-container class="mb-5">
        <div class="headline font-weight-bold mb-4">
            <?php echo sprintf('FAQ :: %s', $category['name']); ?>
        </div>

        <?php if ($faq_list) { ?>
            <v-expansion-panels accordion multiple flat>
                <?php foreach ($faq_list as $faq) { ?>
                    <v-expansion-panel>
                        <v-expansion-panel-header class="pl-0">
                            <span class="body-1 kallaGreen--text"><?php echo $faq->title; ?></span>
                        </v-expansion-panel-header>
                        <v-expansion-panel-content>
                            <div class="body-2 ml-n3">
                                <?php echo $faq->description; ?>
                            </div>
                            <v-card flat shaped outlined class="ml-n3">
                                <v-card-actions class="mb-3 pa-3">
                                    <?php if (!$faq->is_rate) : ?>
                                        <rate-form title="<?php echo lang('frontend::faq::are_info_help'); ?>" param-id="<?php echo $faq->id; ?>" label-yes="Ya" label-no="Tidak"></rate-form>
                                    <?php endif; ?>
                                    <v-spacer></v-spacer>
                                    <v-btn dark depressed rounded small href="<?php echo site_url('faq/item/' . $faq->slug) ?>" color="kallaGreen">
                                        Lihat penuh
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-expansion-panel-content>
                        <v-divider></v-divider>
                    </v-expansion-panel>
                <?php } ?>
            </v-expansion-panels>

            <div id="paging">
                <?php echo $page_links; ?>
            </div>
        <?php } else { ?>
            <v-alert :value="true" color="warning" icon="info" outline>
                <?php echo lang('frontend::faq::empty_results'); ?>
            </v-alert>
        <?php } ?>
    </v-container>
</v-content>