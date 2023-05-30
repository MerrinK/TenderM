<?php
session_start();
if( $_SESSION['USER_NAME']==""){
    header("Location: login.php");
}
// $youAreHere ="Home";

require_once('include/header.php');	
?>

	<div  class="container-fluid" id="Dashboard">         

   
          

	</div>

<?php
  require_once('include/footer.php');

  // print "<script>$( document ).ready(function() {setTimeout(function () { Vendors('nav-vendors');}, 1000); });</Script>";

	 print "<script>$( document ).ready(function() {
	  $.get('template/vendors.htm', function(templates) {
	      vendors = templates;
	      Vendors('nav-vendors');
	  });
	});</Script>";

	print "<script>$( document ).ready(function() {
	  $.get('template/jtemplates.htm', function(templates) {
	      jtemplates = templates;
	      // employees('nav-employees');
	  });
	});</Script>";

	print "<script>$( document ).ready(function() {
	  $.get('template/Tender.htm', function(templates) {
	      Tender = templates;
	      // Tenders('nav-tenders');
	  });
	});</Script>";

	 print "<script>$( document ).ready(function() {
	  $.get('template/jtemplates.htm', function(templates) {
	      jtemplates = templates;
	      // dashboard('nav-dashboard');
	  });
	});</Script>";

	
	
	
?>



<style>
	.select2-search__field{
		/*width: 100% !important;*/
		width:-webkit-fill-available !important;
	}
</style>

