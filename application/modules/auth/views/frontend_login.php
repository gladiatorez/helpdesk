<v-row row wrap class="grey lighten-5">
	<v-col cols="12">
		<v-row align="center" justify="center" class="pt-5">
			<v-col cols="12" md="4" lg="3">
				<div class="text-center">
					<?php echo Asset::img('core::logo_kg.png', 'logo kalla', ['style' => 'width:150px;']) ?>
				</div>

				<div class="headline font-weight-light text-center mt-4 mb-4">
					Sign in to <?php echo $site_name_full; ?>
				</div>

				<?php echo form_open('', ['method' => 'post', 'ref' => 'formLogin']); ?>
					<v-card class="mb-4">
						<v-card-text>
							<?php if ($this->session->flashdata('message.error')) { ?>
								<v-alert :value="true">
									<?php echo $this->session->flashdata('message.error'); ?>
								</v-alert>
							<?php } ?>
						</v-card-text>
						<v-card-text class="pl-4 pr-4">
							<v-text-field autofocus
								color="success"
								label="<?php echo lang('auth::identity') ?>"
								placeholder="email@kalla.co.id"
								name="user_login"
								value="<?php echo set_value('user_login') ?>"
								error-messages="<?php echo form_error('user_login') ?>"
							></v-text-field>
							<v-text-field
								color="success"
								type="password"
								name="user_password"
								label="<?php echo lang('auth::password'); ?>"
								placeholder="<?php echo lang('auth::password_placeholder'); ?>"
							></v-text-field>
							<div style="display: none">"		
							 <v-checkbox
								class="blue-grey--text text--darken-4 mt-0"
								color="success"
								v-model="rememberMe"
								label="<?php echo lang('auth::remember_me') ?>"
							></v-checkbox>

							<input type="hidden" name="remember_me" value="0" ref="rememberMe" /> </div>

							<?php 
							echo form_hidden($csrf); 
							?>

							<v-btn block depressed color="success" class="mb-3" @click="doLogin">
								<?php echo lang('auth::login_btn'); ?>
							</v-btn>

							<div class="text-center">
								<?php 
								echo sprintf(
									'<a href="%s" class="d-inline-block body-2">%s</a>',
									site_url('auth/forgot-password'),
									lang('auth::forgot_password')
								);
								?>
							</div>
						</v-card-text>
					</v-card>
				<?php echo form_close(); ?>

				<v-card class="mb-4">
					<v-card-text class="text-center body-2">
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
				'<v-btn href="%s" color="success" small depressed text>%s</v-btn>',
				$menu_item['url'],
				$menu_item['title']
			);
		} ?>
		</div>
	</v-col>
</v-row>