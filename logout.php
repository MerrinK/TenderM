<?php
  session_start();

  unset($_SESSION['SITE']);
  unset($_SESSION['USER_ID']);
  unset($_SESSION['USER_NAME']);
  unset($_SESSION['USER_EMAIL']);
  unset($_SESSION['ROLE']);

  // session_destroy();

  setcookie('KEEP_ME_LOGGED_IN_USER_NAME',"", time()-3600,'/');
  setcookie('KEEP_ME_LOGGED_IN_PASSWORD',"", time()-3600,'/');
  header('Location: index.php');

  exit;
?>