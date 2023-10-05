<v-layout row wrap class="grey lighten-5">
	<v-flex xs12 md12>
		<v-layout align-center justify-center class="pt-5">
			<v-flex xs12 md4 lg3>
				<div class="text-xs-center">
					<?php echo Asset::img('core::logo_kg.png', 'logo kalla', ['style' => 'width:150px;']) ?>
				</div>

				<div class="headline font-weight-light text-xs-center mt-4 mb-4">
					Reset password
				</div>

				<?php echo form_open('', ['method' => 'post', 'ref' => 'formLogin']); ?>
					<v-card class="mb-4">
						<?php if ($this->session->flashdata('message.error')) { ?>
							<v-alert :value="true">
								<?php echo $this->session->flashdata('message.error'); ?>
							</v-alert>
						<?php } ?>
						<?php if ($this->session->flashdata('message.success')) { ?>
							<v-alert :value="true" type="success">
								<?php echo $this->session->flashdata('message.success'); ?>
							</v-alert>
						<?php } ?>

						<v-card-text class="pl-4 pr-4">
							<v-text-field autofocus
								type="password"
								color="success"
								label="<?php echo lang('auth::password_new') ?>"
								placeholder="<?php echo lang('auth::password_new_placeholder') ?>"
								name="identityPassword"
								error-messages="<?php echo form_error('identityPassword') ?>"
							></v-text-field>
							<v-text-field autofocus
								type="password"
								color="success"
								label="<?php echo lang('auth::password_new_confirm') ?>"
								placeholder="<?php echo lang('auth::password_new_confirm_placeholder') ?>"
								name="identityPasswordConfirm"
								error-messages="<?php echo form_error('identityPasswordConfirm') ?>"
							></v-text-field>

							<?php 
							echo form_hidden($csrf); 
							echo form_hidden('userId', $userId); 
							?>

							<v-btn block depressed color="success" class="mb-3" type="submit">
								<?php echo lang('auth::reset_btn'); ?>
							</v-btn>

							<div class="text-xs-center">
								<?php 
								echo sprintf(
									'<a href="%s" class="d-inline-block">%s</a>',
									site_url('login'),
									'Back to login page'
								);
								?>
							</div>
						</v-card-text>
					</v-card>
				<?php echo form_close(); ?>

				<v-card class="mb-4">
					<v-card-text class="text-xs-center">
						Are you new here?
						<?php echo anchor('join', 'Create an account'); ?>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
	</v-flex>

	<v-flex xs12 md12>
		<div class="text-xs-center">
			<?php foreach ($menu_items as $menu_item) {
			echo sprintf(
				'<v-btn href="%s" color="success" small depressed flat>%s</v-btn>',
				$menu_item['url'],
				$menu_item['title']
			);
		} ?>
		</div>
	</v-flex>
</v-layout>