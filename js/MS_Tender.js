// var Tender;
// $(document).ready(function() {
//   $.get('template/Tender.htm', function(templates) {
//       Tender = templates;
//       Tenders('nav-tenders')

//   });
 
// });

function loadTenderTemplate(templateId, data, id="Dashboard"){
  var template = $(Tender).filter(templateId).html();
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

function TotalRequestWidget(){
  Tenders('nav-tenders', 1)
}

function Tenders(nav='',req=0){

  // alert();
  MakeNavActive(nav);
  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);
    resp.data.SiteIncharge=parseInt(resp.data.SiteIncharge);
    // resp.data.Accounts=parseInt(resp.data.Accounts);
    for (var key in resp.data.Tender) {
      resp.data.Tender[key]["TenderStartDate"]= moment(resp.data.Tender[key]["TenderStartDate"]).format('DD-MM-YYYY');
      resp.data.Tender[key]["TenderEndDate"]= moment(resp.data.Tender[key]["TenderEndDate"]).format('DD-MM-YYYY');
      resp.data.Tender[key]["orderRequest"]=parseInt(resp.data.Tender[key]["orderRequest"]);
      resp.data.Tender[key]["TotalRequest"]=parseInt(resp.data.Tender[key]["TotalRequest"]);
      resp.data.Tender[key]["orderRequestUnconfirmed"]=parseInt(resp.data.Tender[key]["orderRequestUnconfirmed"]);

      

      if(resp.data.Tender[key]["TotalRequest"]>1){
        resp.data.Tender[key]["S"]=1;
      }else{
        resp.data.Tender[key]["S"]=0;
      }
    }
    

    loadTenderTemplate('#Tender_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#Dt_Tender").DataTable({});

    // setTimeout(function () {
    // }, 1500)

    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'Tender', "method":"TenderList","request":req};
  apiCall(data,successFn);
}





function DeleteTender(id,show=''){


  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        if(show=='dashboard'){
          dashboard();
        }else{
          Tenders();
        }
        
        setTimeout(function () {
          Swal.close();
        }, 1000)
      }
      data = {"function": 'Tender', "method": "DeleteTender","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}

function AddNewTender(save='add'){
  $('#Btn_AddNew').addClass('d-none');
  $('#Btn_CancelAddNew').removeClass('d-none');
  successFn = function(resp)  {
    loadTenderTemplate('#addNewTender_New_templ',toMustacheDataObj(resp), 'ForAddNewTender');
    $('.js-example-basic-single').select2();

    $('#TenderForm').validate({
      ignore: [], //disable form auto submit on button click
      submitHandler: function(form) {
        $('#AddNewSubmitBtn').addClass('d-none');
        $('#AddNewLoadBtn').removeClass('d-none');
        var form = $("#TenderForm");
        var data = new FormData(form[0]);
        data.append('function', 'Tender');
        data.append('method', 'RegisterTender');
        data.append('save', save);
        data.append('SiteSupervisorMultiple',$('#SiteSupervisor').val());
        data.append('SiteEngineerMultiple',$('#SiteEngineer').val());

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });

          }else if(resp.status==1){
            // alert('login successfull');
            SwalRoundTick('Record Updated Successfully');
            $('#BaseModal').modal('hide');
            Tenders();
          }
        }
        apiCallForm(data,successFn);
      },

      rules: {
        TenderName:{
          required: true,
          maxlength: 50,
        },
        WorkOrderNo:{
          required: true,
          maxlength: 4,
        },
        SiteIncharge:{
          required: true,
          // maxlength: 50,
        },
        SiteEngineer:{
          required: true,
        },
        SiteSupervisor:{
          required: true,
        },
        BMCDepartment:{
          required: true,
        },
        EMDStartDate:{
          required: true,
        },
        // EMDEndDate:{
        //   required: true,
        // },
        EMDAmount:{
          required: true,
        },
        WorkOrderText:{
          required: true,
        },
        WorkOrderDate:{
          required: true,
        },
        tender_description:{
          required: true,
          maxlength: 1000,
        },
        InsuranceCAR:{
           required: function() {
           return ($("#InsuranceCARExpiryDate").val()!="" );
          },
          maxlength: 50,
        },
        InsuranceCARExpiryDate:{
           required: function() {
           return ($("#InsuranceCAR").val()!="" );
          }
        },
        // InsuranceCARUpload:{
        //   required: true,
        // },

        PerformanceBG:{
          required: function() {
           return ( $("#BankAmount").val()!="" || $("#BGIssueDate").val()!="" || $("#BGExpiryDate").val()!="");
          }
        },
        BGIssueDate:{
         required: function() {
           return ( $("#PerformanceBG").val()!="" || $("#BankAmount").val()!="" || $("#BGExpiryDate").val()!="");
          }
        },
        BGExpiryDate:{
         required: function() {
           return ( $("#PerformanceBG").val()!="" || $("#BGIssueDate").val()!="" || $("#BankAmount").val()!="");
          }
        },
        BankAmount:{
          required: function() {
           return ( $("#PerformanceBG").val()!="" || $("#BGIssueDate").val()!="" || $("#BGExpiryDate").val()!="");
          }
        },
        // PerformanceBGUpload:{
        //   required: true,
        // },
        ContractDeposit:{
          required: function() {
           return ($("#ContractDepositAmount").val()!="" || $("#ContractDepositIssueDate").val()!="" || $("#ContractDepositExpiryDate").val()!="" );
          }
        },
        ContractDepositIssueDate:{
          required: function() {
           return ($("#ContractDeposit").val()!="" || $("#ContractDepositAmount").val()!="" || $("#ContractDepositExpiryDate").val()!="" );
          }
        },
        ContractDepositExpiryDate:{
          required: function() {
           return ($("#ContractDeposit").val()!="" || $("#ContractDepositIssueDate").val()!="" || $("#ContractDepositAmount").val()!="" );
          }
        },
        ContractDepositAmount:{
          required: function() {
           return ($("#ContractDeposit").val()!="" || $("#ContractDepositIssueDate").val()!="" || $("#ContractDepositExpiryDate").val()!="" );
          }
        },
        // BGContractDepositUpload:{
        //   required: true,
        // },
        DefectLiabilityPeriod:{
          required: true,
        },
        // ASDStartDate:{
        //   required: true,
        // },
        // ASDEndDate:{
        //   required: true,
        // },
        // ASDAmount:{
        //   required: true,
        // },

        ASDReceipt:{
          required: function() {
           return ($("#ASDStartDate").val()!="" || $("#ASDEndDate").val()!="" || $("#ASDAmount").val()!="");
          }
        },
        ASDStartDate:{
          required: function() {
           return ($("#ASDEndDate").val()!="" || $("#ASDAmount").val()!="");
          }
        },
        ASDEndDate:{
          required: function() {
           return ($("#ASDStartDate").val()!="" || $("#ASDAmount").val()!="" );
          }
        },
        ASDAmount:{
          required: function() {
           return ($("#ASDStartDate").val()!="" || $("#ASDStartDate").val()!="" );
          }
        },
        
        // ASDUpload:{
        //   required: function() {
        //    return ($("#ASDStartDate").val()!="" );
        //   }
        // },
        // BankAccNo:{
        //   required: true,
        // },
        // BankName:{
        //   required: true,
        // },
        // BankAmount:{
        //   required: true,
        // },
        // RetentionEndDate:{
        //   required: true,
        // },
        // RetentionAmount:{
        //   required: true,
        // },
        
        TenderStartDate:{
          required: true,
        },
        TenderEndDate:{
          required: true,
        },
        InsurancePolicy:{
          maxlength: 50,
          required: function() {
           return ($("#InsurancePolicyExpiryDate").val()!="" );
          }
        },
        InsurancePolicyExpiryDate:{
          required: function() {
           return ($("#InsurancePolicy").val()!="" );
          }
        },
        // Miscell:{
        //   required: true,
        // },
        address:{
          required: true,
        },
        // ExcludeMoonsoon:{
        //   required: true,
        // },

        // MiscellUpload_Div:{

        // }

        // MiscellUpload:{
        //   required: true,
        // },
        // EMDUpload:{
        //   required: true,
        // },
        // ASDUpload:{
        //   required: true,
        // },
        // RetentionUpload:{
        //   required: true,
        // },
        // InsurancePolicyUpload:{
        //   required: true,
        // },
       
        // UploadBOQ:{
        //   required: true,
        // }
      },

      messages: {
        TenderName:{
          required: "Please enter the tender name.",
        },
        WorkOrderNo:{
          required: "Please enter the work order no.",
        },
        SiteIncharge:{
          required: "Please choose the Site-Incharge.",
        },
        SiteSupervisor:{
          required: "Please choose the Site-Supervisor.",
        },
        BMCDepartment:{
          required: "Please  choose the department.",
        },
        InsurancePolicy:{
          required: "Please enter the insurance policy.",
        },
        EMDStartDate:{
          required: "Please select the  EMD start date.",
        },
        EMDEndDate:{
          required: "Please select the  EMD start date. ",
        },
        EMDAmount:{
          required: "Please enter the  EMD Amount.",
        },
        EMDUpload:{
          required: "Please upload the EMD document.",
        },
        ASDStartDate:{
          required: "Please select the  ASD start date.",
        },
        ASDEndDate:{
          required: "Please select the  ASD end date.",
        },
        ASDAmount:{
          required: "Please enter the  ASD Amount.",
        },
        ASDUpload:{
          required: "Please choose a file.",
        },
        BankAccNo:{
          required: "Please enter the bank account no.",
        },
        BankName:{
          required: "Please enter the bank name.",
        },
        BankAmount:{
          required: "Please enter the amount.",
        },
        RetentionEndDate:{
          required: "Please  select the  retention end date.",
        },
        RetentionAmount:{
          required: "Please enter the retention amount.",
        },
        RetentionUpload:{
          required: "Please choose a file.",
        },
        TenderStartDate:{
          required: "Please select the  tender start date.",
        },
        TenderEndDate:{
          required: "Please select the  tender end date.",
        },
        // ExcludeMoonsoon:{
        //   required: "Please Enter this Feild",
        // },
        address:{
          required: "Please enter the address.",
        },
        // UploadBOQ:{
        //   required: "Please choose a file.",
        // }
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
  data = {"function": 'Tender', "method":"AddTenderPage"};
  apiCall(data,successFn);


}

function EditTenderFromDashboard(id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  Tenders();
  setTimeout( function() {
    EditTender(id);
    $('#Btn_AddNew').addClass('d-none');

    setTimeout( function() {
    $('#Btn_AddNew').addClass('d-none');
    $('#Btn_CancelAddNew').addClass('d-none');
    $('#Btn_CancelDashboard').removeClass('d-none');
    }, 1000); 
  }, 500); 

    

}

function EditTender(id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  
  AddNewTender('update');

  successFn = function(resp)  {
      $('#AddTenderHead').html('Edit ');

      setTimeout( function() {
      $('[data-toggle="tooltip"]').tooltip();


        // $("#ModelTitle").html('Edit');
        $("#SubmitBtnText").html('Update ');
        $("#id").val(id);
        $("#TenderName").val(resp.data.Tender['TenderName']);
        $("#WorkOrderNo").val(resp.data.Tender['WorkOrderNo']);
        $("#SiteIncharge").val(resp.data.Tender['SiteIncharge']);
        // $("#SiteSupervisor").val(resp.data.Tender['SiteSupervisor']);
        $("#BMCDepartment").val(resp.data.Tender['BMCDepartment']);
        $("#EMDStartDate").val(resp.data.Tender['EMDStartDate']);
        $("#EMDEndDate").val(resp.data.Tender['EMDEndDate']);
        $("#EMDAmount").val(resp.data.Tender['EMDAmount']);
        $("#ASDStartDate").val(resp.data.Tender['ASDStartDate']);
        $("#ASDEndDate").val(resp.data.Tender['ASDEndDate']);
        if(resp.data.Tender['ASDAmount']==0 || resp.data.Tender['ASDAmount']=='' ){
          $("#ASDAmount").val('');
        }else{
          $("#ASDAmount").val(resp.data.Tender['ASDAmount']);
        }
        $("#BankAccNo").val(resp.data.Tender['BankAccNo']);
        $("#BankName").val(resp.data.Tender['BankName']);
        if(resp.data.Tender['BankAmount']==0 || resp.data.Tender['BankAmount']=='' ){
          $("#BankAmount").val('');
        }else{
          $("#BankAmount").val(resp.data.Tender['BankAmount']);
        }
        $("#RetentionEndDate").val(resp.data.Tender['RetentionEndDate']);
        $("#RetentionAmount").val(resp.data.Tender['RetentionAmount']);
        // alert(resp.data.Tender['TenderStartDate']);
        startDate= moment(resp.data.Tender['TenderStartDate']).format('YYYY-MM-DD');
        $("#TenderStartDate").val(startDate);
        endDate= moment(resp.data.Tender['TenderEndDate']).format('YYYY-MM-DD');
        $("#TenderEndDate").val(endDate);
        $("#address").val(resp.data.Tender['address']);

        $("#InsurancePolicy").val(resp.data.Tender['InsurancePolicy']);
        $("#InsurancePolicyExpiryDate").val(resp.data.Tender['InsurancePolicyExpiryDate']);
        $("#Miscell").val(resp.data.Tender['Miscell']);

        $("#ExcludeMoonsoon").val(resp.data.Tender['ExcludeMoonsoon']);



        $("#tender_description").val(resp.data.Tender['tender_description']);
        $("#WorkOrderText").val(resp.data.Tender['WorkOrderText']);        
        $("#InsuranceCAR").val(resp.data.Tender['InsuranceCAR']);
        $("#WorkOrderDate").val(resp.data.Tender['WorkOrderDate']);
        $("#InsuranceCARExpiryDate").val(resp.data.Tender['InsuranceCARExpiryDate']);
        $("#ASDReceipt").val(resp.data.Tender['ASDReceipt']);
        $("#PerformanceBG").val(resp.data.Tender['PerformanceBG']);
        $("#BGIssueDate").val(resp.data.Tender['BGIssueDate']);
        $("#BGExpiryDate").val(resp.data.Tender['BGExpiryDate']);
        $("#ContractDeposit").val(resp.data.Tender['ContractDeposit']);
        $("#ContractDepositIssueDate").val(resp.data.Tender['ContractDepositIssueDate']);
        $("#ContractDepositExpiryDate").val(resp.data.Tender['ContractDepositExpiryDate']);
        $("#ContractDepositAmount").val(resp.data.Tender['ContractDepositAmount']);
        $("#DefectLiabilityPeriod").val(resp.data.Tender['DefectLiabilityPeriod']);
        // $("#").val(resp.data.Tender['']);
      
        SiteSupervisor = (resp.data.Tender['SiteSupervisor']).split(",");  
        SiteEngineer = (resp.data.Tender['SiteEngineer']).split(",");  
        setTimeout( function() {
          for(i=0; i< SiteSupervisor.length; i++){
            $('#SiteSupervisor option[value='+SiteSupervisor[i]+']').attr("selected", "selected");
          }

          for(i=0; i< SiteEngineer.length; i++){
            $('#SiteEngineer option[value='+SiteEngineer[i]+']').attr("selected", "selected");
          }

          $("#SiteSupervisor").trigger('change');
          $("#SiteEngineer").trigger('change');

        }, 500);


        // url=resp.data.url+resp.data.Tender['MiscellUpload'];
        url=resp.data.url;


        if(resp.data.Tender['MiscellUpload']!=""){
          // url=resp.data.url+resp.data.Tender['MiscellUpload'];
           // $("#MiscellUpload").val(resp.data.url+resp.data.Tender['MiscellUpload']);
           fileName=resp.data.Tender['MiscellUpload'];
           CheckUploadedFile_('MiscellUpload',fileName, url+fileName);
        }

        if(resp.data.Tender['EMDUpload']!=""){
           fileName=resp.data.Tender['EMDUpload'];
           CheckUploadedFile_('EMDUpload',fileName, url+fileName);
        }

        if(resp.data.Tender['InsurancePolicyUpload']!=""){
           fileName=resp.data.Tender['InsurancePolicyUpload'];
           CheckUploadedFile_('InsurancePolicyUpload',fileName, url+fileName);
        }
        if(resp.data.Tender['InsuranceCARUpload']!=""){
           fileName=resp.data.Tender['InsuranceCARUpload'];
           // alert()
           CheckUploadedFile_('InsuranceCARUpload',fileName, url+fileName);
        }
        if(resp.data.Tender['ASDUpload']!=""){
           fileName=resp.data.Tender['ASDUpload'];
           CheckUploadedFile_('ASDUpload',fileName, url+fileName);
        }

        if(resp.data.Tender['RetentionUpload']!=""){
           fileName=resp.data.Tender['RetentionUpload'];
           CheckUploadedFile_('RetentionUpload',fileName, url+fileName);
        }

        if(resp.data.Tender['UploadBOQ']!=""){
           fileName=resp.data.Tender['UploadBOQ'];
           CheckUploadedFile_('UploadBOQ',fileName, url+fileName);
        }
        if(resp.data.Tender['MiscellUpload']!=""){
           fileName=resp.data.Tender['MiscellUpload'];
           CheckUploadedFile_('MiscellUpload',fileName, url+fileName);
        }
        if(resp.data.Tender['PerformanceBGUpload']!=""){
           fileName=resp.data.Tender['PerformanceBGUpload'];
           CheckUploadedFile_('PerformanceBGUpload',fileName, url+fileName);
        }
        if(resp.data.Tender['BGContractDepositUpload']!=""){
           fileName=resp.data.Tender['BGContractDepositUpload'];
           CheckUploadedFile_('BGContractDepositUpload',fileName, url+fileName);
        }

   
      }, 800); 
  }
  data = {"function": 'Tender', "method":"SelectTender_Id", "id":id};
  apiCall(data,successFn);

  

  
}

function AddNewTender_old(save='add'){

  successFn = function(resp)  {
    loadTenderTemplate('#addNewTender_templ',toMustacheDataObj(resp), 'BaseModal');
    // setTimeout(function () {
    //   if($('#MiscellUpload_Div').is(':visible')){
    //     alert();
    //   }
    // }, 100)
      
      

      $('#BaseModal').modal('show');
      $('#TenderForm').validate({
         ignore: [], //disable form auto submit on button click
        submitHandler: function(form) {

         

          var form = $("#TenderForm");
          var data = new FormData(form[0]);
          data.append('function', 'Tender');
          data.append('method', 'RegisterTender');
          data.append('save', save);

          successFn = function(resp)  {
            $('#AddNewSubmitBtn').addClass('d-none');
            $('#AddNewLoadBtn').removeClass('d-none');

            if(resp.status==0){
                Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: resp.data,
              });
            }else if(resp.status==1){
              // alert('login successfull');
              SwalRoundTick('Record Updated Successfully');
              
              $('#BaseModal').modal('hide');
              Tenders();
            }
          }
         
          apiCallForm(data,successFn);
        },



        rules: {
          TenderName:{
            required: true,
            maxlength: 50,
          },
          WorkOrderNo:{
            required: true,
          },
          SiteIncharge:{
            required: true,
            maxlength: 50,
          },
          SiteSupervisor:{
            required: true,
          },
          BMCDepartment:{
            required: true,
          },
          EMDStartDate:{
            required: true,
          },
          EMDEndDate:{
            required: true,
          },
          EMDAmount:{
            required: true,
          },


          
          ASDStartDate:{
            required: true,
          },
          ASDEndDate:{
            required: true,
          },
          ASDAmount:{
            required: true,
          },


          




          
          BankAccNo:{
            required: true,
          },
          BankName:{
            required: true,
          },
          BankAmount:{
            required: true,
          },
          RetentionEndDate:{
            required: true,
          },
          RetentionAmount:{
            required: true,
          },
          
          TenderStartDate:{
            required: true,
          },
          TenderEndDate:{
            required: true,
          },
          InsurancePolicy:{
            required: true,
          },
          InsurancePolicyExpiryDate:{
            required: true,
          },
          Miscell:{
            required: true,
          },

          address:{
            required: true,
          },
          // ExcludeMoonsoon:{
          //   required: true,
          // },

          // MiscellUpload_Div:{

          // }

          // MiscellUpload:{
          //   required: true,
          // },
          // EMDUpload:{
          //   required: true,
          // },
          // ASDUpload:{
          //   required: true,
          // },
          // RetentionUpload:{
          //   required: true,
          // },
          // InsurancePolicyUpload:{
          //   required: true,
          // },
         
          UploadBOQ:{
            required: true,
          }
        },

        messages: {
          TenderName:{
            required: "Please enter the tender name.",
          },
          WorkOrderNo:{
            required: "Please enter the work order no.",
          },
          SiteIncharge:{
            required: "Please choose the Site-Incharge.",
          },
          SiteSupervisor:{
            required: "Please choose the Site-Supervisor.",
          },
          BMCDepartment:{
            required: "Please  choose the department.",
          },
          InsurancePolicy:{
            required: "Please enter the insurance policy.",
          },
          EMDStartDate:{
            required: "Please select the  EMD start date.",
          },
          EMDEndDate:{
            required: "Please select the  EMD start date. ",
          },
          EMDAmount:{
            required: "Please enter the  EMD Amount.",
          },
          EMDUpload:{
            required: "Please upload the EMD document.",
          },
          ASDStartDate:{
            required: "Please select the  ASD start date.",
          },
          ASDEndDate:{
            required: "Please select the  ASD end date.",
          },
          ASDAmount:{
            required: "Please enter the  ASD Amount.",
          },
          ASDUpload:{
            required: "Please choose a file.",
          },
          BankAccNo:{
            required: "Please enter the bank account no.",
          },
          BankName:{
            required: "Please enter the bank name.",
          },
          BankAmount:{
            required: "Please enter the amount.",
          },
          RetentionEndDate:{
            required: "Please  select the  retention end date.",
          },
          RetentionAmount:{
            required: "Please enter the retention amount.",
          },
          RetentionUpload:{
            required: "Please choose a file.",
          },
          TenderStartDate:{
            required: "Please select the  tender start date.",
          },
          TenderEndDate:{
            required: "Please select the  tender end date.",
          },
          // ExcludeMoonsoon:{
          //   required: "Please Enter this Feild",
          // },
          address:{
            required: "Please enter the address.",
          },
          UploadBOQ:{
            required: "Please choose a file.",
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
  data = {"function": 'Tender', "method":"AddTenderPage"};
  apiCall(data,successFn);


}

function viewTender(id){
  // $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {

    resp.data['Tender']["ExcludeMoonsoon"]=parseInt(resp.data['Tender']["ExcludeMoonsoon"]);
    
// console.log( resp.data['Tender']);
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
    resp.data['Tender']['RetentionAmount'] = parseInt(resp.data['Tender']['RetentionAmount']).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });


    loadTenderTemplate('#viewTender_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

  }
  data = {"function": 'Tender', "method":"fetchTenderID","id":id};
  apiCall(data,successFn);
}


function CheckUploadedFile_(id, fileName='', url=''){

  if(fileName==''){
    fileName= $('#'+id).val().replace(/.*(\/|\\)/, '');
  }
  
  extension= fileName.split('.').pop();
// AddCostBills_Info
  $('#'+id+'_Div').addClass('d-none');

  if(extension=='jpeg' || extension=='jpg'  || extension=='bmp'  || extension=='png'|| extension=='gif' || extension=='JPG'|| extension=='JPEG' ){
    if(url!=''){

      $("#"+id+'_Label').append('<a href="javascript:void(0);" onclick="viewImage(`'+url+'`)"><img src="'+url+'" width="50px" height="50px"></a>');
    }else{
  // alert(id);
      
      $("#"+id+'_Label').append('<i class="fas fa-2x  fa-file-image text-info"></i>');
    }
  }else if(extension=='pdf'){
    $("#"+id+'_Label').append('<i class="fas fa-2x fa-file-pdf text-danger"></i>');
  }else if(extension=='doc' || extension=='docx' ){
    $("#"+id+'_Label').append('<i class="fas fa-2x fa-file-word text-info"></i>');
  }else if(extension=='xls' || extension=='xlsx' ){
    $("#"+id+'_Label').append('<i class="fas fa-2x fa-file-excel text-success"></i>');
  }else{
    $("#"+id+'_Label').append('<i class="fas fa-2x fa-question text-danger"></i>');

  }
  $("#"+id+'_Label').append(' &nbsp&nbsp <a href="javascript:void(0);" onclick="DeleteFile_(`'+id+'`)"><i class="fas  fa-trash text-danger"></i></a>');
  $("#"+id+'-error').empty();

  if(url!=''){

    $("#"+id+'_Label').append('&nbsp&nbsp<a href="'+url+'" target="_blank">View</a>');
  }

  $('#'+id).rules('remove', 'required');

}


function DeleteFile_(id){
  $('#'+id+'_Div').removeClass('d-none');
  $("#"+id+'_Label').empty();
  $('#'+id).val('');
  $('#'+id).rules('add', 'required');
}


function EditControlFileUploaded(id, fileName, url){
  // fileName= $('#'+id).val().replace(/.*(\/|\\)/, '');



 // $("#"+label_id).text(filename);
  // $('#'+Div_id).addClass('d-none');
  // extension= filename.split('.').pop();
  // // alert(extension);
  // if(extension=='jpeg' || extension=='jpg'  || extension=='bmp'  || extension=='png' ){
  //   $("#"+label_id).append('<i class="fas fa-2x  fa-file-image text-info"></i>');
  // }else if(extension=='pdf'){
  //   $("#"+label_id).append('<i class="fas fa-2x fa-file-pdf text-danger"></i>');
  // }else if(extension=='doc' || extension=='docx' ){
  //   $("#"+label_id).append('<i class="fas fa-2x fa-file-word text-info"></i>');
  // }else if(extension=='xls' || extension=='xlsx' ){
  //   $("#"+label_id).append('<i class="fas fa-2x fa-file-excel text-success"></i>');
  // }else{
  //   $("#"+label_id).append('<i class="fas fa-2x fa-question text-danger"></i>');

  // }
  // $("#"+label_id).append(' &nbsp&nbsp <a href="javascript:void(0);" onclick="DeleteFile_(`'+id+'`,`'+label_id+'`,`'+Div_id+'`)"><i class="fas  fa-trash text-danger"></i></a>');
  // $("#"+id+'-error').empty();
}

function PO_Active(id){

  $('[data-toggle="tooltip"]').tooltip('dispose'); 

  successFn = function(resp)  {
    for (var key in resp.data.BOQ) {
        resp.data.BOQ[key]["status"]=parseInt(resp.data.BOQ[key]["status"]);
        resp.data.BOQ[key]["purchase"]=parseInt(resp.data.BOQ[key]["purchase"]);
        resp.data.BOQ[key]["reject"]=parseInt(resp.data.BOQ[key]["reject"]);
        resp.data.BOQ[key]["received"]=parseInt(resp.data.BOQ[key]["received"]);

        resp.data.BOQ[key]["required_by"]= moment(resp.data.BOQ[key]["required_by"]).format('DD-MM-YYYY');
        
        if(resp.data.BOQ[key]["pdf_location"]==""){
          resp.data.BOQ[key]["pdf_Available"]=0;
        }else{
          resp.data.BOQ[key]["pdf_Available"]=1;
        }
    }
        resp.data.admin=parseInt(resp.data.admin);

    
    loadTenderTemplate('#PO_BOQ_templ2',toMustacheDataObj(resp), 'BaseModal');
    // $("#Dt_BOQ").DataTable();
    $("#Dt_BOQ").DataTable({'order':[], dom: 'Blfrtip',
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

    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();
    

  }
  data = {"function": 'Tender', "method":"Select_ACtive_PO","tender_id":id};
  apiCall(data,successFn);
}




function CheckTenderAmount(){
  if($('#TenderAmount').val()==''){
      $('#TenderAmount').focus();
  }
}

function CalculateLabourAmount() {
  TenderAmount = parseInt($('#TenderAmount').val());
  aboveBelow = $('#aboveBelow').val();
  TenderPrecentage = parseInt($('#TenderPrecentage').val());
  LabourWorkPre = parseInt($('#LabourWorkPre').val());

  quotedAmount =  0.00;
  LabourAmount = 0.00;

  if(TenderAmount != "" && aboveBelow != "" && TenderPrecentage != "" && TenderAmount > 0 && TenderPrecentage > 0) {
    PercentageAmount = parseFloat(TenderAmount * TenderPrecentage / 100);
    quotedAmount = (aboveBelow == 'Above') ? TenderAmount + PercentageAmount : TenderAmount - PercentageAmount;
  }

  $("#spn-quoted").html(quotedAmount.toFixed(2));
  $("#quotedAmount").val(quotedAmount.toFixed(2));

  if(quotedAmount > 0 && LabourWorkPre != ""  && LabourWorkPre > 0) {
    LabourAmount = quotedAmount * LabourWorkPre / 100;
  }

  $('#LabourAount').val(LabourAmount.toFixed(2));
  $("#spn-labour").html(LabourAmount.toFixed(2));

}



function AddLabourCost(id){
  
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {

    resp.data.TenderTag=parseInt(resp.data.TenderTag);
    resp.data.BillTag=parseInt(resp.data.BillTag);
    resp.data.labourAvailable=parseInt(resp.data.labourAvailable);
    resp.data.Below=parseInt(resp.data.Below);
    resp.data.admin=parseInt(resp.data.admin);
    

    // alert(resp.data.TenderCosting[0]['labour_amount']);
    // alert(resp.data.total_labour_amount);
    // ProgressBarPrecentage=parseInt(resp.data.total_labour_amount)/
    // alert(ProgressBarPrecentage);
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
    }

    


    loadTenderTemplate('#AddLabourCost_templ',toMustacheDataObj(resp), 'Dashboard');

    $('[data-toggle="tooltip"]').tooltip();


     $('#ProgressBar_LabourAmount').attr('data-percentage', ProgressBarPrecentage);
     $('.progress-bar').each(function () {

          // percentage=70;

          var t = $(this);
          var barPercentage = t.data('percentage');
          
          // add a div for the label text
          t.children('.label').append('<div class="label-text"></div>');

          // add some "gimme" percentage when data-percentage is <2
          if (parseInt((t.data('percentage')), 10) < 2) barPercentage = 2;

          // set up the left/right label flipping
          if (barPercentage > 50) {
              t.children('.label').css("right", (100 - barPercentage) + '%');
              t.children('.label').css("margin-right", "-10px");
          }
          if (barPercentage < 51) {
              t.children('.label').css("left", barPercentage + '%');
              t.children('.label').css("margin-left", "-20px");
          }

          // fill in bars and labels
          t.find('.label-text').text(t.attr('data-percentage') + ' Remaining');
          t.children('.bar').animate({
              width: barPercentage + '%'
          }, 500);
          t.children('.label').animate({
              opacity: 1
          }, 1000);
      });
    
  



       

  }
  data = {"function": 'Tender', "method":"AddLabourCosts","Tender_id":id};

  apiCall(data,successFn);


}


function CancelEdit(){
  $('#BaseModal').modal('hide');
}

function EditTender_Costing(id,tender_id,tender_amount,above_below,percentage,labour_work_percentage,labour_amount){
  loadTenderTemplate('#EditTenderCosting_templ',{}, 'BaseModal');
  $('#BaseModal').modal('show');

  $('#id').val(id);
  $('#tender_id').val(tender_id);
  $('#TenderAmount').val(tender_amount);
  $('#aboveBelow').val(above_below);
  $('#TenderPrecentage').val(percentage);
  $('#LabourWorkPre').val(labour_work_percentage);
  $('#LabourAmount').val(labour_amount);

  if(id!=""){
    $('#SubmitBtnText').html('Update');
  }

  CalculateLabourAmount();
  $('#TenderCostingForm').validate({
    ignore: [], //disable form auto submit on button click
    submitHandler: function(form) {
      titleText = "";
      btnText = "";
      if($('#SubmitBtnText').html() == "Update"){
          titleText = "Are you sure to update this record?";
          btnText = "Update";
          alertText='Record Updated Successfully';
      }else{
          titleText = "Are you sure to save this record?"
          btnText = "Save";
          alertText='Record Saved Successfully';

      }

      Swal.fire({
        title: titleText,
        text: "",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: btnText
      }).then((result) => {
        if (result.isConfirmed) {
          $('#SubmitButton').addClass('d-none');
          $('#LoadingBtn').removeClass('d-none');
          var form = $("#TenderCostingForm");
          var data = new FormData(form[0]);
          data.append('function', 'Tender');
          data.append('method', 'RegisterTenderCosting');

          successFn = function(resp)  {
            // SwalRoundTick(alertText);

            $('#BaseModal').modal('hide');
            // alert(tender_id);
            AddLabourCost(tender_id);
          }
          apiCallForm(data,successFn);

          Swal.fire(
            'Updated!',
              alertText,
            'success'
          );
          setTimeout(function () {
            Swal.close();
          }, 1000);
          
        }
      });
            
    },

    rules: {
      TenderAmount:{
        required: true,
      },
      aboveBelow:{
        required: true,
      },
      TenderPrecentage:{
        required: true,
      },
      LabourWorkPre:{
        required: true,
      },
      LabourAmount:{
        required: true,
      }
    },

    messages: {
      TenderAmount:{
        required: "Please enter the tender amount.",
      },
      aboveBelow:{
        required: "Please select above or below.",
      },
      TenderPrecentage:{
        required: "Please enter the tender precentage.",
      },
      LabourWorkPre:{
        required: "Please enter the labour work precentage.",
      },
      LabourAmount:{
        required: "Please enter the labour amount.",
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


// function EditTender_Costing2(id,tender_id,tender_amount,above_below,percentage,labour_work_percentage,labour_amount){
//   $('#TenderTagOld').addClass('d-none');
//   $('#TenderTagNew').removeClass('d-none');


//   $('#id').val(id);
//   $('#tender_id').val(tender_id);
//   $('#TenderAmount').val(tender_amount);
//   $('#aboveBelow').val(above_below);
//   $('#TenderPrecentage').val(percentage);
//   $('#LabourWorkPre').val(labour_work_percentage);
//   $('#LabourAmount').val(labour_amount);
//   $('#SubmitBtnText').html('Update');
    
//   $('[data-toggle="tooltip"]').tooltip();

// }

function UploadBillLabour(id){

  successFn = function(resp)  {

    $('[data-toggle="tooltip"]').tooltip('dispose'); 
    loadTenderTemplate('#uploadBill_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');
    $('[data-toggle="tooltip"]').tooltip();

    $('#tender_id').val(id);

    $('#UploadBillsForm').validate({
         ignore: [], //disable form auto submit on button click
        submitHandler: function(form) {
          $('#SubmitButton').addClass('d-none');
          $('#LoadingBtn').removeClass('d-none');
          var form = $("#UploadBillsForm");
          var data = new FormData(form[0]);
          data.append('function', 'Tender');
          data.append('method', 'AddLabourBill');
          data.append('id', id);
          // data.append('save', save);

          successFn = function(resp)  {
           
            SwalRoundTick('Record Updated Successfully');
            $('#BaseModal').modal('hide');
            // AddLabourCost(id);
            AdditionalLabourCostDetails();
          }
         
          apiCallForm(data,successFn);
        },


        rules: {
          BillName:{
            required: true,
          },
          BillNumber:{
            required: true,
          },
          BillAmount:{
            required: true,
          },
          BillDate:{
            required: true,
          },
          AddCostBills:{
            required: true,
          },
          BillVendor:{
            required: true,
          }
        },

        messages: {
          BillNumber:{
            required: "Please enter the  bill number.",
          },
          BillName:{
            required: "Please enter the  bill description.",
          },
          BillAmount:{
            required: "Please enter the amount. ",
          },
          AddCostBills:{
            required: "Please attach the bill.",
          },
          BillDate:{
            required: "Please select the date.",
          },
          BillVendor:{
            required: "please select a vendor.",
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
  data = {"function": 'Vendors', "method": "VendorList"};
  apiCall(data,successFn);
      


}

function DeleteTenderBill(id,tender_id){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 

  Swal.fire({
    title: 'Are you sure to delete this record?',
    text: "",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Delete!'
  }).then((result) => {
    if (result.isConfirmed) {
      successFn = function(resp)  {
        setTimeout(function () {
          Swal.close();

        }, 1000)
        $('[data-toggle="tooltip"]').tooltip();

        // AddLabourCost(tender_id);
        AdditionalLabourCostDetails();
        
      }
      data = {"function": 'Tender', "method": "DeleteBill","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your recored is deleted.',
        'success'
      );
      
    }
  });

}

function ShowRequest(){
  successFn = function(resp)  {

    resp.data.admin=parseInt(resp.data.admin);

    loadTenderTemplate('#AllRequestedPO_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#Dt_AllPOs").DataTable({});
  }
  data = {"function": 'Tender', "method": "FetchAllRequestedPO"};
  apiCall(data,successFn);
}

function ViewAllTenderPO(){
  alert('here');
}


