<?php if ($this->session->flashdata('message.error')) { ?>
	<v-alert :value="true" type="error" dismissible>
		<?php echo $this->session->flashdata('message.error'); ?>
	</v-alert>
<?php 
} ?>

<?php if ($this->session->flashdata('message.success')) { ?>
	<v-alert :value="true" type="success" dismissible>
		<?php echo $this->session->flashdata('message.success'); ?>
	</v-alert>
<?php 
} ?>

<router-view></router-view>