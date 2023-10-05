<div class="container-fluid">
  <div class="login-form row">
    <div class="col-md-4 mt-3 offset-md-4">

      <div class="text-center mt-5 mb-3">
      <?php echo Asset::img('core::logo_kg.png', 'logo kalla group', ['class' => 'logo']); ?>
      </div>

      <div class="card">
        <div class="card-header d-flex">
          <div class="icon-separator align-items-center d-flex">CICT</div>
          <div class="flex-grow-1">
            <h5>Reset password</h5>
          </div>
        </div>
        <div class="card-body">
          
          <?php if (!empty($error_msg)) { ?>
          <div class="alert alert-danger">
            <?php echo $error_msg; ?>
          </div>
          <?php } ?>
            <?php if (!empty($success_msg)) { ?>
              <div class="alert alert-success">
                  <?php echo $success_msg; ?>
              </div>
            <?php } ?>

          <?php echo form_open(); ?>
          <div class="form-group">
            <label for="identityPassword"><?php echo lang('auth::password_new') ?></label>
            <input type="password" 
              id="identityPassword"
              name="identityPassword"
              class="form-control<?php echo formInvalid('identityPassword'); ?>"
              placeholder="<?php echo lang('auth::password_new_placeholder'); ?>">
            <?php echo formInvalidFeedback('identityPassword'); ?>
          </div>

          <div class="form-group">
            <label for="identityPasswordConfirm"><?php echo lang('auth::password_new_confirm') ?></label>
            <input type="password" 
              id="identityPasswordConfirm"
              name="identityPasswordConfirm"
              class="form-control<?php echo formInvalid('identityPasswordConfirm'); ?>"
              placeholder="<?php echo lang('auth::password_new_confirm_placeholder'); ?>">
            <?php echo formInvalidFeedback('identityPasswordConfirm'); ?>
          </div>

          <?php 
          echo form_hidden($csrf);
          echo form_hidden('userId', $userId); 
          ?>

          <div class="form-group">
            <button type="submit" class="btn btn-login btn-success btn-block">
              Submit
            </button>
          </div>
          <div class="text-xs-center">
            <?php 
            echo sprintf(
              '<a href="%s" class="d-inline-block">%s</a>',
              site_url_backend('auth'),
              'Back to login page'
            );
            ?>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</div>