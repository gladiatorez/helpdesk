<v-row row wrap class="grey lighten-5">
	<v-col cols="12">
		<v-row align="center" justify="center" class="pt-5">
			<v-col cols="12" md="4" lg="3">
				<div class="text-center">
					<?php echo Asset::img('core::logo_kg.png', 'logo kalla', ['style' => 'width:150px;']) ?>
				</div>

				<div class="headline font-weight-light text-center mt-4 mb-4">
					Forgotten password
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
								color="success"
								label="<?php echo lang('auth::identity') ?>"
								placeholder="email@kalla.co.id"
								name="login_reset"
								value="<?php echo set_value('login_reset') ?>"
								error-messages="<?php echo form_error('login_reset') ?>"
							></v-text-field>

							<?php 
							echo form_hidden($csrf); 
							?>

							<v-btn block depressed color="success" class="mb-3" type="submit">
								<?php echo lang('auth::forgot_btn'); ?>
							</v-btn>

							<div class="text-center">
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
					<v-card-text class="text-center">
						Are you new here?
						<?php echo anchor('join', 'Create an account'); ?>
					</v-card-text>
				</v-card>
			</v-col>
		</v-row>
	</v-col>

	<v-col cols="12">
		<div class="text-center">
			<?php foreach ($menu_items as $menu_item) {
			echo sprintf(
				'<v-btn href="%s" color="success" rounded small depressed text>%s</v-btn>',
				$menu_item['url'],
				$menu_item['title']
			);
		} ?>
		</div>
	</v-col>
</v-row>