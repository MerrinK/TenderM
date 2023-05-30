<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title id="ProjectTitle"><?php (isset($page_title)) ? print $page_title  : print "Devengineers - Tender ";  ?></title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="plugins/fontawesome-free/css/solid.min.css" rel="stylesheet" type="text/css">

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">


    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

  
</head>



<body id="page-top" >
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion  toggled" id="accordionSidebar">

            <!-- Sidebar - Brand -->

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">

                <div class="sidebar-brand-icon ">

                <!-- <div class="sidebar-brand-icon rotate-n-15"> -->



                    <!-- <i class="fas fa-laugh-wink"></i> -->

                    <img src="assets/img/logo.png" width="50px" height="50px">

                </div>

                <div class="sidebar-brand-text mx-3">Devengineers</div>

            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item  nav-active-control" id="nav-dashboard">
                <a class="nav-link" href="dashboard.php" >
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>

            </li>
            <hr class="sidebar-divider">



            <?php if($_SESSION['IS_ADMIN']==1){ ?>
                <li class="nav-item nav-active-control" id="nav-employees">
                    <a class="nav-link" href="employees.php" >
                        <i class="fas fa-users fa-users-alt"></i>
                        <span>Employees</span></a>
                </li>
                <hr class="sidebar-divider">
            <?php }?>

            <?php if($_SESSION['ROLE_ID']!=5){ ?>
                <li class="nav-item nav-active-control" id="nav-vendors">
                    <a class="nav-link" href="vendors.php">
                        <i class="fas fa-user-tag fa-user-tag-alt"></i>
                        <span>Vendors</span></a>
                </li>
                <hr class="sidebar-divider">

            <?php }?>

 

            <li class="nav-item  nav-active-control" id="nav-tenders">
                <a class="nav-link" href="tender.php"  >
                    <i class="fas fa-copy fa-copy-alt"></i>
                    <span>Tenders</span></a>
            </li>
            <hr class="sidebar-divider">


            <li class="nav-item  nav-active-control" id="nav-Inventory">
                <a class="nav-link" href="Inventory.php">
                    <i class="fas fa-copy fa-copy-alt"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <hr class="sidebar-divider">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>


        </ul>
        <!-- End of Sidebar -->


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item">
                            <a href="javascript:void(0);" class="nav-link btn-link"> welcome :&nbsp &nbsp <span class="text-info"><?php echo $_SESSION['USER_FIRST_NAME'].' '.$_SESSION['USER_LAST_NAME']; ?> </span></a>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0);" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['ROLE']; ?></span>
                                <img class="img-profile rounded-circle" src="assets/img/undraw_profile.svg">
                            </a>

                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="javascript:void(0);">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <!-- <a class="dropdown-item" href="logout.php"> -->
                                <a class="dropdown-item" href="javascript:void(0);" onclick="logout()">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                                <!-- <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#logoutModal">

                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>

                                    Logout

                                </a> -->

                            </div>
                        </li>

                    </ul>
                </nav>
                <!-- End of Topbar -->

                <div class="modal fade " id="BaseModal"  role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-backdrop="static"></div>







                <div class="modal fade " id="imagemodal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-dialog  modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Image preview</h4>
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      </div>
                      <div class="modal-body text-center">
                        <div class="d-none " id="ForDescription"> Description : <span id="challan_description"></span> </div>
                        <div id="">
                            <img src="" id="imagepreview" style="width: 100%; height:100%" >
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default ml-4" data-dismiss="modal">Close</button>
                        <a type="button" href="javascript:void(0);" rel="nofollow" class="btn btn-info" id="DownloadImage">Download</a>
                      </div>
                    </div>
                  </div>
                </div>



                <div class="modal fade " id="imagemodalNew" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-dialog  modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Image preview</h4>
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      </div>
                      <div class="modal-body text-center">
                        <div class="d-none " id="ForDescription"> Description : <span id="challan_description"></span> </div>
                        <div id="LoadNewImage">
                            <img src="" id="imagepreviewNew" style="width: 100%; height:100%" >
                        </div>
                        <input type="hidden" id="Hid_imageSrc">

                      </div>
                      <div class="modal-footer">
                        <div class="w3-center ">
                            <input type="hidden" id="rotateAngle2" name="rotateAngle2" value='0'>
                            <input type="hidden" id="Hid_ImageId" name="Hid_ImageId" >
                            <input type="hidden" id="Hid_table" name="Hid_table" >
                            <button class="btn btn-info degreeBtn" onClick="rotateImage('-90')"> &#8635; </button>
                            <button class="btn btn-info degreeBtn" onClick="rotateImage('90')"> &#8634;</button>
                            <!-- <button class="btn btn-info" onClick="rotateImage(180)"> &#8631;</button> -->
                            <!--  <button class="btn btn-primary d-none"  id="LoadingBtn" disabled>
                                <span class="spinner-border spinner-border-sm"></span>
                                Please Wait...
                              </button> -->
                        </div>
                        <div class=" d-sm-none">

                        </div>

                        <button type="button" class="btn btn-default ml-4" data-dismiss="modal">Close</button>
                        <a type="button" href="javascript:void(0);" rel="nofollow" class="btn btn-info" id="DownloadImageNew">Download</a>
                      </div>
                    </div>
                  </div>
                </div>