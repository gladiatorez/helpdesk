<v-content>
	<v-container class="mb-5" id="request-ticket-page">
		<div class="display-1 font-weight-light">
			Request ticket
		</div>
		<div class="font-weight-light mb-4">
			<?php echo lang('frontend::auth:register_heading_descr'); ?>
		</div>
		<v-row>
			<v-col cols="12" md="9">
				<request-ticket-form :is-login="<?php echo frontendIsLoggedIn() ? 'true' : 'false' ?>"></request-ticket-form>
			</v-col>

            <v-col cols="12" md="3">
                <?php if (!frontendIsLoggedIn()) { ?>
                <div class="">
                    <span class="font-weight-bold info--text">Do you have an account? </span><br/>
                    <span class="font-weight-light">If not, Please join by pressing the "join" button in the top menu</span>
                </div>
                <?php } ?>
            </v-col>
		</v-row>
	</v-container>
</v-content>