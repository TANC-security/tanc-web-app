<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo _get('site_title');?> | <?php echo _get('page_title');?></title>

    <!-- Bootstrap -->
    <!-- Font Awesome -->
    <!-- iCheck -->
    <link href="<?php echo m_turl();?>css/plugins-css.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?php echo m_turl();?>css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login" data-base-url="<?php echo m_appurl();?>">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <?php echo Metrofw_Template::parseSection('sparkmsg'); ?>
      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
		  <form action="<?php echo m_appurl('dologin');?>" method="POST">
              <h1>Login Form</h1>
              <div>
                <input type="text" class="form-control" placeholder="Username" required="" name="email" />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="Password" required="" name="password" />
              </div>
              <div>
                <button type="submit" class="btn btn-default submit">Log in</button>
                <a class="reset_pass" href="#">Lost your password?</a>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <p class="change_link">New to site?
                  <a href="#signup" class="to_register"> Create Account </a>
                </p>

                <div class="clearfix"></div>
                <br />

                <div>
                  <h1><img src="<?php echo m_turl();?>logo-sm.png" height="32" width="32" style="margin-left:10px;">TANC!</h1>
                  <p>©2016 All Rights Reserved. Gentelella Alela! is a Bootstrap 3 template. Privacy and Terms</p>
                </div>
              </div>
            </form>
          </section>
        </div>

        <div id="register" class="animate form registration_form">
          <section class="login_content">
            <form>
              <h1>Create Account</h1>
              <div>
                <input type="text" class="form-control" placeholder="Username" required="" />
              </div>
              <div>
                <input type="email" class="form-control" placeholder="Email" required="" />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="Password" required="" />
              </div>
              <div>
                <button type="submit" class="btn btn-default submit">Submit</button>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <p class="change_link">Already a member ?
                  <a href="#signin" class="to_register"> Log in </a>
                </p>

                <div class="clearfix"></div>
                <br />

                <div>
                  <h1><img src="<?php echo m_turl();?>logo-sm.png" height="32" width="32" style="margin-left:10px;">TANC!</h1>
                  <p></p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
    <!-- jQuery -->
    <!-- Bootstrap -->
    <script src="<?php echo m_turl();?>js/plugins.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>


    <script src="<?php echo m_turl();?>scripts/sslcheck.js" type="text/javascript"></script>

  </body>
</html>
