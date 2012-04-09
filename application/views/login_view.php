<div class="container login_view">

  <div class="content">
    <div class="page-header">
      <h1>Login</h1>
    </div>


<?php echo form_open('admin'); ?>
    <div class="row">
        <div class="span6">
            <div class="clearfix">
               <?php echo form_label('Username: ', 'username');?>
               <div class="input">
               <?php
                     $data = array(
                          'name'        => 'username',
                          'id'          => 'username',
                          'value'       =>  set_value('username'),
                          'maxlength'   => '20',
                          'size'        => '30',
                          'class'       => 'large',
                          'required'    => 'required',
                          'autofocus'   => 'autofocus'
                    );
                  echo form_input($data);
                ?>
               </div>
            </div>

               <?php echo form_label('Password: ', 'password');?>
               <div class="input">
               <?php
                     $data = array(
                          'name'        => 'password',
                          'id'          => 'password',
                          'maxlength'   => '20',
                          'size'        => '30',
                          'type'        => 'password',
                          'class'       => 'large',
                          'required'    => 'required'
                    );
                    echo form_input($data);
                ?>
               </div>
               <input type="submit" value="Login" class="btn large primary" id="submit">

        </div>
        <div class="span4">
                <?php echo validation_errors('<div class="alert-message error">', '</div>'); ?>
        </div>
    </div>

<?php echo form_close(); ?>


<!--
        </div>
      </div>
 -->

    </div>