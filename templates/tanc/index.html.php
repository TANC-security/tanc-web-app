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
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- jVectorMap -->
    <link href="css/maps/jquery-jvectormap-2.0.3.css" rel="stylesheet"/>

    <!-- Custom Theme Style -->
    <link href="<?php echo m_turl();?>css/custom.min.css" rel="stylesheet">
  </head>

  <body class="nav-off" data-base-url="<?php echo m_appurl();?>">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="<?php echo m_appurl();?>" class="site_title"><img src="<?php echo m_turl();?>logo-sm.png" height="32" width="32" style="margin-left:10px;"> <span>TANC!</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                  <li><a href="<?php echo m_appurl('kp');?>"><i class="fa fa-table"></i> Keypad </a>
                  </li>
                  <li><a href="<?php echo m_appurl('rules');?>"><i class="fa fa-envelope"></i> Notifications </a>
                  </li>
                  <li><a href="<?php echo m_appurl('config');?>"><i class="fa fa-wifi"></i> Wifi </a>
                  </li>
                  <li><a href="<?php echo m_appurl('update');?>"><i class="fa fa-gears"></i> Update </a>
                  </li>
                </ul>
              </div>

            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav class="" role="navigation">
<div class="nav toggle">
<a id="menu_toggle">
<i class="fa fa-bars"></i>
</a>
</div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                 <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Profile</a></li>
                    <li>
                      <a href="javascript:;">
                        <span class="badge bg-red pull-right">50%</span>
                        <span>Settings</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Help</a></li>
                    <li><a href="login.html"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>

                <li role="presentation" class="dropdown">
                  <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-envelope-o"></i>
					<!--
                    <span class="badge bg-green">6</span>
					-->
                  </a>
                  <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="text-center">
                        <a>
                          <strong>See All Alerts</strong>
                          <i class="fa fa-angle-right"></i>
                        </a>
                      </div>
                    </li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <!-- top tiles -->
          <div class="row tile_count">
          </div>
          <!-- /top tiles -->
          <div class="row" id="content-sparkmsg">
          <div class="col-sm-12">
          <?php echo Metrofw_Template::parseSection('sparkmsg'); ?>
          </div>
          </div>
          <div class="row" id="content-main">
          <div class="col-sm-12">
			<?php echo Metrofw_Template::parseSection('main'); ?>
          </div>
          </div>
          <br />
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <!-- Bootstrap -->
    <script src="<?php echo m_turl();?>js/plugins.js"></script>

    <script src="<?php echo m_turl();?>scripts/healthcheck.js"></script>
    <!-- FastClick -->
	<!--
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
	-->
    <!-- NProgress -->
	<!--
    <script src="../vendors/nprogress/nprogress.js"></script>
	-->
    <!-- Chart.js -->
	<!--
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
	-->
    <!-- gauge.js -->
	<!--
    <script src="../vendors/bernii/gauge.js/dist/gauge.min.js"></script>
	-->
    <!-- iCheck -->
	<!--
    <script src="../vendors/iCheck/icheck.min.js"></script>
	-->
    <!-- Skycons -->
	<!--
    <script src="../vendors/skycons/skycons.js"></script>
	-->
    <!-- Flot -->
	<!--
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
	-->
    <!-- Flot plugins -->
	<!--
    <script src="js/flot/jquery.flot.orderBars.js"></script>
    <script src="js/flot/date.js"></script>
    <script src="js/flot/jquery.flot.spline.js"></script>
    <script src="js/flot/curvedLines.js"></script>
	-->
    <!-- jVectorMap -->
	<!--
    <script src="js/maps/jquery-jvectormap-2.0.3.min.js"></script>
	-->
    <!-- bootstrap-daterangepicker -->
	<!--
    <script src="js/moment/moment.min.js"></script>
    <script src="js/datepicker/daterangepicker.js"></script>
	-->

    <!-- Custom Theme Scripts -->
    <script src="<?php echo m_turl();?>js/custom.js"></script>
    <script src="<?php echo m_turl();?>scripts/form.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.js"></script>

	<?php echo Metrofw_Template::parseSection('extraJs'); ?>
  </body>
</html>
