var TenderDetails;
$(document).ready(function() {
  $.get('template/TenderDetails.htm', function(templates) {
      TenderDetails = templates;
  });
 
});

function loadTenderDetailsTemplate(templateId, data, id="Dashboard"){
  var template = $(TenderDetails).filter(templateId).html();
  var rendered = Mustache.render(template, data);
  $('#'+id).html(rendered); 
}


function toMustacheDataObj(obj){
  obj.resetIndex = function() {
    window['INDEX']=0;
  }
  obj.index = function() {
    return ++window['INDEX']||(window['INDEX']=0);
  }
  obj.showBorder= function(){
    return ((window['INDEX'])%2 == 0) ? true: false;
  }
  return obj;
}


function View_TenderDetails(id, TenderName, location=''){
  $('[data-toggle="tooltip"]').tooltip('dispose');
  successFn = function(resp)  {

    loadTenderDetailsTemplate('#TenderDetailsTab_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#ViewTenderDetails-tab").click(function() {
      ViewTenderDetails_fn();
    });
    $("#TenderBOQ-tab").click(function() {
      TenderBOQDetails();
    });
    $("#AdditionalLabourCost-tab").click(function() {
      AdditionalLabourCostDetails();
    });
    $("#Details_PORequest-tab").click(function() {
      Details_PORequest();
    });
    $("#Details_PO-tab").click(function() {
      Details_PO();
    });
    $("#challanBills-tab").click(function() {
      challanBills();
    });
    $("#CreatedBills-tab").click(function() {
      CreatedBills();
    });

    $("#DailyProgress-tab").click(function() {
      DailyProgress();
    });
    $("#Expenses-tab").click(function() {
      Expenses();
    });

    $("#Voucher-tab").click(function() {
      Voucher();
    });

    $("#Documents-tab").click(function() {
      Documents();
    });

    if(resp.data.Accounts==1){
      $("#challanBills-tab").click();
    }else{
      $("#ViewTenderDetails-tab").click();
    }
    // $("#TenderBOQ-tab").click();
  }
  data = {"function": 'TenderDetails', "method":"CheckAccounts", "id":id,"TenderName":TenderName,"location":location};
  apiCall(data,successFn);
}

function OrderTenderBOQDetails(){
  $("#TenderBOQ-tab").click();
}

function ViewTenderDetails_fn(){
  id=$('#tender_id_details').val(); 
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {

    
    resp.data['Tender']["ExcludeMoonsoon"]=parseInt(resp.data['Tender']["ExcludeMoonsoon"]);
    resp.data['Tender']['EMDAmount'] = parseInt(resp.data['Tender']['EMDAmount']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
    });
    resp.data['Tender']['BankAmount'] = parseInt(resp.data['Tender']['BankAmount']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
    });
    resp.data['Tender']['ASDAmount'] = parseInt(resp.data['Tender']['ASDAmount']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
    });
    resp.data['Tender']['ContractDeposit'] = parseInt(resp.data['Tender']['ContractDeposit']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
    });
    // resp.data['Tender']['RetentionAmount'] = parseInt(resp.data['Tender']['RetentionAmount']).toLocaleString('en-IN', {
    //         maximumFractionDigits: 2,
    //         style: 'currency',
    //         currency: 'INR'
    // });

    resp.data['Tender']["TenderStartDate"]= moment(resp.data['Tender']["TenderStartDate"]).format('DD-MM-YYYY');
    resp.data['Tender']["TenderEndDate"]= moment(resp.data['Tender']["TenderEndDate"]).format('DD-MM-YYYY');
    resp.data['Tender']["EMDStartDate"]= moment(resp.data['Tender']["EMDStartDate"]).format('DD-MM-YYYY');
    if(resp.data['Tender']["ASDStartDate"] ==''){
      resp.data['Tender']["ASDStartDate"]='NA';
    }else{
      resp.data['Tender']["ASDStartDate"]=moment(resp.data['Tender']["ASDStartDate"]).format('DD-MM-YYYY');
    }
    if(resp.data['Tender']["ASDEndDate"] ==''){
      resp.data['Tender']["ASDEndDate"]='NA';
    }else{
      resp.data['Tender']["ASDEndDate"]=moment(resp.data['Tender']["ASDEndDate"]).format('DD-MM-YYYY');
    }
    resp.data['Tender']["ContractDepositExpiryDate"]= moment(resp.data['Tender']["ContractDepositExpiryDate"]).format('DD-MM-YYYY');
    resp.data['Tender']["ContractDepositIssueDate"]= moment(resp.data['Tender']["ContractDepositIssueDate"]).format('DD-MM-YYYY');


    for (var key in resp.data.RequestedMaterial) {
      resp.data.RequestedMaterial[key]["required_by"]= moment(resp.data.RequestedMaterial[key]["required_by"]).format('DD-MM-YYYY');
    }


    loadTenderDetailsTemplate('#viewTender2_templ',toMustacheDataObj(resp), 'ViewTenderDetails');

  }
  data = {"function": 'TenderDetails', "method":"fetchTenderID","id":id};
  apiCall(data,successFn);

}


function View_RequstedPO(PORequest_id){

  $('[data-toggle="tooltip"]').tooltip('dispose'); 
    // loadBOQTemplate('#EditTotalQty_templ',{}, 'BaseModal');
    // $('#BaseModal').modal('show');
  successFn = function(resp)  {
    loadBOQTemplate('#ViewPurchaseOrderRequest_templ',toMustacheDataObj(resp), 'BaseModal');
    
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();

  }
  data = {"function": 'TenderDetails', "method": 'View_RequstedPO', 'PORequest_id':PORequest_id};
  apiCall(data,successFn);

}

function Details_PORequest(){
  successFn = function(resp)  {
    for (var key in resp.data.BOQ) {
      resp.data.BOQ[key]["confirm"]=parseInt(resp.data.BOQ[key]["confirm"]);
      resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
      resp.data.BOQ[key]["approved"]=parseInt(resp.data.BOQ[key]["approved"]);
      resp.data.BOQ[key]["created_date"]= moment(resp.data.BOQ[key]["created_date"]).format('DD-MM-YYYY');
      resp.data.BOQ[key]["required_by"]= moment(resp.data.BOQ[key]["required_by"]).format('DD-MM-YYYY');
    }
    resp.data.admin=parseInt(resp.data.admin);
    loadTenderDetailsTemplate('#TenderDetails_PO_BOQ_templ',toMustacheDataObj(resp), 'Details_PORequest');
    $("#Dt_DashBOQ").DataTable({});
   $('[data-toggle="tooltip"]').tooltip();

  }
  data = {"function": 'TenderDetails', "method":"AllRequested_POs","tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);

}



function Details_PO(){
  successFn = function(resp)  {
    for (var key in resp.data.BOQ) {
        resp.data.BOQ[key]["status"]=parseInt(resp.data.BOQ[key]["status"]);
        resp.data.BOQ[key]["purchase"]=parseInt(resp.data.BOQ[key]["purchase"]);
        resp.data.BOQ[key]["reject"]=parseInt(resp.data.BOQ[key]["reject"]);
        resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);
        resp.data.BOQ[key]["TotalReceivedQty"]=parseInt(resp.data.BOQ[key]["TotalReceivedQty"])+parseInt(resp.data.BOQ[key]["difference"]);
        resp.data.BOQ[key]["pending_qty"]=parseInt(resp.data.BOQ[key]["pending_qty"])-parseInt(resp.data.BOQ[key]["difference"]);

        // resp.data.BOQ[key]["TotalReceivedQty"]=parseInt(resp.data.BOQ[key]["TotalReceivedQty"]);
        // resp.data.BOQ[key]["difference"]=parseInt(resp.data.BOQ[key]["difference"]);

        resp.data.BOQ[key]["required_by"]= moment(resp.data.BOQ[key]["required_by"]).format('DD-MM-YYYY');

        if(resp.data.BOQ[key]["pdf_location"]==""){
          resp.data.BOQ[key]["pdf_Available"]=0;
        }else{
          resp.data.BOQ[key]["pdf_Available"]=1;
        }
    }
    resp.data.admin=parseInt(resp.data.admin);

    loadTenderDetailsTemplate('#PO_BOQ_templ3',toMustacheDataObj(resp), 'Details_PO');
    $('[data-toggle="tooltip"]').tooltip();
    $("#Dt_PO_BOQ").DataTable({'order':[], dom: 'Blfrtip',
        buttons: [              
          { extend: 'print', messageTop: '<h3> Tender PO</h3>',className: 'btn btn-link btn-lg text-info ', 
                exportOptions: { columns: [0, 1, 2,3,4,5,6,7] }, titleAttr: 'Print' },
            { extend: 'pdfHtml5', messageTop: 'Tender',className: 'btn btn-link btn-lg text-danger ', 
                exportOptions: { columns: [0, 1, 2,3,4,5,6,7] }, titleAttr: 'PDF' },
        ], 
        initComplete: function() {
          $('.buttons-pdf').html('<i class="fa fa-file-pdf"/>');
          $('.buttons-print').html('<i class="fa fa-print"/>');
        }
    });
  }
  data = {"function": 'TenderDetails', "method":"SelectACtive_POs","tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}

function ApproveBOQsPO(id,boq_id,tender_id){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      Swal.fire({
        title: resp.data,
        showConfirmButton: false,
        timer: 1500
      });
      RequestedPO_tab(boq_id,tender_id);
    }
  }
  data = {"function": 'TenderDetails', "method":"Approve_BOQsPO","Po_id":id};
  apiCall(data,successFn);

}

function EditTotalReceivedQty(id, quantity){
  // $('[data-toggle="tooltip"]').tooltip('dispose');
  // $('[data-toggle="tooltip"]').tooltip();

 
  $(".popover").remove();  
     
    $("#PopoverTotalReceivedQty"+id).popover({
        title: "Edit Total Received Quantity <i class='fas fa-window-close text-muted float-right' onclick='ClosePopover()' title='Close'</i>",
        content: `<form id="EditTotalReceivedQty" ><table><tr>Quantity<td><input type="number" class="form-control" id="newQuantity" name="newQuantity" value="${quantity}"/></td><td><button type="button" class="btn btn-primary" onclick="updateReceivedQuantity(${id},${quantity})">Update</button</td></tr></table>`,
        html: true,
        sanitize: false
    }); 


    $('.myPopoverClass').popover({
        trigger: 'manual', /* <- important, instantiates popover */
        container: 'body', /* optional */
        animation: false
    }).click(function(e) {
        $("#PopoverTotalReceivedQty"+id).not(this).hide(); /* optional, hide other popovers */
        $(this).popover('show'); /* show popover now it's setup */
        e.preventDefault();
    });

    $("#PopoverTotalReceivedQty"+id).popover("show");

    $('body').on('click', function (e) {
      $('[data-toggle=popover]').each(function () {
        // hide any open popovers when the anywhere else in the body is clicked
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
            $(this).popover('hide');
            // $("#HidForTeamPopup").val('');

        }
      });
    });
    


}
function updateReceivedQuantity(id, quantity){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      Swal.fire({
        title: resp.data,
        showConfirmButton: false,
        timer: 1500
      });
      // Details_PO();
      $("#Details_PO-tab").click();

    }
  }
  data = {"function": 'TenderDetails', "method":"updateReceivedQuantity","id":id, "quantity":quantity,"newQuantity":$("#newQuantity").val()};
  apiCall(data,successFn);
}

function RejectBOQsPO(id,boq_id,tender_id){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      Swal.fire({
        title: resp.data,
        showConfirmButton: false,
        timer: 1500
      });
      RequestedPO_tab(boq_id,tender_id);
    }
  }
  data = {"function": 'TenderDetails', "method":"Reject_BOQsPO","Po_id":id};
  apiCall(data,successFn);
}


function ApproveBOQsPO_POR(id,tender_id){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      Swal.fire({
        title: resp.data,
        showConfirmButton: false,
        timer: 1500
      });
      Details_PORequest();
    }
  }
  data = {"function": 'TenderDetails', "method":"Approve_BOQsPO","Po_id":id};
  apiCall(data,successFn);

}

function RejectBOQsPO_POR(id,tender_id){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      Swal.fire({
        title: resp.data,
        showConfirmButton: false,
        timer: 2000
      });
      Details_PORequest();
    }
  }
  data = {"function": 'TenderDetails', "method":"Reject_BOQsPO","Po_id":id};
  apiCall(data,successFn);
}


function ViewBOQsPO_Tab(boq_id, Tender_id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    for (var key in resp.data.Requests) {
        resp.data.Requests[key]["status"]=parseInt(resp.data.Requests[key]["status"]);
        resp.data.Requests[key]["purchase"]=parseInt(resp.data.Requests[key]["purchase"]);
        resp.data.Requests[key]["reject"]=parseInt(resp.data.Requests[key]["reject"]);
        resp.data.Requests[key]["received"]=parseInt(resp.data.Requests[key]["received"]);
        resp.data.Requests[key]["required_by"]= moment(resp.data.Requests[key]["required_by"]).format('DD-MM-YYYY');

    }
    resp.data.Admin=parseInt(resp.data.Admin);
    loadTenderDetailsTemplate('#ListPurchaseOrder_tab_templ',toMustacheDataObj(resp), 'TenderBOQ');
    $("#Dt_MaterialOrders").DataTable({});
    
    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'BOQ', "method":"Select_BOQPurchaseOrder","boq_id":boq_id,"Tender_id":Tender_id};
  apiCall(data,successFn);
}

function RequestedPO_tab(boq_id,Tender_id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
      for (var key in resp.data.Requests) {
        resp.data.Requests[key]["status"]=parseInt(resp.data.Requests[key]["status"]);
        resp.data.Requests[key]["purchase"]=parseInt(resp.data.Requests[key]["purchase"]);
        resp.data.Requests[key]["reject"]=parseInt(resp.data.Requests[key]["reject"]);
        resp.data.Requests[key]["approved"]=parseInt(resp.data.Requests[key]["approved"]);
        resp.data.Requests[key]["received"]=parseInt(resp.data.Requests[key]["received"]);
        resp.data.Requests[key]["required_by"]= moment(resp.data.Requests[key]["required_by"]).format('DD-MM-YYYY');

        if(resp.data.Requests[key]["pdf_location"]==""){
          resp.data.Requests[key]["pdf_Available"]=0;
        }else{
          resp.data.Requests[key]["pdf_Available"]=1;
        }
    }
    resp.data.admin=parseInt(resp.data.admin);
    loadTenderDetailsTemplate('#PO_BOQ_order_tab_templ',toMustacheDataObj(resp), 'TenderBOQ');

    
    $("#Dt_BOQReq").DataTable({});

    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'BOQ', "method":"Select_PurchaseOrder","boq_id":boq_id,"Tender_id":Tender_id};
  apiCall(data,successFn);
}


function TenderBOQDetails(){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);
    resp.data.SiteIncharge=parseInt( resp.data.SiteIncharge);
    for (var key in resp.data.BOQ) {
        resp.data.BOQ[key]["confirm"]=parseInt(resp.data.BOQ[key]["confirm"]);
        resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
    }

    // console.log(resp.data.BOQ);
    loadTenderDetailsTemplate('#BOQ_templ2',toMustacheDataObj(resp), 'TenderBOQ');
    $("#Dt_BOQ1").DataTable({});
    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'TenderDetails', "method":"BOQ_List","id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}

function AdditionalLabourCostDetails(){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    resp.data.TenderTag=parseInt(resp.data.TenderTag);
    resp.data.BillTag=parseInt(resp.data.BillTag);
    resp.data.labourAvailable=parseInt(resp.data.labourAvailable);
    resp.data.Below=parseInt(resp.data.Below);
    resp.data.admin=parseInt(resp.data.admin);
  
    if(resp.data.TenderCosting!=''){
        resp.data.TenderCosting[0]['tender_amount_1']=resp.data.TenderCosting[0]['tender_amount'];
        ProgressBarPrecentage=parseInt(parseInt(resp.data.total_labour_amount)/parseInt(resp.data.TenderCosting[0]['labour_amount'])*100);


        TenderAmount=parseFloat(resp.data.TenderCosting[0]['tender_amount']);
        TenderPrecentage=parseFloat(resp.data.TenderCosting[0]['percentage']);

        PercentageAmount=parseFloat(TenderAmount*TenderPrecentage/100);
        if(resp.data.Below==0){
          QuotedTenderAmount=(TenderAmount+PercentageAmount);
        }else if(resp.data.Below==1){
          QuotedTenderAmount=(TenderAmount-PercentageAmount);
        }
        resp.data.TenderCosting[0]['QuotedTenderAmount']=QuotedTenderAmount.toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        resp.data.TenderCosting[0]['tender_amount'] = TenderAmount.toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        resp.data.TenderCosting[0]['labour_amount'] = parseFloat(resp.data.TenderCosting[0]['labour_amount']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        resp.data.total_labour_amount = parseFloat(resp.data.total_labour_amount).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        resp.data.balance_labour_amount = parseFloat(resp.data.balance_labour_amount).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
    }
    for (var key in resp.data.Bills) {
      resp.data.Bills[key]["bill_amount"]=parseInt(resp.data.Bills[key]["bill_amount"]).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });

      resp.data.Bills[key]["bill_date"]= moment(resp.data.Bills[key]["bill_date"]).format('DD-MM-YYYY');

    }
    loadTenderDetailsTemplate('#AddLabourCost_templ2',toMustacheDataObj(resp), 'AdditionalLabourCost');
    $('[data-toggle="tooltip"]').tooltip();
    $("#Dt_labourBills").DataTable();

    $('#ProgressBar_LabourAmount').attr('data-percentage', ProgressBarPrecentage);
    $('.progress-bar').each(function () {
          var t = $(this);
          var barPercentage = t.data('percentage');
          t.children('.label').append('<div class="label-text"></div>');
          if (parseInt((t.data('percentage')), 10) < 2) barPercentage = 2;
          if (barPercentage > 50) {
              t.children('.label').css("right", (100 - barPercentage) + '%');
              t.children('.label').css("margin-right", "-10px");
          }
          if (barPercentage < 51) {
              t.children('.label').css("left", barPercentage + '%');
              t.children('.label').css("margin-left", "-20px");
          }
          t.find('.label-text').text(t.attr('data-percentage') + ' Remaining');
          t.children('.bar').animate({
              width: barPercentage + '%'
          }, 500);
          t.children('.label').animate({
              opacity: 1
          }, 1000);
      });
    


  }
  data = {"function": 'Tender', "method":"AddLabourCosts","Tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);

}



function challanBills(){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);
    for (var key in resp.data.ChallanDetails) {
      resp.data.ChallanDetails[key]["challan_amount"]=parseInt(resp.data.ChallanDetails[key]["challan_amount"]).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
      });
      resp.data.ChallanDetails[key]["challan_date"]= moment(resp.data.ChallanDetails[key]["challan_date"]).format('DD-MM-YYYY');

    }
    loadTenderDetailsTemplate('#challanBills_tmpl',toMustacheDataObj(resp), 'challanBills');
    $('.js-example-basic-single').select2();
    $('.select2-container--default').css('width','-webkit-fill-available');
    
    // $("#Dt_TenderChallan").DataTable();
    $("#Dt_TenderChallan").DataTable({ columns: [
      null, null, null,    
      { "render": function(data, type, row){ return data.split('nl2br').join("<br/>"); } },
      null, null
    ] });
    
    $('[data-toggle="tooltip"]').tooltip();

      var start = moment().subtract(30, 'days');
      var end = moment();
      // function cb(start, end) {
      //     $('#challan_date_range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      // }
      function cb(start, end) {
        if(start._isValid && end._isValid)
        {
            $('#challan_date_range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        else
        {
            $('#challan_date_range span').html('');
        }
      }


      $('#challan_date_range').daterangepicker({
          startDate: start,
          endDate: end,
          ranges: {
             'Clear': ['',''],
             'Today': [moment(), moment()],
             'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
             'Last 7 Days': [moment().subtract(6, 'days'), moment()],
             'Last 30 Days': [moment().subtract(29, 'days'), moment()],
             'This Month': [moment().startOf('month'), moment().endOf('month')],
             'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],

          }
      }, cb);
      cb(start, end);
        if($('#challan_date_range span').html()==''){
            $('#challan_date_range').css("display","block");
            $('#labelChellan').css("margin-bottom","7px");
        }else{
            $('#challan_date_range').css("display","inline");
            $('#labelChellan').css("margin-bottom","15px");
        }

      $('#challan_date_range').on('apply.daterangepicker', function(ev, picker) {
        FilterChellanDateRange_Vendor();
        if($('#challan_date_range span').html()==''){
            $('#challan_date_range').css("display","block");
            $('#challan_date_range').css("width","75%");
            $('#labelChellan').css("margin-bottom","7px");
        }else{
            $('#challan_date_range').css("display","inline");
            $('#labelChellan').css("margin-bottom","15px");

        }
        // alert(picker.startDate.format('YYYY-MM-DD'));
        // alert(picker.endDate.format('YYYY-MM-DD'));
      });


  }
  data = {"function": 'TenderDetails', "method":"challanBills","tender_id":$('#tender_id_details').val(),"vendor_id":0};
  apiCall(data,successFn);
}

function FilterChellanDateRange_Vendor(){
  vendor_id=''+$('#Challan_vendor').val();
  startDate=($('#challan_date_range').data('daterangepicker').startDate).format('YYYY-MM-DD');
  endDate=($('#challan_date_range').data('daterangepicker').endDate).format('YYYY-MM-DD');
       
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){

      for (var key in resp.data.ChallanDetails) {
        resp.data.ChallanDetails[key]["challan_date"]= moment(resp.data.ChallanDetails[key]["challan_date"]).format('DD-MM-YYYY');
      }
      loadTenderDetailsTemplate('#sub_TableForSelectedVendor',toMustacheDataObj(resp), 'TableForSelectedVendor');
      // $("#Dt_SubTenderChallan").DataTable();
      $('#btn_createBill').addClass('d-none');
      $("#Dt_SubTenderChallan").DataTable({ columns: [
        null, null, null,    
        { "render": function(data, type, row){ return data.split('nl2br').join("<br/>"); } },
        null, null
      ] });
    }

  }
  data = {"function": 'TenderDetails', "method":"SelectChellanOfVendor", "vendor_id":vendor_id,"tender_id":$('#tender_id_details').val(), "startDate":startDate,"endDate":endDate};
  apiCall(data,successFn);
}

function SelectChellanForVendor(vendor_id){
  FilterChellanDateRange_Vendor();
  // successFn = function(resp)  {
  //   if(resp.status==0){
  //     Swal.fire({
  //       icon: 'error',
  //       title: 'Oops...',
  //       text: resp.data,
  //     });
  //   }else if(resp.status==1){

  //     for (var key in resp.data.ChallanDetails) {
  //       resp.data.ChallanDetails[key]["challan_date"]= moment(resp.data.ChallanDetails[key]["challan_date"]).format('DD-MM-YYYY');
  //     }
  //     loadTenderDetailsTemplate('#sub_TableForSelectedVendor',toMustacheDataObj(resp), 'TableForSelectedVendor');
  //     $("#Dt_SubTenderChallan").DataTable();
  //     $('#btn_createBill').addClass('d-none');
  //   }

  // }
  // data = {"function": 'TenderDetails', "method":"SelectChellanOfVendor", "vendor_id":vendor_id,"tender_id":$('#tender_id_details').val()};
  // apiCall(data,successFn);
}

function addNewChallanForm(){
  successFn = function(resp)  {
    loadTenderDetailsTemplate('#addNewTenderChallan_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

    $('#TenderChallanForm').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#TenderChallanForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'AddNewChallan');
        data.append('tender_id', $('#tender_id_details').val());

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
         
            $('#BaseModal').modal('hide');
            challanBills();
          }
        }
       
        apiCallForm(data,successFn);
      },


      rules: {
       Challan_vendor:{
          required: true,
        },
        challan_number:{
          required: true,
        },
        challan_date:{
          required: true,
        },
        // challan_amount:{
        //   required: true,
        // },
        challan_description:{
          required: true,
        },
        challan_description:{
          required: true,
        },
        UploadChallan:{
          required: true,
        }
      },

      messages: {
        Challan_vendor: {
          required: "Please choose a vendor.",
        },
        challan_number: {
          required: "Please enter the challan number."
        },
        challan_date: {
          required: "Please choose a date."
        },
        challan_amount: {
          required: "Please enter the challan amount."
        },
        challan_description: {
          required: "Please enter the description."
        },
        UploadChallan:{
          required: "Please attach the challan.",
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


    $('.image-preview').popover('hide');
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
        }, 
         function () {
           $('.image-preview').popover('hide');
        }
    );    


    


  }
  data = {"function": 'TenderDetails', "method":"VendorCompanyList"};
  apiCall(data,successFn);
}




function Delete_chellan(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {

        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else{
          challanBills();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
          
      }
      data = {"function": 'TenderDetails', "method": "DeleteChellan","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}

function CountSelected(){
    var ids = [];
    $.each($("input[name=CB_Chellan]:checked"), function(){
      ids.push($(this).val());
    });

    if(ids==""){
      $('#btn_createBill').addClass('d-none');
    }else{
      $('#btn_createBill').removeClass('d-none');
    }

}




function CreateBills(){
  var ids = [];
  $.each($("input[name=CB_Chellan]:checked"), function(){
    ids.push($(this).val());
  });

  successFn = function(resp)  { 
    if(resp.status==1){
      loadTenderDetailsTemplate('#CreateBillsForVendor_tmpl',toMustacheDataObj(resp), 'BaseModal');
      $('#BaseModal').modal('show');
      setTimeout(function () {
        $('#vendor_id').val(resp.data.vendor_id);
        $('#challan_ids').val(resp.data.Challan_ids);
        $('#challan_bill_number').val(resp.data.BillNo);
        $('#DisplayBillNumber').html(resp.data.BillNo);
        $('#challan_billAmount').val(resp.data.TotalAmount);

      }, 1000);

        $('#BillsForm').validate({
         ignore: [], //disable form auto submit on button click
        submitHandler: function(form) {
          $('#SubmitButton').addClass('d-none');
          $('#LoadingBtn').removeClass('d-none');
          var form = $("#BillsForm");
          var data = new FormData(form[0]);
          data.append('function', 'TenderDetails');
          data.append('method', 'AddNewBills');
          data.append('tender_id', $('#tender_id_details').val());
          data.append('vendor_id', $('#vendor_id').val());
          data.append('challan_ids', $('#challan_ids').val());


          successFn = function(resp)  {
            if(resp.status==0){
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: resp.data,
              });
            }else if(resp.status==1){
              SwalRoundTick('Record Updated Successfully');
              $('#BaseModal').modal('hide');
              challanBills(id);
            }
          }
         
          apiCallForm(data,successFn);
        },


        rules: {
          Challan_BillType:{
            required: true,
          },
          challan_bill_number:{
            required: true,
          },
          challan_billdate:{
            required: true,
          },
          challan_billAmount:{
            required: true,
          },
          challan_billdescription:{
            required: true,
          },
          UploadBills:{
            required: true,
          }
        },

        messages: {
          Challan_BillType: {
            required: "Please choose the bill type.",
          },
          challan_bill_number: {
            required: "Please enter the bill number."
          },
          challan_billdate: {
            required: "Please choose a date."
          },
          challan_billAmount: {
            required: "Please enter the bill amount."
          },
          challan_billdescription: {
            required: "Please enter the description."
          },
          UploadBills:{
            required: "Please attach the Bill",
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

     }else{
      // alert(resp.data)
      Swal.fire({
        icon: 'error',
        title: resp.data
      });

     }

  }
  data = {"function": 'TenderDetails', "method":"SelectChellanForBills", "ids":ids};
  apiCall(data,successFn);
}


function CreatedBills(){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      for (var key in resp.data.CreatedBills) {
        resp.data.CreatedBills[key]["bill_amount"]=parseInt(resp.data.CreatedBills[key]["bill_amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
        resp.data.CreatedBills[key]["bill_date"]= moment(resp.data.CreatedBills[key]["bill_date"]).format('DD-MM-YYYY');

      }
      loadTenderDetailsTemplate('#CreatedBills_tmpl',toMustacheDataObj(resp), 'CreatedBills');
      $("#Dt_TenderChallanBills").DataTable({});

    }
         
  }
  data = {"function": 'TenderDetails', "method":"SelectCreatedBills", "tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}


function viewChallan(challan_ids){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      for (var key in resp.data.ChallanDetails) {
        resp.data.ChallanDetails[key]["challan_amount"]=parseInt(resp.data.ChallanDetails[key]["challan_amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
      }

      loadTenderDetailsTemplate('#CreatedBillsChallan',toMustacheDataObj(resp), 'BaseModal');
      $('#BaseModal').modal('show');
    }
  }
  data = {"function": 'TenderDetails', "method":"viewBillsChellans", "challan_ids":challan_ids};
  apiCall(data,successFn);
}



function viewImageDiscription(src, description){
  $('#imagepreview').attr('src', src); // here asign the image to the modal when the user click the enlarge link
  $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function

  $('#DownloadImage').click(function(e) {
    a = document.createElement('a');
    a.href = src;
    a.download = src;
    a.click();
  });

  setTimeout(function () {
    $('#ForDescription').removeClass('d-none');
    $('#challan_description').html(description);
  }, 100)

}

 

function Delete_chellanBills(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        if(resp.status==1){

          Swal.fire(
            'Deleted!',
            'Your file has been deleted.',
            'success'
          );
          setTimeout(function () {
            Swal.close();
          }, 1000);
          CreatedBills();

        }else{
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'TenderDetails', "method": "DeleteChellanBills","id":id };
      apiCall(data,successFn);
      
    }
  });

}

function DailyProgress(){

  successFn = function(resp)  {
     if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
       for (var key in resp.data.DailyProgress) {
        resp.data.DailyProgress[key]["date"]= moment(resp.data.DailyProgress[key]["date"]).format('DD-MM-YYYY');

      }
      resp.data.admin=parseInt(resp.data.admin);
      loadTenderDetailsTemplate('#DailyProgress_tmpl',toMustacheDataObj(resp), 'DailyProgress');
      $('[data-toggle="tooltip"]').tooltip();

      $("#Dt_DailyProgress").DataTable({});
    }
  }
  data = {"function": 'TenderDetails', "method":"GetDailyProgress","tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);

}



function addNewProgress(){

  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  loadTenderDetailsTemplate('#addNewTenderProgress_templ',{}, 'BaseModal');
  $('#BaseModal').modal('show');
  $('[data-toggle="tooltip"]').tooltip();
  $('#tender_id').val(id);
  today = new Date().toISOString().slice(0, 10);
  setTimeout(function () {
    $('#progress_date').val(today);
  }, 1000);

  $('#TenderProgressForm').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#TenderProgressForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'AddNewProgress');
        data.append('tender_id', $('#tender_id_details').val());
        // data.append('save', save);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
            $('#BaseModal').modal('hide');
            DailyProgress();
          }

        }
       
        apiCallForm(data,successFn);
      },


      rules: {
        "progress_date":{
          required: true,
        },
        "progress_description":{
          required: true,
        },
        "UploadProgress[]":{
          required: true,
        }
      },

      messages: {
        progress_date:{
          required: "Please choose a date.",
        },
        progress_description:{
          required: "Please enter the description. ",
        },
        UploadProgress:{
          required: "Please attach a file",
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
    


}


function Delete_Progress(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {

      successFn = function(resp)  {
        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          setTimeout(function () {
            Swal.close();
          }, 1000);
          DailyProgress();
        }

      }
      data = {"function": 'TenderDetails', "method": "DeleteProgress","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}



function ViewProgressImage(id){
  successFn = function(resp)  {
    for (var key in resp.data.DailyProgressImage) {
      imgext= resp.data.DailyProgressImage[key]["image"].split('.');
      if(imgext[1]=='pdf'){
        resp.data.DailyProgressImage[key]["PDF"]=1;
      }else{
        resp.data.DailyProgressImage[key]["PDF"]=0;
      }

    }



    loadTenderDetailsTemplate('#ViewProgressImages_tmpl',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

  }
  data = {"function": 'TenderDetails', "method":"SelectProgressImages", "progress_id":id};
  apiCall(data,successFn);
}
// function ViewProgressImage(id, progress_id){
//   $(".popover").remove();  
//   successFn = function(resp)  {
     
//     var template = $(TenderDetails).filter('#popover_viewProgress').html();
//     var rendered = Mustache.render(template,  toMustacheDataObj(resp) );

//     $("#"+id).popover("dispose").popover({
//         title: "View Progress Image <i class='fas fa-window-close text-muted float-right' onclick='ClosePopover()' title='Close'</i>",
//         content: rendered,
//         html: true,
//         sanitize: false
//     }); 
//     $('.myPopoverClass').popover({
//         trigger: 'manual', /* <- important, instantiates popover */
//         container: 'body', /* optional */
//         animation: false
//     }).click(function(e) {
//         $("#ProductBatch_"+id).not(this).hide(); /* optional, hide other popovers */
//         $(this).popover('show'); /* show popover now it's setup */
//         e.preventDefault();
//     });
//     $("#"+id).popover("show");
//     $('body').on('click', function (e) {
//       $('[data-toggle=popover]').each(function () {
//         if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
//           $(this).popover('hide');
//         }
//       });
//     });
//   }
//   data = {"function": 'TenderDetails', "method":"SelectProgressImages", "progress_id":progress_id};
//   apiCall(data,successFn);
// }

function Popover_ProgressImageView( id,  src){
  $(".popover").remove();  
     
  var template = $(TenderDetails).filter('#popover_viewImage').html();
  var rendered = Mustache.render(template, {src});

  $("#"+id).popover("dispose").popover({
      title: "Progress Image <i class='fas fa-window-close text-muted float-right' onclick='ClosePopover()' title='Close'</i>",
      content: rendered,
      html: true,
      sanitize: false
  }); 
  $('.myPopoverClass').popover({
      trigger: 'manual', /* <- important, instantiates popover */
      container: 'body', /* optional */
      animation: false
  }).click(function(e) {
      $("#ProductBatch_"+id).not(this).hide(); /* optional, hide other popovers */
      $(this).popover('show'); /* show popover now it's setup */
      e.preventDefault();
  });
  $("#"+id).popover("show");
  $('body').on('click', function (e) {
    $('[data-toggle=popover]').each(function () {
      if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
        $(this).popover('hide');
      }
    });
  });
  checkheight(id);
}

function checkheight(id){
  Wheight=$(window).height();
  imgheight=$("#loadImagePop").height();
  if(Wheight<imgheight  ){
    $("#imagePreview_pop").width('50%');
    window.dispatchEvent(new Event('resize'));
  }
  // alert('width : '+ $(window).width()+' height ' + $(window).height());
  // alert('width : '+ $("#loadImagePop").width()+' height ' + $("#loadImagePop").height());
}




function Popover_ProgressPDFView( id,  src){
  $(".popover").remove();  
     
  var template = $(TenderDetails).filter('#popover_viewPDF').html();
  var rendered = Mustache.render(template, {src});

  $("#"+id).popover("dispose").popover({
      title: "Progress PDF <i class='fas fa-window-close text-muted float-right' onclick='ClosePopover()' title='Close'</i>",
      content: rendered,
      html: true,
      sanitize: false
  }); 
  $('.myPopoverClass').popover({
      trigger: 'manual', /* <- important, instantiates popover */
      container: 'body', /* optional */
      animation: false
  }).click(function(e) {
      $("#ProductBatch_"+id).not(this).hide(); /* optional, hide other popovers */
      $(this).popover('show'); /* show popover now it's setup */
      e.preventDefault();
  });
  $("#"+id).popover("show").css("max-width", '80%');;
  // $("#"+id).popover("show");

  $('body').on('click', function (e) {
    $('[data-toggle=popover]').each(function () {
      if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
        $(this).popover('hide');
      }
    });
  });
  // checkheight(id);
}




function Delete_ProgressImage(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          $("#Block_"+id).remove();
          
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'TenderDetails', "method": "DeleteProgressimage","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}


//Work here

function rotateImage_popover(angle,id,src){
  degree=parseInt(angle);

  $('#hid_src_'+id).animate({
      transform: degree*(-1)
  }, {
      step: function(now, fx) {
          $(this).css({
              '-webkit-transform': 'rotate(' + now + 'deg)',
              '-moz-transform': 'rotate(' + now + 'deg)',
              'transform': 'rotate(' + now + 'deg)'
          });
      }
  });
  // $("#rotateAngle2").val(degree);
  if(src!=''){
    successFn = function(resp)  {
      if(resp.status==1){

        // $('#Block_'+id).empty().append('<img src="'+resp.data+"?t=" + new Date().getTime()+'" id="pop_image_'+id+'" style="width: 95%; height:95%" >');
        $('#Block_'+id).empty().append('<a class=" hover-overlay ripple  align-bottom" href="javascript:void(0);" onclick="Popover_ProgressImageView(`ProgressImage_'+id+'`,`'+src+"?t=" + new Date().getTime()+'`)" id="ProgressImage_'+id+'"  data-toggle="popover" data-placement="right"  style="text-decoration:none;" ><img alt="progress" class="img-fluid rounded" src="'+resp.data+"?t=" + new Date().getTime()+'" id="pop_image_'+id+'" /></a>');
      }
    }
    data = {"function": 'TenderDetails', "method":"RotateImagePOPover","degree":degree,"src": src};
    apiCall(data,successFn);
  }

}

function Expenses(){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
     if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      for (var key in resp.data.Expenses) {
        resp.data.Expenses[key]["closed"]=parseInt(resp.data.Expenses[key]["closed"]);

        resp.data.Expenses[key]["amount_disp"]=parseInt(resp.data.Expenses[key]["amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
        resp.data.Expenses[key]["report_date"]= moment(resp.data.Expenses[key]["report_date"]).format('DD-MM-YYYY');

      }
      resp.data.adminSiteincharge=parseInt(resp.data.adminSiteincharge);

      loadTenderDetailsTemplate('#Expenses_tmpl',toMustacheDataObj(resp), 'Expenses');
      // $("#Dt_TenderExpense1").DataTable();

      if(resp.data.adminSiteincharge==1){
        $("#Dt_TenderExpense1").DataTable({ columns: [
            null, null,null,    
            { "render": function(data, type, row){ return data.split(',').join("<br/>"); } },
            null, null,null
        ] });
      }else{
        $("#Dt_TenderExpense1").DataTable({ columns: [
            null, null, 
            { "render": function(data, type, row){ return data.split(',').join("<br/>"); } },
            null, null
        ] });
      }

      

      // $("#Dt_TenderExpense1").DataTable({ columns: [
      //     null, null,    
      //     { "render": function(data, type, row){ return data.split(',').join("<br/>"); } },
      //     null, null
      // ] });
      $('[data-toggle="tooltip"]').tooltip();


      var start = moment().subtract(30, 'days');
      var end = moment();
      function cb(start, end) {
          $('#expense_date_range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      }
      $('#expense_date_range').daterangepicker({
          startDate: start,
          endDate: end,
          ranges: {
             'Clear': ['01/01/2022',moment()],
             'Today': [moment(), moment()],
             'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
             'Last 7 Days': [moment().subtract(6, 'days'), moment()],
             'Last 30 Days': [moment().subtract(29, 'days'), moment()],
             'This Month': [moment().startOf('month'), moment().endOf('month')],
             'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          }
      }, cb);
      cb(start, end);

      $('#expense_date_range').on('apply.daterangepicker', function(ev, picker) {
        FilterExpenseTypeRange();
        // alert(picker.startDate.format('YYYY-MM-DD'));
        // alert(picker.endDate.format('YYYY-MM-DD'));
      });
      // setTimeout(function () {
        // $("#Dt_TenderExpense1").DataTable({ columns: [
        //     null, null,    
        //     { "render": function(data, type, row){ return data.split(',').join("<br/>"); } },
        //     null, null
        // ] });
      // }, 1000);


      



    }
  }
  data = {"function": 'TenderDetails', "method":"FetchExpenses","tender_id":$('#tender_id_details').val(),"xxxx":0};
  apiCall(data,successFn);
}

function Edit_Expense(id, expense_type_id,amount,report_date,summary,site_supervisor_id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  addNewExpenses('update',id);
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      setTimeout(function () {
        $('#expense_date').val(report_date);
        $('#expense_type').val(expense_type_id);
        $('#expense_amount').val(amount);
        $('#expense_summary').val(summary);
        $('#expense_SiteSupervisor').val(site_supervisor_id);
      }, 500);

      url=resp.data.url;
      if(resp.data.Expenses['image']!=""){
         fileName=resp.data.Expenses['image'];
         CheckUploadedFile_('UploadExpenses',fileName, url+fileName);
      }
    }

  }
  data = {"function": 'TenderDetails', "method":"getExpense_Id", "id":id};
  apiCall(data,successFn);

}

 

function addNewExpenses(save='add',id=""){
  successFn = function(resp)  {
    resp.data.adminSiteincharge=parseInt(resp.data.adminSiteincharge);

    loadTenderDetailsTemplate('#addNewSiteExpenses',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

    $('#SiteExpensesForm').validate({
      ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#SiteExpensesForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'AddNewExpenses');
        data.append('save', save);
        data.append('id', id);
        data.append('tender_id', $('#tender_id_details').val());

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
          }
         
          $('#BaseModal').modal('hide');
          Expenses();

        }
       
        apiCallForm(data,successFn);
      },


      rules: {
       expense_date:{
          required: true,
        },
        expense_type:{
          required: true,
        },
        expense_amount:{
          required: true,
        },
        expense_summary:{
          required: true,
        },
        UploadExpenses:{
          required: true,
        },
        expense_site_person:{
          required: function() {
           return ( $("#expense_site_person").is(':visible'));
          }
        }
      },

      messages: {
        expense_date: {
          required: "Please select a date.",
        },
        expense_type: {
          required: "Please enter the expense type"
        },
        expense_amount: {
          required: "Please enter the amount"
        },
        expense_summary: {
          required: "Please enter the expense summary"
        },
        UploadExpenses:{
          required: "Please attach the file",
        },
        expense_site_person:{
          required: "Please select the site person",
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


  }
  data = {"function": 'TenderDetails', "method":"getExpenseType", "tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}




function Delete_Expense(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          Expenses();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'TenderDetails', "method": "DeleteExpense","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}


function FilterExpenseTypeRange(){
  expense_type=$('#expense_type').val();
  // expense_date_range=$('#expense_date_range').val();
  startDate=($('#expense_date_range').data('daterangepicker').startDate).format('YYYY-MM-DD');
  endDate=($('#expense_date_range').data('daterangepicker').endDate).format('YYYY-MM-DD');
       
  // alert(picker.endDate.format('YYYY-MM-DD'));
 
 // alert(startDate +' '+  endDate);

  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      for (var key in resp.data.Expenses) {
        resp.data.Expenses[key]["amount_disp"]=parseInt(resp.data.Expenses[key]["amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
        resp.data.Expenses[key]["report_date"]= moment(resp.data.Expenses[key]["report_date"]).format('DD-MM-YYYY');

      }
    }
    loadTenderDetailsTemplate('#subExpenses_tmpl',toMustacheDataObj(resp), 'TableForSelectedRange');
    // $("#Dt_TenderExpense2").DataTable();
    $('[data-toggle="tooltip"]').tooltip();
    $('#btn_createVoucher').addClass('d-none');

    if(resp.data.adminSiteincharge==1){
      $("#Dt_TenderExpense2").DataTable({ columns: [
          null, null,null,    
          { "render": function(data, type, row){ return data.split('nl2br,').join("<br/>"); } },
          null, null,null
      ] });
    }else{
      $("#Dt_TenderExpense2").DataTable({ columns: [
          null, null, 
          { "render": function(data, type, row){ return data.split('nl2br,').join("<br/>"); } },
          null, null
      ] });
    }

  }
  data = {"function": 'TenderDetails', "method":"FilterExpenses","tender_id":$('#tender_id_details').val(), "SitePerson":$('#expense_SitePerson').val(), "expense_type":expense_type, "startDate":startDate,"endDate":endDate};
  apiCall(data,successFn);


}

function CountSelectedExpense(){

   var ids = [];
    $.each($("input[name=CB_Expense]:checked"), function(){
      ids.push($(this).val());
    });

    if(ids==""){
      $('#btn_createVoucher').addClass('d-none');
    }else{
      $('#btn_createVoucher').removeClass('d-none');
    }


}
 


function CreateVoucher(){
  var ids = [];
  $.each($("input[name=CB_Expense]:checked"), function(){
    ids.push($(this).val());
  });

  successFn = function(resp)  { 
    if(resp.status==1){
      loadTenderDetailsTemplate('#closeVouchers_tmpl',{}, 'BaseModal');
      $('#BaseModal').modal('show');
      today = new Date().toISOString().slice(0, 10);

      setTimeout(function () {
        $('#voucher_ids').val(resp.data.voucher_ids);
        $('#voucher_bill_number').val(resp.data.BillNo);
        $('#DisplayVoucherBillNumber').html(resp.data.BillNo);
        $('#voucher_billAmount').val(resp.data.TotalAmount);
        $('#voucher_TenderName').html(resp.data.TenderName);
        $('#voucher_billdate').val(today);
      }, 1000);

        $('#VoucherBillsForm').validate({
         ignore: [], //disable form auto submit on button click
        submitHandler: function(form) {
          $('#SubmitButton').addClass('d-none');
          $('#LoadingBtn').removeClass('d-none');
          var form = $("#VoucherBillsForm");
          var data = new FormData(form[0]);
          data.append('function', 'TenderDetails');
          data.append('method', 'AddNewVoucherBills');
          data.append('tender_id', $('#tender_id_details').val());
          // alert($('#tender_id_details').val());
          // return false;

          successFn = function(resp)  {
            if(resp.status==0){
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: resp.data,
              });
            }else if(resp.status==1){
              SwalRoundTick('Record Updated Successfully');
              $('#BaseModal').modal('hide');
              Expenses();
            }

          }
         
          apiCallForm(data,successFn);
        },

        rules: {
          voucher_billdate:{
            required: true,
          },
          voucher_billAmount:{
            required: true,
          },
          voucher_billdescription:{
            required: true,
          },
          // UploadVoucher_Bills:{
          //   required: true,
          // }
        },

        messages: {
          voucher_billdate: {
            required: "Please choose a date."
          },
          voucher_billAmount: {
            required: "Please enter the amount."
          },
          voucher_billdescription: {
            required: "Please enter the description."
          },
          UploadVoucher_Bills:{
            required: "Please attach the Bill",
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

     }else{
      // alert(resp.data)
      Swal.fire({
        icon: 'error',
        title: resp.data
      });

     }

  }
  data = {"function": 'TenderDetails', "method":"SelectVoucherForBills", "ids":ids,'tender_id': $('#tender_id_details').val()};
  apiCall(data,successFn);
}


function Voucher(){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);

      for (var key in resp.data.CreatedVoucher) {
        resp.data.CreatedVoucher[key]["expenses_amount"]=parseInt(resp.data.CreatedVoucher[key]["expenses_amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
        resp.data.CreatedVoucher[key]["expenses_date"]= moment(resp.data.CreatedVoucher[key]["expenses_date"]).format('DD-MM-YYYY');

      }

      loadTenderDetailsTemplate('#CreatedVouchers_tmpl',toMustacheDataObj(resp), 'Voucher');
      
      $("#Dt_CreatedVouchers").DataTable();
    }

  }
  data = {"function": 'TenderDetails', "method":"SelectCreatedVouchers", "tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}



function Delete_Voucher(id){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          Voucher();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'TenderDetails', "method": "DeleteVoucher","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}


function viewExpencesIds(voucher_ids,id){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      for (var key in resp.data.Expenses) {
        resp.data.Expenses[key]["amount"]=parseInt(resp.data.Expenses[key]["amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
        
      resp.data.Expenses[key]["report_date"]= moment(resp.data.Expenses[key]["report_date"]).format('DD-MM-YYYY');

      }
      resp.data.TotalAmount=parseInt(resp.data.TotalAmount).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
        });
      resp.data.VoucherDate= moment(resp.data.VoucherDate).format('DD-MM-YYYY');


      loadTenderDetailsTemplate('#CreatedVoucherBills',toMustacheDataObj(resp), 'BaseModal');
      $('#BaseModal').modal('show');
    }
  }
  data = {"function": 'TenderDetails', "method":"viewVouchers_Id", "voucher_ids":voucher_ids, 'tender_id': $('#tender_id_details').val(),"id":id};
  apiCall(data,successFn);
}


function addNewBOQItem(){

  successFn = function(resp)  { 
    loadTenderDetailsTemplate('#addNewTenderBOQItem_templ',toMustacheDataObj(resp), 'BaseModal');
 

    $('#BaseModal').modal('show');

    $('#TenderBOQItemForm').validate({
      ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#TenderBOQItemForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'addBOQItem');
        data.append('boq_unit_name', $('#boq_unit option:selected').text());
        data.append('tender_id', $('#tender_id_details').val());

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Item added Successfully');
            $('#BaseModal').modal('hide');
            TenderBOQDetails();
          }
        }
       
        apiCallForm(data,successFn);
      },
      rules: {
        boq_item:{
          required: true,
        },
        boq_description:{
          required: true,
        },
        boq_total_qty:{
          required: true,
        },
        boq_unit:{
          required: true,
        },
        boq_rate:{
          required: true,
        }
      },

      messages: {
        boq_item: {
          required: "Please enter the item.",
        },
        boq_description: {
          required: "Please enter the description."
        },
        boq_total_qty: {
          required: "Please enter the total quantity."
        },
        boq_unit: {
          required: "Please enter the unit."
        },
        boq_rate: {
          required: "Please enter the rate."
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
  }
  data = {"function": 'TenderDetails', "method":"getUnits"};
  apiCall(data,successFn);
}


function Delete_BOQ_TenderDetails(id, tender_id){

   Swal.fire({
    title: 'Are you sure to Delete ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Delete'
  }).then((result) => {
    if (result.isConfirmed) {
        setTimeout(function () {

          successFn = function(resp)  {
            TenderBOQDetails();
          }
          data = {"function": 'BOQ', "method":"DeleteBOQ_ID","id":id};
          apiCall(data,successFn);


          Swal.close();
        }, 1000)

      Swal.fire(
        'Deleted!',
        'Record deleted Successfully',
        'success'
      );
      
    }
  });
}


function addNewBOQItemExcel(){

    loadTenderDetailsTemplate('#addTenderBOQExcel_templ',{}, 'BaseModal');
    $('#BaseModal').modal('show');

    $('#TenderUploadBOQExcelForm').validate({
      ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#TenderUploadBOQExcelForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'addBOQExcel');
        data.append('tender_id', $('#tender_id_details').val());

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Item added Successfully');
            $('#BaseModal').modal('hide');
            TenderBOQDetails();
          }
        }
       
        apiCallForm(data,successFn);
      },
      rules: {
        UploadBOQ:{
          required: true,
        },
      },

      messages: {
        UploadBOQ:{
          required: "Please choose a file.",
        },

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

}



function PlaceRequestPOTenderDetails(boq_id, TenderId){
    $('[data-toggle="tooltip"]').tooltip('dispose'); 

  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);

    loadBOQTemplate('#BOQ_order_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();
    setDate('tomorrow','Required_by');


    $('#OrderBOQForm').validate({
      submitHandler: function(form) {

        var MaterialsOrdered = new Array();
        var AdditionalMaterialsOrdered = new Array();
        var totalRows=  $("#HidRowCount").val();
        var Required_by=  $("#Required_by").val();
        var remaining_qty=  parseInt($("#remaining_qty").val());
        var TotalOrderedMO=  0;
        var TotalOrderedAMO=  0;
        var j=0;
        for (i =1; i <= totalRows; i++) {
          j++;
          if($("#material_type_"+i).length == 0 || $("#material_type_"+i).val()=="") {
              j--;
              continue;
          }
          MaterialsOrdered[j] ={'material_type':$("#material_type_"+i).val(), 'sub_type':$("#material_sub_type_"+i).val(), 'Quantity':$("#Quantity_"+i).val(), 'unit_id':$("#unit_type_"+i).val(), 'unit_name':$("#unit_type_"+i+" option:selected").text()};
          TotalOrderedMO = TotalOrderedMO+parseInt($("#Quantity_"+i).val());

        }

        // if(MaterialsOrdered?.length == 0) {
        //   Swal.fire("Please add one Material Type.");
        //   return false;
        // } 

        var additional_totalRows=  $("#additional_HidRowCount").val();

        var k=0;
        for (l =1; l <= additional_totalRows; l++) {
          k++;
          if($("#additional_material_type_"+l).length == 0 || $("#additional_material_type_"+l).val()=="") {
              k--;
              continue;
          }

          // alert(k);

          AdditionalMaterialsOrdered[k] ={'additional_material_type':$("#additional_material_type_"+l).val(), 'additional_sub_type':$("#additional_material_sub_type_"+l).val(), 'additional_Quantity':$("#additional_Quantity_"+l).val(), 'additional_unit_id':$("#additional_unit_type_"+l).val(), 'additional_unit_name':$("#additional_unit_type_"+l+" option:selected").text()};
          TotalOrderedAMO= TotalOrderedAMO + parseInt($("#additional_Quantity_"+l).val());
          // alert(AdditionalMaterialsOrdered[k]['additional_material_type']);
        }


        if(AdditionalMaterialsOrdered.length == 0 &&  MaterialsOrdered.length == 0) {
          Swal.fire("Please add one Material Type.");
          return false;
        } 

        TotalOrdered=TotalOrderedMO+TotalOrderedAMO;
        if(TotalOrdered>remaining_qty){
          Swal.fire("Quantity ordered is more than Remaining Quantity");
          return false;
        }
        
        $('#LoadingBtn').removeClass('d-none');
        $('#SubmitButton').addClass('d-none');

        successFn = function(resp)  {
          $('#BaseModal').modal('hide');
          // alert(resp.data.boq_id);
          // alert(resp.data.tender_id);
          RequestedPO_tab(resp.data.boq_id,resp.data.tender_id);
          $('#LoadingBtn').addClass('d-none');
          $('#SubmitButton').removeClass('d-none');
           
        }
        data = {"function": 'BOQ', "method": 'SavePORequest',"MaterialsOrdered":MaterialsOrdered,"AdditionalMaterialsOrdered":AdditionalMaterialsOrdered,"Required_by":Required_by,
        'boq_id':$("#boq_id").val(), 'tender_id':$("#tender_id").val(), 'request_note':$("#request_note").val()};
        apiCall(data,successFn);


      },

      rules: {
        material_type_1:{
          required: function() {
           return (  $("#Quantity_1").val()!="");
          }
        },
        Quantity_1:{
          required: function() {
           return ( $("#material_type_1").val()!="" );
          }
        },
        unit_type_1:{
          required: function() {
           return ( $("#material_type_1").val()!="" );
          }
        },
        Required_by:{
          required: true,
        },
        additional_material_type_1:{
          required: function() {
           return ( $("#additional_material_sub_type_1").val()!="" || $("#additional_Quantity_1").val()!="");
          }
        },
        additional_Quantity_1:{
          required: function() {
           return ( $("#additional_material_type_1").val()!="" );
          }
        },
        additional_unit_type_1:{
          required: function() {
           return ( $("#additional_material_type_1").val()!="" );
          }
        }
      },

      messages: {
        material_type_1: {
          required: "Please select the material type "
        },
        material_sub_type_1: {
          required: "Please select the sub type"
        },
        Quantity_1:{
          required: "Please enter the quantity",
        },
        Required_by:{
          required: "Please select the required by date",
        },
        additional_material_type_1: {
          required: "Please enter the material type "
        },
        additional_material_sub_type_1: {
          required: "Please enter the sub type"
        },
        additional_Quantity_1:{
          required: "Please enter the quantity",
        },


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
  
  }
  data = {"function": 'BOQ', "method":"SelectBOQ_Id","boq_id":boq_id,"TenderId":TenderId };
  apiCall(data,successFn);
}



function RejectOrder_TenderDetailsBOQPO(id, boq_id){
   Swal.fire({
    title: 'Are you sure to reject ?',
    icon: 'warning',
    showCancelButton: true,
    cancelButtonColor: '#d33',
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'Reject'
  }).then((result) => {
    if (result.isConfirmed) {
      $('#ActionTd_'+id).html('<span class="text-warning">Please Wait...</span>');
        setTimeout(function () {

          Swal.close();

        }, 1000);

      successFn = function(resp)  {
        // BOQs(tender_id);
          ViewBOQsPO_Tab(resp.data.BOQ_id,resp.data.tender_id);
      }
      data = {"function": 'BOQ', "method":"Reject_BOQ_Id","id":id};
      apiCall(data,successFn);
          
      Swal.fire(
        'Rejected!',
        'Record Rejected Successfully.',
        'success'
      );
      
    }
  });
        
}



function RejectOrder_TenderDetailsPORequest(id, boq_id){
   Swal.fire({
    title: 'Are you sure to reject ?',
    icon: 'warning',
    showCancelButton: true,
    cancelButtonColor: '#d33',
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'Reject'
  }).then((result) => {
    if (result.isConfirmed) {
      $('#ActionTd_'+id).html('<span class="text-warning">Please Wait...</span>');
        setTimeout(function () {
          Swal.close();
        }, 1000);
      successFn = function(resp)  {
          Details_PORequest();
      }
      data = {"function": 'BOQ', "method":"Reject_BOQ_Id","id":id};
      apiCall(data,successFn);
          
      Swal.fire(
        'Rejected!',
        'Record Rejected Successfully.',
        'success'
      );
      
    }
  });
        
}

function CheckDescriptionInitialiseText(description){
  // checkDescription=$('#description_hidden').val();
  // res1 = description.substring(0, 121);
  // res2 = checkDescription.substring(0, 121);
  // if (res1!=res2){
  //   $('#description').val(checkDescription);
  // }
}

function Documents(){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      loadTenderDetailsTemplate('#Documents_tmpl',toMustacheDataObj(resp), 'Documents');
      $("#Dt_Documents").DataTable();

      for (var key in resp.data.Documents) {
        if(resp.data.Documents[key]["file_type"]=='jpeg' || resp.data.Documents[key]["file_type"]== 'jpg' || resp.data.Documents[key]["file_type"]== 'gif' || resp.data.Documents[key]["file_type"]== 'png'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-image text-warning'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='sheet'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-excel text-success'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='document' ){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-word  text-info'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='pdf'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-pdf text-danger'></i>");
        }
      }

      $('#AddDocumentsForm').validate({
        ignore: [], //disable form auto submit on button click
        submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#AddDocumentsForm");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'AddNewDocument');
        data.append('tender_id', $('#tender_id_details').val());

        $.ajax({
          xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = ((evt.loaded / evt.total) * 100);
                    $(".progressDoc-bar").width(percentComplete + '%');
                    $(".progressDoc-bar").html(percentComplete+'%');
                }
            }, false);
            return xhr;
          },
          type: 'POST',
          url: 'ajaxHandler.php',
          data: data,
          contentType: false,
          processData:false,
          beforeSend: function(){
              $(".progressDoc-bar").width('0%');
              // $('#uploadStatus').html('<img src="images/loading.gif"/>');
          },
          error:function(){
              // $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
          },
          success: function(result){
            setTimeout(function () {
              Documents();
            }, 1000);
          }
        });
      },

      rules: {
       document_category:{
          required: true,
        },
        document_tag1:{
          required: true,
        },
        // document_tag2:{
        //   required: true,
        // },
        UploadDocument:{
          required: true,
        }
      },

      messages: {
        document_category: {
          required: "Please select a category.",
        },
        document_tag1: {
          required: "Please enter the tag."
        },
        document_tag2: {
          required: "Please  enter the tag."
        },
        UploadDocument: {
          required: "Please upload the document."
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

      
    }

  }
  data = {"function": 'TenderDetails', "method":"DocumentsNCategory", "tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}

// function Documents_orginal(category=''){
//   successFn = function(resp)  { 
//     if(resp.status==0){
//       Swal.fire({
//         icon: 'error',
//         title: 'Oops...',
//         text: resp.data,
//       });
//     }else if(resp.status==1){
//       resp.data.admin=parseInt(resp.data.admin);
//       loadTenderDetailsTemplate('#Documents_tmpl',toMustacheDataObj(resp), 'Documents');
//       $("#Dt_Documents").DataTable();

//       for (var key in resp.data.Documents) {
//         if(resp.data.Documents[key]["file_type"]=='jpeg' || resp.data.Documents[key]["file_type"]== 'jpg' || resp.data.Documents[key]["file_type"]== 'gif' || resp.data.Documents[key]["file_type"]== 'png'){
//           $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-image text-warning'></i>");
//         }else if(resp.data.Documents[key]["file_type"]=='sheet'){
//           $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-excel text-success'></i>");
//         }else if(resp.data.Documents[key]["file_type"]=='document' ){
//           $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-word  text-info'></i>");
//         }else if(resp.data.Documents[key]["file_type"]=='pdf'){
//           $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-pdf text-danger'></i>");
//         }
//       }


//     $('#AddDocumentsForm').validate({
//        ignore: [], //disable form auto submit on button click
//       submitHandler: function(form) {
//         $('#SubmitButton').addClass('d-none');
//         $('#LoadingBtn').removeClass('d-none');
//         var form = $("#AddDocumentsForm");
//         var data = new FormData(form[0]);
//         data.append('function', 'TenderDetails');
//         data.append('method', 'AddNewDocument');
//         data.append('tender_id', $('#tender_id_details').val());

//         successFn = function(resp)  {
//           if(resp.status==0){
//             Swal.fire({
//               icon: 'error',
//               title: 'Oops...',
//               text: resp.data,
//             });
//           }else if(resp.status==1){
//             SwalRoundTick('Record Updated Successfully');
         
//             // $('#BaseModal').modal('hide');
//             Documents();

//           }
//         }
       
//         apiCallForm(data,successFn);
//       },

//       rules: {
//        document_category:{
//           required: true,
//         },
//         document_tag1:{
//           required: true,
//         },
//         document_tag2:{
//           required: true,
//         },
//         UploadDocument:{
//           required: true,
//         }
//       },

//       messages: {
//         document_category: {
//           required: "Please select a category.",
//         },
//         document_tag1: {
//           required: "Please enter the tag."
//         },
//         document_tag2: {
//           required: "Please  enter the tag."
//         },
//         UploadDocument: {
//           required: "Please upload the document."
//         }

//       },
//       errorElement: 'span',
//       errorPlacement: function (error, element) {
//         error.addClass('invalid-feedback');
//         element.closest('.form-group').append(error);
//       },
//       highlight: function (element, errorClass, validClass) {
//         $(element).addClass('is-invalid');
//       },
//       unhighlight: function (element, errorClass, validClass) {
//         $(element).removeClass('is-invalid');
//       }
//     });

//     }

//   }
//   data = {"function": 'TenderDetails', "method":"DocumentsNCategory", "tender_id":$('#tender_id_details').val()};
//   apiCall(data,successFn);
// }


function DownloadImage(src, fileName ){
  a = document.createElement('a');
  a.href = src;
  a.download = fileName;
  a.click();
}



function DownloadImage_PHPCode(id){
  successFn = function(resp)  { 
    a = document.createElement('a');
    a.href = resp.data.file;
    a.download = resp.data.fileName;
    a.click();
  }
  data = {"function": 'TenderDetails', "method":"Documents_Id", "id":id};
  apiCall(data,successFn);
}


function clickToCopy(id) {
  successFn = function(resp)  { 
    if(resp.status==1){
      navigator.clipboard.writeText(resp.data.file);
      Swal.fire('Link Copied');
      setTimeout(function () {
        Swal.close();
      }, 1000);
    }else{

    }
  }
  data = {"function": 'TenderDetails', "method":"Documents_Id", "id":id};
  apiCall(data,successFn);

}

function filterCategory(category){
  successFn = function(resp)  { 
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      resp.data.admin=parseInt(resp.data.admin);
      loadTenderDetailsTemplate('#Documents_tmpl_sub',toMustacheDataObj(resp), 'TableForSelectedDocumentCategory');
      $("#Dt_Documents_sub").DataTable();

      for (var key in resp.data.Documents) {
        if(resp.data.Documents[key]["file_type"]=='jpeg' || resp.data.Documents[key]["file_type"]== 'jpg' || resp.data.Documents[key]["file_type"]== 'gif' || resp.data.Documents[key]["file_type"]== 'png'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-image text-warning'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='sheet'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-excel text-success'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='document' ){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-word  text-info'></i>");
        }else if(resp.data.Documents[key]["file_type"]=='pdf'){
          $('#DocList_'+resp.data.Documents[key]["id"]).html("<i class='fas fa-2x  fa-file-pdf text-danger'></i>");
        }
      }

    }
  }
  data = {"function": 'TenderDetails', "method":"DocumentsCategory_Id", "tender_id":$('#tender_id_details').val(),"category":category};
  apiCall(data,successFn);

}

function Delete_ConfirmedRequest(id, TenderId, fileLoc){
  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      $('#Td_PO_Action_'+id).html("<span class='text-warning'>Please wait..</span>");

      successFn = function(resp)  {

        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){

          Swal.fire(
            'Deleted!',
            'This record is removed successfully',
            'success'
          );
          Details_PO();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
          
      }
      data = {"function": 'TenderDetails', "method": "DeleteConfirmedRequest","id":id ,"fileLoc":fileLoc};
      apiCall(data,successFn);

     
      
    }
  });


}

function UnconfirmedRequests(tender_id){
  successFn = function(resp)  {
    for (var key in resp.data.BOQ) {
        resp.data.BOQ[key]["status"]=parseInt(resp.data.BOQ[key]["status"]);
        resp.data.BOQ[key]["purchase"]=parseInt(resp.data.BOQ[key]["purchase"]);
        resp.data.BOQ[key]["reject"]=parseInt(resp.data.BOQ[key]["reject"]);
        resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);
        resp.data.BOQ[key]["approved"]=parseInt(resp.data.BOQ[key]["approved"]);
        resp.data.BOQ[key]["required_by"]= moment(resp.data.BOQ[key]["required_by"]).format('DD-MM-YYYY');

        if(resp.data.BOQ[key]["pdf_location"]==""){
          resp.data.BOQ[key]["pdf_Available"]=0;
        }else{
          resp.data.BOQ[key]["pdf_Available"]=1;
        }
    }
    resp.data.admin=parseInt(resp.data.admin);

    // loadTenderDetailsTemplate('#PO_BOQ_templ3',toMustacheDataObj(resp), 'Details_PO');
    $('[data-toggle="tooltip"]').tooltip('dispose'); 
    loadTenderDetailsTemplate('#PO_BOQ_unconfirmed',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');


    $('[data-toggle="tooltip"]').tooltip();
    $("#Dt_PO_BOQ").DataTable({'order':[], dom: 'Blfrtip',
        buttons: [              
          { extend: 'print', messageTop: '<h3> Tender PO</h3>',className: 'btn btn-link btn-lg text-info ', 
                exportOptions: { columns: [0, 1, 2,3,4,5,6,7] }, titleAttr: 'Print' },
            { extend: 'pdfHtml5', messageTop: 'Tender',className: 'btn btn-link btn-lg text-danger ', 
                exportOptions: { columns: [0, 1, 2,3,4,5,6,7] }, titleAttr: 'PDF' },
        ], 
        initComplete: function() {
          $('.buttons-pdf').html('<i class="fa fa-file-pdf"/>');
          $('.buttons-print').html('<i class="fa fa-print"/>');
        }
    });
  }
  data = {"function": 'TenderDetails', "method":"unconfirmed_POs","tender_id":tender_id};
  apiCall(data,successFn);
}



function GalleryView(){
  $('#GalleryViewDiv').empty();
  successFn = function(resp)  {
    loadTenderDetailsTemplate('#GalleryView_tmpl',toMustacheDataObj(resp), 'GalleryViewDiv');
    $("#D_"+resp.data.GalleryView[0]["date"]+"-tab").click();
  }
  data = {"function": 'TenderDetails', "method":"GalleryView","tender_id":$('#tender_id_details').val()};
  apiCall(data,successFn);
}

function setDate(dat='tomorrow',id=''){
  var currentDate = new Date();

  if(dat=='today'){
    var year = currentDate.getFullYear();
    var month = String(currentDate.getMonth() + 1).padStart(2, '0');
    var date = String(currentDate.getDate()).padStart(2, '0');
    var dateString = year + '-' + month + '-' + date;
  }else if(dat=='tomorrow'){
      var tomorrowDate = new Date(currentDate);
      tomorrowDate.setDate(currentDate.getDate() + 1);
      var year = tomorrowDate.getFullYear();
      var month = String(tomorrowDate.getMonth() + 1).padStart(2, '0');
      var date = String(tomorrowDate.getDate()).padStart(2, '0');
      var dateString = year + '-' + month + '-' + date;
  }
  $("#"+id).val(dateString);

}

function Edit_ProgressDate(id,date){
  loadTenderDetailsTemplate('#EditProgressDate_tmpl',{}, 'BaseModal');
  $('#BaseModal').modal('show');
  var parts = date.split("-");
  var formattedDate = parts[2] + "-" + parts[1] + "-" + parts[0];
  $('#EditDateProgress').val(formattedDate);
   
   $('#Form_EditProgressDate').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#Form_EditProgressDate");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'EditProgressDate');
        data.append('id', id);
        data.append('old_date', date);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
         
            $('#BaseModal').modal('hide');
            DailyProgress();
          }
        }
       
        apiCallForm(data,successFn);
      },

      rules: {
       EditDateProgress:{
          required: true,
        },
      
      },
      messages: {
        EditDateProgress: {
          required: "Please select a date.",
        },
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

}



function Edit_ChallanDate(id,date){
  loadTenderDetailsTemplate('#EditChallanDate_tmpl',{}, 'BaseModal');
  $('#BaseModal').modal('show');
  var parts = date.split("-");
  var formattedDate = parts[2] + "-" + parts[1] + "-" + parts[0];
  $('#EditDateChallan').val(formattedDate);
   
   $('#Form_EditChallanDate').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#Form_EditChallanDate");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'EditChallanDate');
        data.append('id', id);
        data.append('old_date', date);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
         
            $('#BaseModal').modal('hide');
            challanBills();
          }
        }
       
        apiCallForm(data,successFn);
      },

      rules: {
       EditDateChallan:{
          required: true,
        },
      
      },
      messages: {
        EditDateChallan: {
          required: "Please select a date.",
        },
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

}

function Edit_ExpensesDate(id,date){
  loadTenderDetailsTemplate('#EditExpensesDate_tmpl',{}, 'BaseModal');
  $('#BaseModal').modal('show');
  var parts = date.split("-");
  var formattedDate = parts[2] + "-" + parts[1] + "-" + parts[0];
  $('#EditDateExpenses').val(formattedDate);
   
   $('#Form_EditExpensesDate').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#Form_EditExpensesDate");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'EditExpensesDate');
        data.append('id', id);
        data.append('old_date', date);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
         
            $('#BaseModal').modal('hide');
            Expenses();
          }
        }
       
        apiCallForm(data,successFn);
      },

      rules: {
       EditDateExpenses:{
          required: true,
        },
      
      },
      messages: {
        EditDateExpenses: {
          required: "Please select a date.",
        },
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

}


function Edit_LabourBillDate(id,date){
  loadTenderDetailsTemplate('#EditLabourBill_tmpl',{}, 'BaseModal');
  $('#BaseModal').modal('show');
  var parts = date.split("-");
  var formattedDate = parts[2] + "-" + parts[1] + "-" + parts[0];
  $('#EditDateLabourBill').val(formattedDate);
   
   $('#Form_LabourBillDate').validate({
       ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#Form_LabourBillDate");
        var data = new FormData(form[0]);
        data.append('function', 'TenderDetails');
        data.append('method', 'EditLabourBillDate');
        data.append('id', id);
        data.append('old_date', date);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            SwalRoundTick('Record Updated Successfully');
         
            $('#BaseModal').modal('hide');
            AdditionalLabourCostDetails();
          }
        }
       
        apiCallForm(data,successFn);
      },

      rules: {
       EditDateLabourBill:{
          required: true,
        },
      
      },
      messages: {
        EditDateLabourBill: {
          required: "Please select a date.",
        },
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

}

function PlaceRequest_BypassBOQ(tender_id){
  successFn = function(resp)  {
    loadTenderDetailsTemplate('#BypassBOQ_tmpl',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');
    loadTenderDetailsTemplate('#Bypass_BOQ_order_templ_empty',{}, 'Sub_ByPassBOQ');
  }
  data = {"function": 'TenderDetails', "method":"ByPass_GetBOQ","tender_id":tender_id};
  apiCall(data,successFn);



}

function Select_BypassBOQlist(boq_id, TenderId){
  if(boq_id==''){
    loadTenderDetailsTemplate('#Bypass_BOQ_order_templ_empty',{}, 'Sub_ByPassBOQ');
    return false;
  }

  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);
    loadTenderDetailsTemplate('#Bypass_BOQ_order_templ',toMustacheDataObj(resp), 'Sub_ByPassBOQ');
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();
    setDate('tomorrow','Required_by');


    $('#Form_LabourBillDate').validate({
      submitHandler: function(form) {

        var MaterialsOrdered = new Array();
        var AdditionalMaterialsOrdered = new Array();
        var totalRows=  $("#HidRowCount").val();
        var Required_by=  $("#Required_by").val();
        var remaining_qty=  parseInt($("#remaining_qty").val());
        var TotalOrderedMO=  0;
        var TotalOrderedAMO=  0;
        var j=0;
        for (i =1; i <= totalRows; i++) {
          j++;
          if($("#material_type_"+i).length == 0 || $("#material_type_"+i).val()=="") {
              j--;
              continue;
          }
          MaterialsOrdered[j] ={'material_type':$("#material_type_"+i).val(), 'sub_type':$("#material_sub_type_"+i).val(), 'Quantity':$("#Quantity_"+i).val(), 'unit_id':$("#unit_type_"+i).val(), 'unit_name':$("#unit_type_"+i+" option:selected").text()};
          TotalOrderedMO = TotalOrderedMO+parseInt($("#Quantity_"+i).val());

        }

        // if(MaterialsOrdered?.length == 0) {
        //   Swal.fire("Please add one Material Type.");
        //   return false;
        // } 

        var additional_totalRows=  $("#additional_HidRowCount").val();

        var k=0;
        for (l =1; l <= additional_totalRows; l++) {
          k++;
          if($("#additional_material_type_"+l).length == 0 || $("#additional_material_type_"+l).val()=="") {
              k--;
              continue;
          }

          // alert(k);

          AdditionalMaterialsOrdered[k] ={'additional_material_type':$("#additional_material_type_"+l).val(), 'additional_sub_type':$("#additional_material_sub_type_"+l).val(), 'additional_Quantity':$("#additional_Quantity_"+l).val(), 'additional_unit_id':$("#additional_unit_type_"+l).val(), 'additional_unit_name':$("#additional_unit_type_"+l+" option:selected").text()};
          TotalOrderedAMO= TotalOrderedAMO + parseInt($("#additional_Quantity_"+l).val());
          // alert(AdditionalMaterialsOrdered[k]['additional_material_type']);
        }


        if(AdditionalMaterialsOrdered.length == 0 &&  MaterialsOrdered.length == 0) {
          Swal.fire("Please add one Material Type.");
          return false;
        } 

        TotalOrdered=TotalOrderedMO+TotalOrderedAMO;
        if(TotalOrdered>remaining_qty){
          Swal.fire("Quantity ordered is more than Remaining Quantity");
          return false;
        }
        
        $('#LoadingBtn').removeClass('d-none');
        $('#SubmitButton').addClass('d-none');

        successFn = function(resp)  {
          $('#BaseModal').modal('hide');
          // alert(resp.data.boq_id);
          // alert(resp.data.tender_id);
          // RequestedPO_tab(resp.data.boq_id,resp.data.tender_id);
          $('#LoadingBtn').addClass('d-none');
          $('#SubmitButton').removeClass('d-none');
           
        }
        data = {"function": 'BOQ', "method": 'SavePORequest',"MaterialsOrdered":MaterialsOrdered,"AdditionalMaterialsOrdered":AdditionalMaterialsOrdered,"Required_by":Required_by,
        'boq_id':$("#boq_id").val(), 'tender_id':$("#tender_id").val(), 'request_note':$("#request_note").val()};
        apiCall(data,successFn);


      },

      rules: {
        material_type_1:{
          required: function() {
           return (  $("#Quantity_1").val()!="");
          }
        },
        Quantity_1:{
          required: function() {
           return ( $("#material_type_1").val()!="" );
          }
        },
        unit_type_1:{
          required: function() {
           return ( $("#material_type_1").val()!="" );
          }
        },
        Required_by:{
          required: true,
        },
        additional_material_type_1:{
          required: function() {
           return ( $("#additional_material_sub_type_1").val()!="" || $("#additional_Quantity_1").val()!="");
          }
        },
        additional_Quantity_1:{
          required: function() {
           return ( $("#additional_material_type_1").val()!="" );
          }
        },
        additional_unit_type_1:{
          required: function() {
           return ( $("#additional_material_type_1").val()!="" );
          }
        }
      },

      messages: {
        material_type_1: {
          required: "Please select the material type "
        },
        material_sub_type_1: {
          required: "Please select the sub type"
        },
        Quantity_1:{
          required: "Please enter the quantity",
        },
        Required_by:{
          required: "Please select the required by date",
        },
        additional_material_type_1: {
          required: "Please enter the material type "
        },
        additional_material_sub_type_1: {
          required: "Please enter the sub type"
        },
        additional_Quantity_1:{
          required: "Please enter the quantity",
        },


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
  
  }
  data = {"function": 'BOQ', "method":"SelectBOQ_Id","boq_id":boq_id,"TenderId":TenderId };
  apiCall(data,successFn);



}


function Delete_PORequest(id){
Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {

        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          Swal.fire(
            'Deleted!',
            'Your Record is deleted successfully.',
            'success'
          );
          Details_PORequest();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
          
      }
      data = {"function": 'TenderDetails', "method": "DeletePORequest","id":id };
      apiCall(data,successFn);

      
      
    }
  });
}