<div class="container-fluid">
  <div class="login-form row">
    <div class="col-md-4 mt-3 offset-md-4">

      <div class="text-center mt-5 mb-3">
      <?php echo Asset::img('core::logo_kg.png', 'logo kalla group', ['class' => 'logo']); ?>
      </div>

      <div class="card">
        <div class="card-header d-flex">
          <div class="icon-separator align-items-center d-flex"></div>
          <div class="flex-grow-1">
            <?php
            echo sprintf('<h5>%s %s <small>%s</small></h5>', $siteNameFull, lang('auth::backend_heading'), lang('auth::backend_description'));
            ?>
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
            <input type="hidden" name="redirect" id="redirect-input">
          <div class="form-group">
            <label for="user_login"><?php echo lang('auth::identity') ?></label>
            <input type="text" name="user_login" id="user_login"
                  value="<?php echo set_value('user_login') ?>"
                  placeholder="<?php echo lang('auth::identity_placeholder'); ?>"
                  class="form-control<?php echo formInvalid('user_login'); ?>"
                  autofocus autocomplete="off">
            <?php echo formInvalidFeedback('user_login'); ?>
          </div>

          <div class="form-group">
            <label for="user_login"><?php echo lang('auth::password') ?></label>
            <input type="password" id="user_password"
                  name="user_password"
                  class="form-control<?php echo formInvalid('user_password'); ?>"
                  placeholder="<?php echo lang('auth::password_placeholder'); ?>">
            <?php echo formInvalidFeedback('user_password'); ?>
          </div>

          <div class="form-group row">
            <div class="col-md-6 mb-md-0 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="remember-check" name="remember">
                <label class="form-check-label" for="remember-check">
                    <?php echo lang('auth::remember_me'); ?>
                </label>
              </div>
            </div>
            <div class="col-md-6 text-md-right text-left">
                <?php echo anchor(BACKEND_URLPREFIX . '/auth/forgotten_password', lang('auth::forgot_password')) ?>
            </div>
          </div>

          <div class="form-group">
            <button class="btn btn-login btn-primary btn-block"><?php echo lang('auth::login_btn'); ?></button>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    const redirect = Cookies.get('macca_redirect')
    if (redirect) {
        document.querySelector('input[name="redirect"]').value = redirect.replace('???', '#')
        Cookies.remove('macca_redirect')
    }
</script>