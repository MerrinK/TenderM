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

  // print "<script>$( document ).ready(function() {setTimeout(function () { Tenders('nav-tenders');}, 800); });</Script>";
	print "<script>$( document ).ready(function() {
	  $.get('template/Tender.htm', function(templates) {
	      Tender = templates;
	      Tenders('nav-tenders');
	      // Order_BOQ(45,2);
	      // Purchase_OrderedRequest(12)
	      // BOQs(2)
	      // Purchase_OrderedRequest(1);
	      // PlaceRequestPO(397,6);
	      // Purchase_OrderedRequest(53);
	      // ConfirmBOQsPO(53);
	      // UploadBillLabour('6');
	      // View_TenderDetails('6','BUS STOP','Tenders');
	      // Expenses();
	      // addNewExpenses();
	      // rotate('C:/xampp7.4/htdocs/Tender/UploadDoc/333/Expense/UploadDoc/333/Expense/UploadExpensesBills_9.JPG');
	      


	  });
	});</Script>";

	 print "<script>$( document ).ready(function() {
	  $.get('template/jtemplates.htm', function(templates) {
	      jtemplates = templates;
	      // dashboard('nav-dashboard');
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



