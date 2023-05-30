<?php
session_start();
if( $_SESSION['USER_NAME']==""){
    header("Location: login.php");
}
// $youAreHere ="Home";

require_once('include/header.php');	
?>

	<div  class="container-fluid small-device" id="Dashboard" ></div>

<?php
  require_once('include/footer.php');
  // print "<script>$( document ).ready(function() {setTimeout(function () { dashboard('nav-dashboard');}, 200); });</Script>";
	 print "<script>$( document ).ready(function() {
	  $.get('template/jtemplates.htm', function(templates) {
	      jtemplates = templates;
	      dashboard('nav-dashboard');
	  });
	});</Script>";

	print "<script>$( document ).ready(function() {
	  $.get('template/Tender.htm', function(templates) {
	      Tender = templates;
	      // Tenders('nav-tenders');
	  });
	});</Script>";

	  print "<script>$( document ).ready(function() {
	  $.get('template/vendors.htm', function(templates) {
	      vendors = templates;
	      // Vendors('nav-vendors');
	  });
	});</Script>";

		print "<script>$( document ).ready(function() {
	  $.get('template/jtemplates.htm', function(templates) {
	      jtemplates = templates;
	      // employees('nav-employees');
	  });
	});</Script>";
?>

