<faq-categories-drawer></faq-categories-drawer>

<v-content app class="has-left-drawer">
    <faq-categories-menu class="hidden-md-and-up"></faq-categories-menu>

    <v-container class="mt-5 pb-5 mb-5">
        <div class="headline font-weight-bold mb-4">
            Questions that you might ask
        </div>

        <div>
            <?php if ($headline) {
                foreach ($headline as $item) { ?>
                    <a href="<?php echo site_url('faq/item/' . $item->slug); ?>" class="d-block mb-3 kallaGreen--text" style="text-decoration: none">
                        <?php echo $item->title; ?>
                    </a>
            <?php
                }
            } ?>
        </div>
    </v-container>
</v-content>