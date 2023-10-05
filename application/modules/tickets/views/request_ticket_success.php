<v-content>
	<v-container class="mb-5" id="request-ticket-page" grid-list-xl>
		<div class="display-1 font-weight-light">
			Request ticket
		</div>
		<div class="font-weight-light mb-4">
			<?php echo lang('frontend::auth:register_heading_descr'); ?>
		</div>
		<v-layout row wrap>
			<v-flex xs12 md9>
				<V-card color="teal lighten-5" elevation="0" class="teal--text">
					<v-card-text>
						<div class="text-xs-center">
							<h3 class="display-1 font-weight-light">Well done!</h3>
							<h4 class="headline mb-4">Your ticket problem has been successfully created.</h4>

							<p>
								Ticket numbers & ticket details can be seen in the email inbox you registered or you can 
								<?php echo anchor('login', 'Login') ?> the Macca system through your account.<br/>
								We will respond to the ticket you sent as soon as possible.
							</p>
						</div>
					</v-card-text>
				</V-card>
			</v-flex>
		</v-layout>
	</v-container>
</v-content>