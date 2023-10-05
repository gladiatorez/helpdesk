<faq-categories-drawer></faq-categories-drawer>

<v-content class="has-left-drawer">
    <faq-categories-menu class="hidden-md-and-up"></faq-categories-menu>

    <v-container class="mb-5">
        <div class="subheading success--text font-weight-bold mb-4">
            <?php echo sprintf('FAQ :: %s', $category['name']); ?>
        </div>

        <v-card flat>
            <v-card-title class="pl-0 pr-0">
                <div class="headline font-weight-bold">
                    <?php echo $faq->title; ?>
                </div>
            </v-card-title>
            <v-card-text class="faq-description black--text pl-0 pr-0">
                <?php echo $faq->description; ?>
            </v-card-text>
        </v-card>

        <v-card flat shaped outlined>
            <v-card-actions class="mb-3 pa-3">
                <?php if (!$faq->is_rate) : ?>
                    <rate-form title="<?php echo lang('frontend::faq::are_info_help'); ?>" param-id="<?php echo $faq->id; ?>" label-yes="Ya" label-no="Tidak"></rate-form>
                <?php endif; ?>
            </v-card-actions>
        </v-card>

    </v-container>
</v-content>