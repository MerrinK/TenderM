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

    }else{
            // header("Location: login.php");
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

    <title>Tender - Forgot Passoword</title>
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
                            <!-- <div class="col-lg-6 d-none d-lg-block bg-login-image"></div> -->
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Forgot Password </h1>
                                    </div>
                                    <form class="user" id="ForgotPasswordForm" method="post">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="email"  name="email" aria-describedby="emailHelp"
                                                placeholder="Enter your email" >
                                        </div>

                                        <button class="btn btn-danger btn-user btn-block" type="submit" id="SubmitButton">Reset password</button>
                                         <button class="btn btn-danger btn-user btn-block d-none"  id="LoadingBtn" disabled>
                                            <span class="spinner-border spinner-border-sm"></span>
                                            Please Wait...
                                        </button>
                                        <hr/>
                                        <div class="text-center">
                                            <a class="small" href="login.php">Already have an account? Login!</a>
                                        </div>

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
    $('#ForgotPasswordForm').validate({
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');

        var form = $("#ForgotPasswordForm");
        var data = new FormData(form[0]);
        data.append('function', 'Employees');
        data.append('method', 'ForgotPassword');

        successFn = function(resp)  {
            // alert(resp.data);
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            Swal.fire(
              'Success!',
              'Please Click the link in your mail to reset your password',
              'success'
            );
            $('#SubmitButton').removeClass('d-none');
            $('#LoadingBtn').addClass('d-none');

            // setTimeout(function () {
            //     header("Location: logout.php");
            // }, 1000);
          }
        }
       
        apiCallForm(data,successFn);
      },
      rules: {
        email:{
          required: true,
          email: true,
        }
      },
      messages: {
        email: {
          required: "Please enter a valid email address."
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
    width: 100%
}
</style>
</html>