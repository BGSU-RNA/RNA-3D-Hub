<div class="container login_view">

  <div class="content">
    <div class="page-header">
      <h1>Please Login</h1>
    </div>
    <div class="row">

    <div class="span8 offset4">
<?php echo form_open('admin'); ?>
<p>
   <?php
      echo form_label('Login: ', 'username');
      echo form_input('username', set_value('username'), 'id="username" autofocus');
   ?>
</p>

<p>
   <?php
      echo form_label('Password:', 'password');
      echo form_password('password', '', 'id="password"');
   ?>
</p>

<p>
    <input type="submit" value="Login" class="btn primary" >
<!--    <?php echo form_submit('submit', 'Login'); ?>  -->
</p>
<?php echo form_close(); ?>

<?php echo validation_errors(); ?>

        </div>
      </div>

    </div>