<v-content>
	<v-container class="mb-5" id="registration-page">
    <div class="display-1 font-weight-light">
      <?php echo lang('frontend::auth:register_heading'); ?>
    </div>
		<div class="font-weight-light mb-4">
			<?php echo lang('frontend::auth:register_heading_descr'); ?>
		</div>

		<?php 
		echo file_partial('alert'); 
		
		echo form_open();
		echo form_hidden($csrf); 
		?>

			<join-form></join-form>
		<?php echo form_close(); ?>

	</v-container>
</v-content>