var BOQ;
$( document ).ready(function() {
  $.get('template/BOQ.htm', function(templates) {
      BOQ = templates;
      // employees();
  });
});

function loadBOQTemplate(templateId, data, id="Dashboard"){
  var template = $(BOQ).filter(templateId).html();
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





function BOQs(id){
  // $("body").tooltip({ selector: '[data-toggle=tooltip]' });
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  // alert(id);
  successFn = function(resp)  {
    // resp.data.Access=parseInt(resp.data.Access);
    // console.log(resp.data)
    resp.data.admin=parseInt(resp.data.admin);
    resp.data.SiteIncharge=parseInt( resp.data.SiteIncharge);
    
    for (var key in resp.data.BOQ) {
        resp.data.BOQ[key]["confirm"]=parseInt(resp.data.BOQ[key]["confirm"]);
        resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
    }
    loadBOQTemplate('#BOQ_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#Dt_BOQ").DataTable();
    $('[data-toggle="tooltip"]').tooltip();

    

     // Order_BOQ(661);//Delete This
  }
  data = {"function": 'BOQ', "method":"BOQList","id":id};
  apiCall(data,successFn);
}

function Delete_BOQ(id, tender_id){

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
            BOQs(tender_id);
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

function Edit_BOQ_TotalQty( boq_id, tender_id, Quantity,item,description,units,remainingQty){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 

  loadBOQTemplate('#EditTotalQty_templ',{}, 'BaseModal');
  $('[data-toggle="tooltip"]').tooltip();
  
  $('#BaseModal').modal('show');
  $("#product").val(item);

  $("#id").val(boq_id);
  $("#NewTotalQty").val(Quantity);
  $("#tender_id").val(tender_id);

  $("#boq_description").html(description);
  $("#boq_units").val(units);
  $("#boq_remainingQty").val(remainingQty);



  $('#UpdateTotalQtyForm').validate({
    submitHandler: function(form) {
      var form = $("#UpdateTotalQtyForm");
      var data = new FormData(form[0]);
      data.append('function', 'BOQ');
      data.append('method', 'UpdateTotalQty');
      // data.append('save', save);

      successFn = function(resp)  {
        // BOQs(resp.data);
        TenderBOQDetails();
        if(resp.status==0){
          Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
          });
        }else if(resp.status==1){
          SwalRoundTick('Record Updated Successfully');
          $('#BaseModal').modal('hide');
        }
      }
      apiCallForm(data,successFn);
    },

    rules: {
      NewTotalQty:{
        required: true,
      }
    },

    messages: {
      NewTotalQty: {
        required: "Please enter the required quantity."
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

function RejectOrder_BOQ(id, boq_id){

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
          ViewBOQsPO(resp.data.BOQ_id,resp.data.tender_id);

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




function ConfirmBOQsPO(id){

  successFn = function(resp)  {
    loadBOQTemplate('#Confirm_PO_order_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');


    totalQuantity = 0;
    totalunit_price = 0;

     i=0;
    for (var key in resp.data.porequstMeterials) {
      i++;
      SNo=i;

      id=resp.data.porequstMeterials[key]["id"];
      materialSubType=resp.data.porequstMeterials[key]["material_sub_type"];
      materialSubType_id=resp.data.porequstMeterials[key]["material_sub_type_id"];
      materialType=resp.data.porequstMeterials[key]["material_type"];
      materialType_id=resp.data.porequstMeterials[key]["material_type_id"];
      quantityRequested=resp.data.porequstMeterials[key]["quantity_requested"];
      materialDescription=resp.data.porequstMeterials[key]["material_description"];
      unit_price=resp.data.porequstMeterials[key]["unit_price"];
      unit_name=resp.data.porequstMeterials[key]["unit_name"];

      totalQuantity= totalQuantity+parseInt(quantityRequested);
      totalunit_price = totalunit_price+parseInt(unit_price);
      ReceivedQty = totalunit_price+parseInt(unit_price);
      
      $('#PO_confirmed_body').append('<tr id="MaterialsRow_'+i+'"><td>'+SNo+'</td><td><input type="hidden" id="row_id_'+i+'" name="row_id_'+i+'" value="'+id+'"><input type="hidden" id="material_type_id_'+i+'" name="material_type_id_'+i+'" value="'+materialType_id+'"><input type="hidden" id="sub_type_id_'+i+'" name="sub_type_id_'+i+'" value="'+materialSubType_id+'"><input type="text" class="form-control form-control-sm" id="material_type_'+i+'" name="material_type_'+i+'" readonly value="'+materialType+'"></td><td><input type="text" class="form-control form-control-sm" id="sub_type_'+i+'" name="sub_type_'+i+'" readonly value="'+materialSubType+'"></td><td><input type="text" class="form-control form-control-sm " readonly  value="'+unit_name+'"></td><td><input type="text" class="form-control form-control-sm text-right " id="requested_qty_'+i+'" name="requested_qty_'+i+'" readonly value="'+quantityRequested+'"></td><td><input type="number" class="form-control text-right form-control-sm" id="confirmed_qty_'+i+'" name="confirmed_qty_'+i+'" readonly value="'+quantityRequested+'" onkeyup="CalculateConfirmQty()"></td><td><input type="text" class="form-control form-control-sm text-right " readonly id="material_description_'+i+'" name="material_description_'+i+'"   value="'+materialDescription+'"></td><td><input type="number" class="form-control form-control-sm text-right " id="unit_price_'+i+'" name="unit_price_'+i+'"  onkeyup="CalculateConfirmQty()"  readonly  value="'+unit_price+'"></td><td><input type="number" class="form-control form-control-sm text-right " id="quantity_received_'+i+'" name="quantity_received_'+i+'"  onkeyup="CalculateReceivedQty();" onchange="CalculateReceivedQty();"  value="'+quantityRequested+'"></td></tr>');

      // <td><input type="checkbox" class="" id="select_'+i+'" name="select_'+i+'" style="margin-right:1px" checked></td>

      $('#HidRowCount').val(i);


    }

    if(i>0){
      $("#StandardMaterialsBlock1").removeClass('d-none');
    }
    
    // ,'material_type':$("#material_type_"+l).val(),'material_sub_type':$("#material_sub_type_"+l).val(),'material_desc':$("#material_desc_"+l).val()
    k=0
    for (var key in resp.data.PO_DataAdd) {
      k++;
      SNo=k;
      id_add=resp.data.PO_DataAdd[key]["id"];
      materialType=resp.data.PO_DataAdd[key]["material_type"];
      materialSubType=resp.data.PO_DataAdd[key]["material_sub_type"];
      materialSubType_id=0;
      materialType_id=0;
      quantityRequested=resp.data.PO_DataAdd[key]["quantity_requested"];
      materialDescription=resp.data.PO_DataAdd[key]["material_description"];
      unit_price=resp.data.PO_DataAdd[key]["unit_price"];
      unit_name=resp.data.PO_DataAdd[key]["unit_name"];

      totalQuantity= totalQuantity+parseInt(quantityRequested);
      totalunit_price = totalunit_price+parseInt(unit_price);
      ReceivedQty = totalunit_price+parseInt(unit_price);
      
      $('#PO_confirmed_body_add').append('<tr id="MaterialsRow_add_'+k+'"><td>'+SNo+'</td><td><input type="hidden" id="material_type_'+k+'" name="material_type_'+k+'" value="'+materialType+'"><input type="hidden" id="material_sub_type_'+k+'" name="material_sub_type_'+k+'" value="'+materialSubType+'"><input type="hidden" id="row_id_add_'+k+'" name="row_id_add_'+k+'" value="'+id_add+'"><input type="hidden" id="material_type_id_add_'+k+'" name="material_type_id_add_'+k+'" value="'+materialType_id+'"><input type="hidden" id="sub_type_id_add_'+k+'" name="sub_type_id_add_'+k+'" value="'+materialSubType_id+'"><input type="text" class="form-control form-control-sm" id="material_type_add_'+k+'" name="material_type_add_'+k+'" readonly value="'+materialType+'"></td><td><input type="text" class="form-control form-control-sm" id="sub_type_add_'+k+'" name="sub_type_add_'+k+'" readonly value="'+materialSubType+'"></td><td><input type="text" class="form-control form-control-sm " readonly value="'+unit_name+'"></td><td><input type="text" class="form-control form-control-sm text-right " id="requested_qty_add_'+k+'" name="requested_qty_add_'+k+'" readonly value="'+quantityRequested+'"></td><td><input type="number" class="form-control text-right form-control-sm" id="confirmed_qty_add_'+k+'" name="confirmed_qty_add_'+k+'" readonly value="'+quantityRequested+'" onkeyup="CalculateConfirmQty()"></td><td><input type="text" class="form-control form-control-sm text-right " readonly id="material_description_add_'+k+'" name="material_description_add_'+k+'"   value="'+materialDescription+'"></td><td><input type="number" class="form-control form-control-sm text-right " id="unit_price_add_'+k+'" name="unit_price_add_'+k+'"  onkeyup="CalculateConfirmQty()"  readonly  value="'+unit_price+'"></td><td><input type="number" class="form-control form-control-sm text-right " id="quantity_received_add_'+k+'" name="quantity_received_add_'+k+'"  onkeyup="CalculateReceivedQty();" onchange="CalculateReceivedQty();"  value="'+quantityRequested+'"></td></tr>');


      // <td><input type="checkbox" class="" id="select_'+i+'" name="select_'+i+'" style="margin-right:1px" checked></td>

      $('#HidRowCount_add').val(k);


    }
    if(k>0){
      $("#CustomeMaterialBlock1").removeClass('d-none');
    }

    CalculateReceivedQty();
    $("#Required_by").val(resp.data.PoData['required_by']);
    $("#description_con").val(resp.data.PoData['description']);
    $("#PORequest_id").val(resp.data.PoData['id']);
    $("#Po_note").val(resp.data.BOQ.request_note);

// alert(resp.data.PoData['description']);

// alert(totalQuantity);
    $("#Quantity_Con").val(totalQuantity);
    $("#rate_con").val(totalunit_price);

  

   
  }
  data = {"function": 'BOQ', "method":"get_purchase_boq_order","id":id};
  apiCall(data,successFn);

}

function  CalculateReceivedQty(){
  totalRows=$("#HidRowCount").val();
  totalRows_add=$("#HidRowCount_add").val();
  Total_received_quantity=0;
  j=0;
  for (i =1; i <= totalRows; i++) {
    j++;
    
    if($("#quantity_received_"+i).val()=="" ||$("#quantity_received_"+i).val()<=0){
      Swal.fire("Please enter the quantity in row :"+j);
      return false;
      break;
    }else{
      Total_received_quantity+=parseInt($("#quantity_received_"+i).val());
    }

  }
  for (i =1; i <= totalRows_add; i++) {
    j++;
    
    if($("#quantity_received_add_"+i).val()=="" ||$("#quantity_received_add_"+i).val()<=0){
      Swal.fire("Please enter the quantity in row :"+j);
      return false;
      break;
    }else{
      Total_received_quantity+=parseInt($("#quantity_received_add_"+i).val());
    }

  }
  $("#received_quantity").val(Total_received_quantity);


}


function SaveReceivedMaterials(){
  MaterialsReceived = new Array();
  MaterialsReceived_add = new Array();
  totalRows=$("#HidRowCount").val();
  totalRows_add=$("#HidRowCount_add").val();
  received_quantity=$("#received_quantity").val();
  received_date=$("#received_date").val();
  description_received=$("#description_received").val();
  po_request_id=$("#PORequest_id").val()

  var j=0;
   MaterialsReceived = [];
   MaterialsReceived_add = [];
  for (i =1; i <= totalRows; i++) {
    j++;
    // if(!$('#select_'+i).prop("checked")) {
    //   j--;
    //   continue;
    // }

    if($("#quantity_received_"+i).val()=="" ||$("#quantity_received_"+i).val()<=0){
      Swal.fire("Please enter the confirmed quantity in row :"+j);
      return false;
      break;
    }

    MaterialsReceived.push({'row_id':$("#row_id_"+i).val(),'quantity_received':$("#quantity_received_"+i).val()});

  }
  k=0
  for (l =1; l <= totalRows_add; l++) {
    k++;
    // if(!$('#select_'+i).prop("checked")) {
    //   j--;
    //   continue;
    // }

    if($("#quantity_received_add_"+l).val()=="" || $("#quantity_received_add_"+l).val()<=0){
      Swal.fire("Please enter the confirmed quantity in row :"+k);
      return false;
      break;
    }

    MaterialsReceived_add.push({'row_id':$("#row_id_add_"+l).val(),'quantity_received':$("#quantity_received_add_"+l).val()});

  }

  if(MaterialsReceived?.length == 0 ) {
    if( MaterialsReceived_add == 0){
       Swal.fire("No items added, Please contact administrator");
      return false;
    }  
  }
  

  if(received_quantity<=0){
    Swal.fire("Please enter the quantity received");
    return false;
  }
  if(received_date==""){
    Swal.fire("Please enter the received date");
    return false;
  }
 

  if(description_received==""){
    Swal.fire("Please enter the received  description");
    return false;
  }
  // alert('dgsd');
  // $('#ActionTd_'+PORequest_id).html('<span class="text-warning">Please Wait...</span>');

  $('#LoadingBtn').removeClass('d-none');
  $('#SubmitButton').addClass('d-none');

  successFn = function(resp)  {
    $('#BaseModal').modal('hide');
    // RequestedPO(resp.data.boq_id,resp.data.Tender_id);
    Details_PO();
  }
  data = {"function": 'BOQ', "method": 'Save_ReceivedQuantity',"MaterialsReceived":MaterialsReceived,"MaterialsReceived_add":MaterialsReceived_add,"received_date":received_date,"received_quantity":received_quantity,"po_request_id":po_request_id,"description_received":description_received};
  apiCall(data,successFn);

}


// function Order_BOQ(id,Tender_id){
function RequestedPO(boq_id,Tender_id){
// alert("ghhgjh");
  // $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="tooltip"]').tooltip('dispose'); 

  successFn = function(resp)  {
      for (var key in resp.data.Requests) {

        resp.data.Requests[key]["status"]=parseInt(resp.data.Requests[key]["status"]);
        resp.data.Requests[key]["purchase"]=parseInt(resp.data.Requests[key]["purchase"]);
        resp.data.Requests[key]["reject"]=parseInt(resp.data.Requests[key]["reject"]);
        resp.data.Requests[key]["received"]=parseInt(resp.data.Requests[key]["received"]);
        if(resp.data.Requests[key]["pdf_location"]==""){
          resp.data.Requests[key]["pdf_Available"]=0;
        }else{
          resp.data.Requests[key]["pdf_Available"]=1;
        }
    }
        resp.data.admin=parseInt(resp.data.admin);
      //   console.log(resp.data.Requests[key]);
      //    // quantity = parseInt(resp.data.Requests[key]["quantity_requested"]);

      //    //  TotalOrderedQty = TotalOrderedQty + quantity;
      //     // resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
      //     // resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);
      //      // resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);
      //     // alert(resp.data.BOQ[key]["received"]);
      // }

      // resp.data.BOQ[key]["TotalOrderedQty"] = TotalOrderedQty;
      // console.log(resp);
      loadBOQTemplate('#PO_BOQ_order_templ',toMustacheDataObj(resp), 'Dashboard');
    
    $('[data-toggle="tooltip"]').tooltip();

  }
  data = {"function": 'BOQ', "method":"Select_PurchaseOrder","boq_id":boq_id,"Tender_id":Tender_id};
  apiCall(data,successFn);

}

function SelectSubType(id,ListId){
  successFn = function(resp)  {

    $('#'+ListId).empty().append(new Option('Sub Type', 0));

    for (var key in resp.data.SubType) {
      $('#'+ListId).append(new Option(resp.data.SubType[key]["name"], resp.data.SubType[key]["id"]));
    }
  }
  data = {"function": 'BOQ', "method":"Select_sub_type","id":id};
  apiCall(data,successFn);

}

function checkRemainingQty(){
  
  
  remaining_qty=parseFloat($("#remaining_qty").val());
  Quantity=parseFloat($("#Quantity").val());
  if(Quantity>remaining_qty){
    $("#Quantity").val(remaining_qty);

    Swal.fire('Quantity booked is greater than remaining quantity!');

  }
}





// function PO_BOQ(boq_id,Tender_id){
  // alert();
  // successFn = function(resp)  {

  //   if(resp.status==1){

  //     for (var key in resp.data.BOQ) {
  //         resp.data.BOQ[key]["confirm"]=parseInt(resp.data.BOQ[key]["confirm"]);
  //         resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
  //         resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);
  //         // alert(resp.data.BOQ[key]["received"])
  //     }
  //     loadBOQTemplate('#PO_BOQ_templ',toMustacheDataObj(resp), 'Dashboard');
  //   }else if(resp.status==2){
  //     Swal.fire({
  //       icon: 'error',
  //       title: 'No request found!',
  //     });
  //     // alert('No record Found');
  //   }
  //   setTimeout(function () {
  //     Swal.close();
  //   }, 1000);

  // }
  // data = {"function": 'BOQ', "method":"Select_BOQPurchaseOrder","boq_id":boq_id,"Tender_id":Tender_id};
  // apiCall(data,successFn);
// }







// function Purchase_OrderedRequest_old(id, location=""){
//   successFn = function(resp)  {
//     loadBOQTemplate('#PO_order_templ',toMustacheDataObj(resp), 'BaseModal');
//     $('#BaseModal').modal('show');

//     setTimeout(function () {
//       $("#material_type").val(resp.data.BOQ['material_type']).trigger('change');;
//       $("#Quantity").val(resp.data.BOQ['quantity']);
//       $("#Required_by").val(resp.data.BOQ['required_by']);
//       // $("#").val(resp.data.BOQ['']); TotalQuantity
//       console.log(resp.data.BOQ['quantity']);
//       setTimeout(function () {
//         $("#opc_ppc").val(resp.data.BOQ['opc_ppc']);
//         // alert(resp.data.BOQ['opc_ppc']);
//       }, 200);

//     }, 100);


//      $('#PO_OrderBOQForm').validate({
//       submitHandler: function(form) {

//         // alert('asdfasd');
//         $('#ActionTd_'+id).html('<span class="text-warning">Please Wait...</span>');
//         $('#SubmitButton').addClass('d-none');
//         $('#LoadingBtn').removeClass('d-none');


//         var form = $("#PO_OrderBOQForm");
//         var data = new FormData(form[0]);
//         data.append('function', 'BOQ');
//         data.append('method', 'PurchaseOrderedRequest');
//         // data.append('save', save);

//         successFn = function(resp)  {
//           if(location==''){
//             PO_BOQ(resp.data);
//           }else if(location=='widget'){
//             ShowRequest();
//           }
//           // $('#LoadingBtn').addClass('d-none');
//           // $('#SubmitButton').removeClass('d-none');

//           if(resp.status==1){

//             // alert('login successfull');
//             SwalRoundTick('Record Updated Successfully');
            
//             $('#BaseModal').modal('hide');
//           }
//         }
       
//         apiCallForm(data,successFn);
//       },

//       rules: {
//         material_type:{
//           required: true,
//         },
//         opc_ppc:{
//           required: true,
//         },
//         Quantity:{
//           required: true,
//         },
//         Vendor:{
//           required: true,
//         },
//         Rate:{
//           required: true,
//         },
//         description:{
//           required: true,
//         },
//         Required_by:{
//           required: true,
//         }
//       },

//       messages: {
//         material_type: {
//           required: "Please choose a material type.",
//         },
//         opc_ppc: {
//           required: "Please choose a the OPC/PPC."
//         },
//         Quantity: {
//           required: "Please enter a quantity."
//         },
//         Vendor: {
//           required: "Please select a vendor."
//         },
//         Rate: {
//           required: "Please enter a rate."
//         },
//         description: {
//           required: "Please enter the description."
//         },
//         Required_by: {
//           required: "Please select a date."
     
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




   
//   }
//   data = {"function": 'BOQ', "method":"Purchase_OrderedRequest","id":id};
//   apiCall(data,successFn);
// }

// function DeleteVendor(id){


// Swal.fire({
//     title: 'Are you sure?',
//     text: "You won't be able to revert this!",
//     icon: 'warning',
//     showCancelButton: true,
//     confirmButtonColor: '#3085d6',
//     cancelButtonColor: '#d33',
//     confirmButtonText: 'Yes, delete it!'
//   }).then((result) => {
//     if (result.isConfirmed) {
//       successFn = function(resp)  {
//         Vendors();
//         setTimeout(function () {
//           Swal.close();
//         }, 1000)
//       }
//       data = {"function": 'Vendors', "method": "DeleteVendor","id":id };
//       apiCall(data,successFn);

//       Swal.fire(
//         'Deleted!',
//         'Your file has been deleted.',
//         'success'
//       );
      
//     }
//   });





// }

// function EditVendor(id,company_name,name,email,gst_no,mobile,address){
//   AddNewVendor('update');
//   setTimeout( function() {
//     $("#ModelTitle").html('Edit');
//     $("#SubmitBtnText").html('Update ');
//     $("#id").val(id);
//     $("#company_name").val(company_name);
//     $("#name").val(name);
//     $("#email").val(email);
//     $("#gst_no").val(gst_no);
//     $("#mobile").val(mobile);
//     $("#address").val(address);
 
//   }, 200); 
// }

// function AddNewVendor(save='add'){
//     loadVendorsTemplate('#addNewVendor_templ',{}, 'BaseModal');
//     $('#BaseModal').modal('show');

//     $('#VendorForm').validate({
//       submitHandler: function(form) {
//         var form = $("#VendorForm");
//         var data = new FormData(form[0]);
//         data.append('function', 'Vendors');
//         data.append('method', 'RegisterVendor');
//         data.append('save', save);

//         successFn = function(resp)  {
//           if(resp.status==0){
//             alert(resp.data);
//           }else if(resp.status==1){
//             // alert('login successfull');
//             SwalRoundTick('Record Updated Successfully');
            
//             $('#BaseModal').modal('hide');
//             Vendors();
//           }
//         }
       
//         apiCallForm(data,successFn);
//       },

//       rules: {
//         company_name:{
//           required: true,
//         },
//         email:{
//           required: true,
//           email: true,
//         },
//         name:{
//           required: true,
//         },
//         gst_no:{
//           required: true,
//         },
//         mobile:{
//           required: true,
//         },
//         address:{
//           required: true,
//         }

//       },

//       messages: {
//         company_name: {
//           required: "Please enter the company name",
//         },
//         email: {
//           required: "Please enter the email"
//         },
//         name: {
//           required: "Please enter the name"
//         },
//         gst_no: {
//           required: "Please enter the GST No"
//         },
//         mobile: {
//           required: "Please enter the mobie number"
//         },
//         address: {
//           required: "Please enter the addrers"
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
  

// }



function PlaceRequestPO(boq_id, TenderId){
    $('[data-toggle="tooltip"]').tooltip('dispose'); 

  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);

    loadBOQTemplate('#BOQ_order_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();


    $('#OrderBOQForm').validate({
      submitHandler: function(form) {

        var MaterialsOrdered = new Array();
        var AdditionalMaterialsOrdered = new Array();

        var totalRows=  $("#HidRowCount").val();
        Required_by=  $("#Required_by").val();

        var j=0;
        for (i =1; i <= totalRows; i++) {
          j++;
          if($("#material_type_"+i).length == 0 || $("#material_type_"+i).val()=="") {
              j--;
              continue;
          }

          MaterialsOrdered[j] ={'material_type':$("#material_type_"+i).val(), 'sub_type':$("#material_sub_type_"+i).val(), 'Quantity':$("#Quantity_"+i).val()};

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

          AdditionalMaterialsOrdered[k] ={'additional_material_type':$("#additional_material_type_"+l).val(), 'additional_sub_type':$("#additional_material_sub_type_"+l).val(), 'additional_Quantity':$("#additional_Quantity_"+l).val()};
          // alert(AdditionalMaterialsOrdered[k]['additional_material_type']);
        }

        if(AdditionalMaterialsOrdered.length == 0 &&  MaterialsOrdered.length == 0) {
          Swal.fire("Please add one Material Type.");
          return false;
        } 


        $('#LoadingBtn').removeClass('d-none');
        $('#SubmitButton').addClass('d-none');

        successFn = function(resp)  {
          $('#BaseModal').modal('hide');
          // alert(resp.data.boq_id);
          // alert(resp.data.tender_id);
          RequestedPO(resp.data.boq_id,resp.data.tender_id)
          $('#LoadingBtn').addClass('d-none');
          $('#SubmitButton').removeClass('d-none');
           
        }
        data = {"function": 'BOQ', "method": 'SavePORequest',"MaterialsOrdered":MaterialsOrdered,"AdditionalMaterialsOrdered":AdditionalMaterialsOrdered,"Required_by":Required_by,
        'boq_id':$("#boq_id").val(), 'tender_id':$("#tender_id").val(),'request_note':$("#request_note").val() };
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



// Add row dynamically to a table
function DeleteRowMaterialType(Row_id){
 $('#MaterialsRow_'+Row_id).remove();
}
function DeleteRowAdditional_MaterialType(Row_id){
 $('#additional_MaterialsRow_'+Row_id).remove();
}

function AddRowMaterialType(){
  var ID=Number($("#HidRowCount").val());
  var Id_N=ID+1; 
  $("#HidRowCount").val(Id_N);
  $('#PO_request_body').append('<tr id="MaterialsRow_'+Id_N+'"><td class="form-group"><select class="form-control form-control-sm" id="material_type_'+Id_N+'" name="material_type_'+Id_N+'"></select></td><td class="form-group"><select class="form-control form-control-sm" id="material_sub_type_'+Id_N+'" name="material_sub_type_'+Id_N+'" ></select></td><td class="form-group"><select class="form-control form-control-sm" id="unit_type_'+Id_N+'" name="unit_type_'+Id_N+'"></select></td><td class="form-group"><input type="number" class="form-control form-control-sm" id="Quantity_'+Id_N+'" name="Quantity_'+Id_N+'"></td><td><a href="#" onclick="DeleteRowMaterialType('+Id_N+');"class="text-danger"><i class="fas fa-trash"   title="Remove"  data-toggle="tooltip" data-placement="top"  ></i></a></td></tr>');

  successFn = function(resp)  {
    $('#material_type_'+Id_N).append(new Option("--SELECT--", ""));
    for (var key in resp.data.MaterialType) {
        $('#material_type_'+Id_N).append(new Option(resp.data.MaterialType[key]["name"], resp.data.MaterialType[key]["id"]+'_'+resp.data.MaterialType[key]["unit_id"]));
    }

    $('#unit_type_'+Id_N).append(new Option("--SELECT--", ""));
    for (var key in resp.data.Units) {
        $('#unit_type_'+Id_N).append(new Option(resp.data.Units[key]["unit"], resp.data.Units[key]["id"]));
    }




    // $('[data-toggle="tooltip"]').tooltip('dispose'); 
    $('[data-toggle="tooltip"]').tooltip();
    $("#material_type_"+Id_N).rules("add", {
      required: true,
      messages: {
        required: "Please select the material type"
      }
    });
   
    $("#Quantity_"+Id_N).rules("add", {
      required: true,
      messages: {
        required: "Please enter the quantity"
      }
    });

    $("#unit_type_"+Id_N).rules("add", {
      required: true,
      messages: {
        required: "Please enter the quantity"
      }
    });
  }
  data = {"function": 'BOQ', "method": 'GetMaterialType' };
  apiCall(data,successFn);

  $('#material_type_'+Id_N).on('change', function() {
    getMaterialSubType(Id_N);
  });

}


function AddRowAdditional_MaterialType(){
  var ID=Number($("#additional_HidRowCount").val());
  var Id_N=ID+1; 
  $("#additional_HidRowCount").val(Id_N);
  $('#additional_PO_request_body').append('<tr id="additional_MaterialsRow_'+Id_N+'"><td class="form-group"><input type="text" class="form-control form-control-sm" id="additional_material_type_'+Id_N+'" name="additional_material_type_'+Id_N+'"></td></td><td class="form-group"><input type="text" class="form-control form-control-sm" id="additional_material_sub_type_'+Id_N+'" name="additional_material_sub_type_'+Id_N+'"></td></td><td class="form-group"><select class="form-control form-control-sm" id="additional_unit_type_'+Id_N+'" name="additional_unit_type_'+Id_N+'"></select></td><td class="form-group"><input type="number" class="form-control form-control-sm" id="additional_Quantity_'+Id_N+'" name="additional_Quantity_'+Id_N+'"></td><td><a href="javascript:void(0)" onclick="DeleteRowAdditional_MaterialType('+Id_N+');" class="text-danger" ><i class="fas fa-trash"  title="Remove"  data-toggle="tooltip" data-placement="top"  ></i></a></td></tr>');

  successFn = function(resp)  {
    $('#additional_unit_type_'+Id_N).append(new Option("--SELECT--", ""));
    for (var key in resp.data.Units) {
        $('#additional_unit_type_'+Id_N).append(new Option(resp.data.Units[key]["unit"], resp.data.Units[key]["id"]));
    }

  }
  data = {"function": 'BOQ', "method": 'Units' };
  apiCall(data,successFn);
  

  $("#additional_material_type_"+Id_N).rules("add", {
    required: true,
    messages: {
      required: "Please enter the material type"
    }
  });
  $("#additional_Quantity_"+Id_N).rules("add", {
    required: true,
    messages: {
      required: "Please enter the quantity"
    }
  });
  $("#additional_unit_type_"+Id_N).rules("add", {
    required: true,
    messages: {
      required: "Please enter the quantity"
    }
  });

}

function getMaterialSubType(id){
  Material_Id_st=($('#material_type_'+id).val()).split("_");
  Material_Id=Material_Id_st[0];
  unit_id=Material_Id_st[1];
  $('#unit_type_'+id).val(unit_id).trigger('change');

  successFn = function(resp)  {
    $('#material_sub_type_'+id).empty().append(new Option("--SELECT--", ""));
    ind=0;
    $("#material_sub_type_"+id).rules("remove", "required"); 
    for (var key in resp.data.SubType) {
      $('#material_sub_type_'+id).append(new Option(resp.data.SubType[key]["name"], resp.data.SubType[key]["id"]));
      ind++;

    }
    if(ind>0){
      // alert(ind);
      setTimeout(function () {
        $("#material_sub_type_"+id).rules("add", {
          required: true,
          messages: {
            required: "Please enter the sub type"
          }
        });
      }, 300);

      
    }


  }
  data = {"function": 'BOQ', "method": 'GetMaterial_SubType', "Material_Id":Material_Id };
  apiCall(data,successFn);
}



function savePORequest(){
 
  var MaterialsOrdered = new Array();
  var totalRows=  $("#HidRowCount").val();
  Required_by=  $("#Required_by").val();

  var j=0;
  for (i =1; i <= totalRows; i++) {
    j++;
    if($("#material_type_"+i).length == 0) {
        j--;
        continue;
    }
    if($("#material_type_"+i).val()==""){
      Swal.fire("Please choose the material type in row : "+j)
      return false;
      break;
    }else if($("#material_sub_type_"+i).val()==""){
      Swal.fire("Please choose the sub type in row : "+j);
      return false;
      break;
    }else if($("#Quantity_"+i).val()==""){
      Swal.fire("Please enter the required quantity in row :"+j);
      return false;
      break;
    }

    MaterialsOrdered[j] ={'material_type':$("#material_type_"+i).val(), 'sub_type':$("#material_sub_type_"+i).val(), 'Quantity':$("#Quantity_"+i).val()};

  }

  if(MaterialsOrdered?.length == 0) {
    Swal.fire("Please add a row.");
    return false;

  } 
  if(Required_by==""){ Swal.fire("Please choose the required date.");
   return  false; 
  }

  $('#LoadingBtn').removeClass('d-none');
  $('#SubmitButton').addClass('d-none');

  successFn = function(resp)  {
    $('#BaseModal').modal('hide');
    // alert(resp.data.boq_id);
    // alert(resp.data.tender_id);
    RequestedPO(resp.data.boq_id,resp.data.tender_id)
    $('#LoadingBtn').addClass('d-none');
    $('#SubmitButton').removeClass('d-none');
     
  }
   data = {"function": 'BOQ', "method": 'SavePORequest',"MaterialsOrdered":MaterialsOrdered,"Required_by":Required_by,
  'boq_id':$("#boq_id").val(), 'tender_id':$("#tender_id").val
  ()};
  apiCall(data,successFn);

}

function ViewRequstedPO(PORequest_id){

  $('[data-toggle="tooltip"]').tooltip('dispose'); 
    // loadBOQTemplate('#EditTotalQty_templ',{}, 'BaseModal');
    // $('#BaseModal').modal('show');
  successFn = function(resp)  {
    loadBOQTemplate('#ViewPurchaseOrderRequest_templ',toMustacheDataObj(resp), 'BaseModal');
    
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();

  }
  data = {"function": 'BOQ', "method": 'ViewRequstedPO', 'PORequest_id':PORequest_id};
  apiCall(data,successFn);

}

function ViewBOQsPO(boq_id, Tender_id){
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

    loadBOQTemplate('#ListPurchaseOrder_templ',toMustacheDataObj(resp), 'Dashboard');
    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'BOQ', "method":"Select_BOQPurchaseOrder","boq_id":boq_id,"Tender_id":Tender_id};
  apiCall(data,successFn);
}



function Purchase_OrderedRequest(PORequest_id, location=""){
  successFn = function(resp)  {
    loadBOQTemplate('#PO_order_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

      $('#tender_id').val(resp.data.BOQ.tender_id);
      $('#boq_id').val(resp.data.BOQ.boq_id);
      $('#required_by').val(resp.data.required_by);
      $('#PORequest_id').val(PORequest_id);
      $('#Po_note').val(resp.data.BOQ.request_note);
 
    i=0;
    totalQuantityRequested = 0;
    for (var key in resp.data.PO_Data) {
      i++;
      SNo=i;

      materialSubType=resp.data.PO_Data[key]["material_sub_type"];
      materialSubType_id=resp.data.PO_Data[key]["material_sub_type_id"];
      materialType=resp.data.PO_Data[key]["material_type"];
      materialType_id=resp.data.PO_Data[key]["material_type_id"];
      quantityRequested=resp.data.PO_Data[key]["quantity_requested"];
      row_id=resp.data.PO_Data[key]["row_id"];
      unit_name=resp.data.PO_Data[key]["unit_name"];

      totalQuantityRequested = totalQuantityRequested + parseInt(resp.data.PO_Data[key]["quantity_requested"]);

      $('#PO_requested_body').append('<tr id="MaterialsRow_'+i+'"><td>'+SNo+'</td><td><input type="hidden" id="row_'+i+'" name="row_'+i+'" value="'+row_id+'"><input type="hidden" id="material_type_id_'+i+'" name="material_type_id_'+i+'" value="'+materialType_id+'"><input type="hidden" id="sub_type_id_'+i+'" name="sub_type_id_'+i+'" value="'+materialSubType_id+'"><input type="text" class="form-control form-control-sm" id="material_type_'+i+'" name="material_type_'+i+'" readonly value="'+materialType+'"></td><td><input type="text" class="form-control form-control-sm" id="sub_type_'+i+'" name="sub_type_'+i+'" readonly value="'+materialSubType+'"></td><td><input type="text" class="form-control form-control-sm " id="unit_name_'+i+'" name="unit_name_'+i+'" readonly value="'+unit_name+'"></td><td><input type="text" class="form-control form-control-sm text-right " id="requested_qty_'+i+'" name="requested_qty_'+i+'" readonly value="'+quantityRequested+'"></td><td><input type="number" class="form-control text-right form-control-sm" id="confirmed_qty_'+i+'" name="confirmed_qty_'+i+'"  value="'+quantityRequested+'" onkeyup="CalculateConfirmQty()"></td><td><input type="text" class="form-control form-control-sm text-right " id="material_description_'+i+'" name="material_description_'+i+'" ></td><td><input type="number" class="form-control form-control-sm text-right " id="unit_price_'+i+'" name="unit_price_'+i+'"  onkeyup="CalculateConfirmQty()"></td></tr>');

      // <td><input type="checkbox" class="" id="select_'+i+'" name="select_'+i+'" style="margin-right:1px" checked></td>

      $('#HidRowCount').val(i);

    }
    if(i>0){
      $("#StandardMaterialBlockPO_Order").removeClass('d-none');
    }


    // setTimeout(function () {
    k=0;
    i=0;
    for (var key in resp.data.PO_DataAdd) {
      k++
      i++;
      SNo=i;

      materialSubType=resp.data.PO_DataAdd[key]["material_sub_type"];
      materialType=resp.data.PO_DataAdd[key]["material_type"];
      quantityRequested=resp.data.PO_DataAdd[key]["quantity_requested"];
      row_id=resp.data.PO_DataAdd[key]["row_id"];
      unit_name=resp.data.PO_DataAdd[key]["unit_name"];

      totalQuantityRequested = totalQuantityRequested + parseInt(resp.data.PO_DataAdd[key]["quantity_requested"]);

      $('#PO_confirmed_body_add').append('<tr id="MaterialsRow_add_'+k+'"><td>'+SNo+'</td><td><input type="hidden" id="row_id_add_'+k+'" name="row_id_add_'+k+'" value="'+row_id+'"><input type="text" class="form-control form-control-sm" id="material_type_add_'+k+'" name="material_type_add_'+k+'" readonly value="'+materialType+'"></td><td><input type="text" class="form-control form-control-sm" id="sub_type_add_'+k+'" name="sub_type_add_'+k+'" readonly value="'+materialSubType+'"></td><td><input type="text" class="form-control form-control-sm " id="unit_name_add_'+i+'" name="unit_name_add_'+i+'" readonly value="'+unit_name+'"></td><td><input type="text" class="form-control form-control-sm text-right " id="requested_qty_add_'+k+'" name="requested_qty_add_'+k+'" readonly value="'+quantityRequested+'"></td><td><input type="number" class="form-control text-right form-control-sm" id="confirmed_qty_add_'+k+'" name="confirmed_qty_add_'+k+'"  value="'+quantityRequested+'" onkeyup="CalculateConfirmQty()"></td><td><input type="text" class="form-control form-control-sm text-right " id="material_description_add_'+k+'" name="material_description_add_'+k+'" ></td><td><input type="number" class="form-control form-control-sm text-right " id="unit_price_add_'+k+'" name="unit_price_add_'+k+'"  onkeyup="CalculateConfirmQty()"></td></tr>');
      // <td><input type="checkbox" class="" id="select_'+i+'" name="select_'+i+'" style="margin-right:1px" checked></td>
      $('#HidRowCount_add').val(k);

      }
  // }, 300);

  
    if(k>0){
      $("#CustomeMaterialBlockPO_Order").removeClass('d-none');
    }


    
    // alert(totalQuantityRequested);
    setTimeout(function () {
      $("#TotalQuantity").val(totalQuantityRequested);
    }, 300);
   
    $('#vendor').append(new Option("--SELECT--",""));
    for (var key in resp.data.vendor) {
      $('#vendor').append(new Option(resp.data.vendor[key]["name"], resp.data.vendor[key]["id"]));
    }

    // setTimeout(function () {
      CalculateConfirmQty();
    // }, 1500);



  }
  data = {"function": 'BOQ', "method":"Purchase_OrderedRequest","PORequest_id":PORequest_id};
  apiCall(data,successFn);
}

function  CalculateConfirmQty(){
  totalRows=$('#HidRowCount').val();
  totalRows_add=$('#HidRowCount_add').val();


  TotalConfirmedQty=0;
  TotalAmount=0;
  for (i =1; i <= totalRows; i++) {
    if($("#confirmed_qty_"+i).val()==""){
      continue;
    }else if($("#unit_price_"+i).val()==""){
      continue;
    }else{
      TotalConfirmedQty+=parseInt($('#confirmed_qty_'+i).val());
      TotalAmount+=parseInt($('#confirmed_qty_'+i).val())*parseFloat($('#unit_price_'+i).val());
    }
    
  }
  for (k =1; k <= totalRows_add; k++) {
    if($("#confirmed_qty_add_"+k).val()==""){
      continue;
    }else if($("#unit_price_add_"+k).val()==""){
      continue;
    }else{
      TotalConfirmedQty+=parseInt($('#confirmed_qty_add_'+k).val());
      TotalAmount+=parseInt($('#confirmed_qty_add_'+k).val())*parseFloat($('#unit_price_add_'+k).val());
    }
    
  }
  $('#TotalQuantity').val(TotalConfirmedQty);
  $('#Rate').val(TotalAmount.toFixed(2));


}


// function loadVendor(row_id){
//   successFn = function(resp)  {
//       $('#vendor_'+row_id).append(new Option("--SELECT--",""));

//     for (var key in resp.data.vendor) {
//       $('#vendor_'+row_id).append(new Option(resp.data.vendor[key]["name"], resp.data.vendor[key]["id"]));
//     }

//   }
//   data = {"function": 'BOQ', "method":"SelectVendor"};
//   apiCall(data,successFn);

// }

// function  CalculateConfirmQty(){
//   totalRows=$('#HidRowCount').val();
//   TotalConfirmedQty=0;
//   TotalAmount=0;

//   for (i =1; i <= totalRows; i++) {
//     // if(!$('#select_'+i).prop("checked")) {
//     //   continue;
//     // }
//     if($("#confirmed_qty_"+i).val()==""){
//       // Swal.fire("Please enter the confirmed qty in row : "+j)
//       return false;
//       continue;


//       break;
//     }else if($("#unit_price_"+i).val()==""){
//       // Swal.fire("Please enter the confirmed qty in row : "+j)
//       return false;
//       break;
//     }else{
//       TotalConfirmedQty+=parseInt($('#confirmed_qty_'+i).val());
//     }
    
//   }
//   $('#TotalQuantity').val(TotalConfirmedQty);


// }



function SaveConfirmRequestedPO(){
  MaterialsOrdered = new Array();
  MaterialsOrdered_add = new Array();
  tender_id=$("#tender_id").val();
  boq_id=$("#boq_id").val();
  totalRows=$("#HidRowCount").val();
  totalRows_add=$("#HidRowCount_add").val();
  required_by=$("#required_by").val();
  description=$("#description").val();
  // description=($("#description").val()).replace(/(?:(?:\r\n|\r|\n)\s*){2}/gm, "");

  Rate=$("#Rate").val();
  PORequest_id=$("#PORequest_id").val();
  TotalQuantity=$("#TotalQuantity").val();
  vendor=$("#vendor").val();

  var j=0;
  for (i =1; i <= totalRows; i++) {
    j++;
    // if(!$('#select_'+i).prop("checked")) {
    //   j--;
    //   continue;
    // }

    if($("#confirmed_qty_"+i).val()==""){
      Swal.fire("Please enter the confirmed quantity in row :"+j);
      return false;
      break;
    }else if($("#material_description_"+i).val()==""){
      Swal.fire("Please enter the material description in row : "+j);
      return false;
      break;
    }else if($("#unit_price_"+i).val()==""){
      Swal.fire("Please enter the unit price in row : "+j);
      return false;
      break;
    }

    MaterialsOrdered[j] ={'material_type_text':$("#material_type_"+i).val(),'sub_type_text':$("#sub_type_"+i).val(),'row_id':$("#row_"+i).val(),'material_type':$("#material_type_id_"+i).val(), 'sub_type':$("#material_sub_type_id_"+i).val(), 'Quantity':$("#confirmed_qty_"+i).val(),'material_description':$("#material_description_"+i).val(),'unit_price':$("#unit_price_"+i).val(),'unit_name':$("#unit_name_"+i).val() };
  }

  if(totalRows>0 ){
    if(MaterialsOrdered?.length == 0) {
      Swal.fire("Please select a row.");
      return false;

    }
  }

  var k=0;
  for (l =1; l <= totalRows_add; l++) {
    k++;
    // if(!$('#select_'+i).prop("checked")) {
    //   j--;
    //   continue;
    // }

    if($("#confirmed_qty_"+l).val()==""){
      Swal.fire("Please enter the confirmed quantity in row :"+k);
      return false;
      break;
    }else if($("#material_description_"+l).val()==""){
      Swal.fire("Please enter the material description in row : "+k);
      return false;
      break;
    }else if($("#unit_price_"+l).val()==""){
      Swal.fire("Please enter the unit price in row : "+k);
      return false;
      break;
    }
    MaterialsOrdered_add[k] ={'material_type_text':$("#material_type_add_"+l).val(),'sub_type_text':$("#sub_type_add_"+l).val(),'row_id':$("#row_id_add_"+l).val(), 'Quantity':$("#confirmed_qty_add_"+l).val(),'material_description':$("#material_description_add_"+l).val(),'unit_price':$("#unit_price_add_"+l).val(),'unit_name':$("#unit_name_add_"+l).val() };
  }

  if(totalRows_add>0 ){
    if(MaterialsOrdered_add?.length == 0) {
      Swal.fire("Please select a row.");
      return false;
    }
  }
  

  if(TotalQuantity==0){
    Swal.fire("Please enter the quantity to be ordered");
    return false;
  }
  if(Rate==""){
    Swal.fire("Please enter the unit price");
    return false;
  }
  if(vendor==""){
    Swal.fire("Please select a vendor");
    return false;
  }

  if(Rate==""){
    Swal.fire("Please enter the total amount");
    return false;
  }

  // if(description==""){
  //   Swal.fire("Please enter the description");
  //   return false;
  // }
  // alert('dgsd');
  $('#ActionTd_'+PORequest_id).html('<span class="text-warning">Please Wait...</span>');

  $('#LoadingBtn').removeClass('d-none');
  $('#SubmitButton').addClass('d-none');

  successFn = function(resp)  {
    $('#BaseModal').modal('hide');
    // RequestedPO(resp.data.boq_id,resp.data.Tender_id);

    $('#ActionTd_'+PORequest_id).html('<span class="text-warning">Ordered</span>');
    Details_PORequest();

  }
  data = {"function": 'BOQ', "method": 'Save_ConfirmRequestedPO',"MaterialsOrdered":MaterialsOrdered,"MaterialsOrdered_add":MaterialsOrdered_add,"Required_by":required_by,"TotalAmount":Rate,"PORequest_id":PORequest_id, "description":description, "TotalQuantity":TotalQuantity, "TotalAmount ": $('#Rate').val(), "vendor":vendor, "tender_id":tender_id, "boq_id":boq_id};
  apiCall(data,successFn);

}


function DeletePDF(id,row_id, PDF_Location){
  $('#'+row_id).empty();
  successFn = function(resp)  {

  }
  data = {"function": 'BOQ', "method": 'DeletePDF',"id": id};
  apiCall(data,successFn);

}
