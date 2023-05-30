<?php
    session_start(); 
    

    if(isset($_SESSION['SITE']) && $_SESSION['SITE']='NEWPROJECT'){
        if(isset($_SESSION['USER_NAME']) && $_SESSION['USER_NAME']!=""){
            header("Location: dashboard.php");
        }
    }else if(isset($_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] ) &&  $_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] !='' ){
        require_once("config.php");
        require_once("classes/Employees.php");
        
        $fn = new Employees($dbc);
        $fn->login();
    }
        
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tender - Login</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
   

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome </h1>
                                    </div>
                                    <form class="user" id="LoginForm" method="post">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user"
                                                id="user_name"  name="user_name" aria-describedby="emailHelp"
                                                placeholder="Enter the user name" value="">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="password"  name="password" placeholder="Password" value="">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="RememberMe"  name="RememberMe" value="1">
                                                <label class="custom-control-label" for="RememberMe">Remember Me</label>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary btn-user btn-block" type="submit">Login</button>
                                        <hr/>
                                        <a  class="btn btn-danger btn-user btn-block" href="forgotPassword.php" >Forgot Password</a>

                                    </form>  
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>
    
    <script type="text/javascript" src="js/appbase.js"></script>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" type="text/css" href="plugins/sweetalert2/sweetalert2.min.css">


</body>

<script type="text/javascript">

$(document).ready(function() {
    $('#LoginForm').validate({
      submitHandler: function(form) {
        var form = $("#LoginForm");
        var data = new FormData(form[0]);
        data.append('function', 'Employees');
        data.append('method', 'Login');

        successFn = function(resp)  {
          if(resp.status==0){
            if(resp.data==2){
                $('#user_name').addClass('is-invalid');
                $('#user_name').closest('.form-group').append('<span id="UserNameSpan" class="error invalid-feedback">Incorrect User Name</span>');
            }else if(resp.data==3){
                $('#password').addClass('is-invalid');
                $('#password').closest('.form-group').append('<span id="UserNameSpan" class="error invalid-feedback">Incorrect Password</span>');
            }
          }else if(resp.status==1){
            // alert('login successfull');

            // Swal.fire(
            //   'Welcome!',
            //   'You are successfully logged in !...',
            //   'success'
            // );
            // setTimeout( function() {
                location.reload();
            // }, 300); 
          }
        }
       
        apiCallForm(data,successFn);
      },
      rules: {
        user_name:{
          required: true,
        },
        password:{
          required: true,
        }
      },
      messages: {
        user_name: {
          required: "Please enter the user name"
        },
       
        password: {
          required: "Please enter the password"
        }

      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    });
  

});

</script>
<style type="text/css">
.error {
    color: #e74a3b !important ;
    font-size: 1rem !important ;
}
</style>
</html>