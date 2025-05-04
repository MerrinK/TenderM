// var vendors;
// $( document ).ready(function() {
//   $.get('template/vendors.htm', function(templates) {
//       vendors = templates;
//       Vendors('nav-vendors');
//   });
// });

function loadVendorsTemplate(templateId, data, id="Dashboard"){
  var template = $(vendors).filter(templateId).html();
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




function Vendors(nav=''){
  MakeNavActive(nav);
  
  successFn = function(resp)  {
    resp.data.admin=parseInt(resp.data.admin);
    for (var key in resp.data.vendor) {
      resp.data.Vendor[key]["EditPermit"]=parseInt(resp.data.Vendor[key]["EditPermit"]);
    }


    loadVendorsTemplate('#vendor_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#Dt_Vendor").DataTable({});
    
  }
  data = {"function": 'Vendors', "method":"VendorList"};
  apiCall(data,successFn);
}


function DeleteVendor(id){


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
        if(resp.status==0){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: resp.data,
          });
        }else if(resp.status==1){
          Vendors();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'Vendors', "method": "DeleteVendor","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });





}

function EditVendor(id,company_name,name,email,gst_no,mobile,pan_card,aadhaar_card,location){
  AddNewVendor('update');
  
    setTimeout( function() {
      $("#ModelTitle").html('Edit');
      $("#SubmitBtnText").html('Update');
      $("#id").val(id);
      $("#company_name").val(company_name);
      $("#name").val(name);
      $("#email").val(email);
      $("#gst_no").val(gst_no);
      $("#mobile").val(mobile);
      $("#pan_card").val(pan_card);
      $("#aadhaar_card").val(aadhaar_card);

      locations=location.split(",");
      for(i=0; i< locations.length; i++){
        $('#location option[value='+locations[i]+']').attr("selected", "selected");
      }

      $("#location").trigger('change');


    }, 500); 

  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      $("#address").val(resp.data.Vendor['address']);
    }
  }
  data = {"function": 'Vendors', "method":"SelectVendorOfId", "id":id};
  apiCall(data,successFn);

  
}

function AddNewVendor(save='add'){
  successFn = function(resp)  {

    loadVendorsTemplate('#addNewVendor_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');



    $('#VendorForm').validate({
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#VendorForm");
        var data = new FormData(form[0]);
        data.append('function', 'Vendors');
        data.append('method', 'RegisterVendor');
        data.append('save', save);
        data.append('location_Multiple',$('#location').val());


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
            Vendors();
          }
        }
       
        apiCallForm(data,successFn);
      },

      rules: {
        company_name:{
          required: true,
        },
        email:{
          // required: true,
          email: true,
        },
        name:{
          required: true,
        },
        gst_no:{
          // required: true,
          required: function() {
           return ( $("#pan_card").val()=="" || $("#aadhaar_card").val()=="" );
          }
        },
        mobile:{
          required: true,
          maxlength: 10,
          minlength: 10,
        },
        address:{
          required: true,
        },
        pan_card:{
          // required: true,
          required: function() {
           return ( $("#gst_no").val()=="" );
          }

        },
        aadhaar_card:{
          // required: true,
          required: function() {
           return ( $("#gst_no").val()=="" );
          }

        }
        


      },

      messages: {
        company_name: {
          required: "Please enter the company name.",
        },
        email: {
          required: "Please enter the email."
        },
        name: {
          required: "Please enter the name."
        },
        gst_no: {
          required: "Please enter the GST No."
        },
        mobile: {
          required: "Please enter the mobie number."
        },
        address: {
          required: "Please enter the address."
        },
        pan_card:{
          required: "Please enter the PAN card number."
        },
        aadhaar_card:{
          required: "Please enter the Aadhaar Card No."
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
  

    $('.js-example-basic-single').select2();
    $('.select2-container--default').css('width','-webkit-fill-available');

  }
  data = {"function": 'Vendors', "method":"GetLocations"};
  apiCall(data,successFn);

}

