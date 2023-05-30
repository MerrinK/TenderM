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

	print "<script>$( document ).ready(function() {
	  $.get('template/Inventory.htm', function(templates) {
	      Inventory = templates;
	      InventoryTab('nav-Inventory');
	  });
	});</Script>";



?>



