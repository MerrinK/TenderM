
function loadInventoryTemplate(templateId, data, id="Dashboard"){
  var template = $(Inventory).filter(templateId).html();
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


function InventoryTab(nav=''){
  MakeNavActive(nav);
  loadInventoryTemplate('#InventoryTab_templ',{}, 'Dashboard');

  $("#InvDocuments-tab").click(function() {
    InvDocuments();
  });
  

  $("#InvDocuments-tab").click();
}


function InvDocuments(){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {
    loadInventoryTemplate('#Documents',toMustacheDataObj(resp), 'InvDocuments');
    $("#Dt_InvDocs").DataTable();
  }
  data = {"function": 'Inventory', "method":"Documents"};
  apiCall(data,successFn);

}

function addNewInventoryDocument(save='add',id=''){
  $('[data-toggle="tooltip"]').tooltip('dispose'); 
  successFn = function(resp)  {

    loadInventoryTemplate('#AddNewDocuments_tmpl',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

    $('#InventoryDocumentForm').validate({
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#InventoryDocumentForm");
        var data = new FormData(form[0]);
        data.append('function', 'Inventory');
        data.append('method', 'RegisterInventoryDocument');
        data.append('save', save);
        data.append('id', id);

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
            InventoryTab();
          }
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
       
        apiCallForm(data,successFn);
      },


      rules: {
        doc_id:{
          required: true,
        },
        doc_type:{
          required: true,
        },
        doc_name:{
          required: true,
        },
        doc_tender_id:{
          required: true,
        },
        doc_count:{
          required: true,
        },
        doc_value:{
          required: true,
        },
        doc_insurance_expiry_date:{
          required: true,
        },
        doc_description:{
          required: true,
        },
      },

      messages: {
        doc_id: {
          required: "Please enter the company name.",
        },
        doc_type: {
          required: "Please enter the company name.",
        },
        doc_name: {
          required: "Please enter the company name.",
        },
        doc_tender_id: {
          required: "Please enter the company name.",
        },
        doc_count: {
          required: "Please enter the company name.",
        },
        doc_value: {
          required: "Please enter the company name.",
        },
        doc_insurance_expiry_date: {
          required: "Please enter the company name.",
        },
        doc_description: {
          required: "Please enter the company name.",
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
  data = {"function": 'Inventory', "method":"AddNew_TypeLoc"};
  apiCall(data,successFn);
  
}


function Edit_Inv_Doc(id){
  addNewInventoryDocument('update',id);

  successFn = function(resp)  {
    $("#ModelTitle").html('Edit');
    $("#SubmitBtnText").html('Update');
    setTimeout( function() {
      $("#doc_id").val(resp.data.Inventory['doc_id']);
      $("#doc_type").val(resp.data.Inventory['type']).trigger('change');
      $("#doc_name").val(resp.data.Inventory['name']);
      $("#doc_tender_id").val(resp.data.Inventory['tender_id']);
      $("#doc_count").val(resp.data.Inventory['count']);
      $("#doc_value").val(resp.data.Inventory['value']);
      $("#doc_description").val(resp.data.Inventory['description']);
      insurance=moment(resp.data.Inventory['insurance_expiry_date']).format('YYYY-MM-DD');
      $("#doc_insurance_expiry_date").val(insurance);
      fitness= moment(resp.data.Inventory['fitness_expiry_date']).format('YYYY-MM-DD');
      $("#doc_fitness_expiry_date").val(fitness);


    }, 600); 
  }
  data = {"function": 'Inventory', "method":"SelectInvDoc",'id':id};
  apiCall(data,successFn);


  
}

function OptionForVehicle(id){
  if(id==1){
    $('#ForVehicle').removeClass('d-none')
  }else{
    $('#ForVehicle').addClass('d-none')
  }
} 

function Delete_Inv_Doc(id){
  successFn = function(resp) {
   if(resp.status==0){
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: resp.data,
        });
    }else if(resp.status==1){
      SwalRoundTick(resp.data);
      $('#BaseModal').modal('hide');
      InvDocuments();
    }
    setTimeout(function () {
      Swal.close();
    }, 1000);
  
  }
  data = {"function": 'Inventory', "method":"DeleteInvDoc",'id':id};
  apiCall(data,successFn);
} 