// var jtemplates;
// $( document ).ready(function() {
//   $.get('template/jtemplates.htm', function(templates) {
//       jtemplates = templates;
//       dashboard('nav-dashboard');
//   });
// });

function loadTemplate(templateId, data, id="Dashboard"){
  var template = $(jtemplates).filter(templateId).html();
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

function dashboard(nav=''){
  MakeNavActive(nav);

  successFn = function(resp)  {
    for (var key in resp.data.TenderList) {


        if(resp.data.TenderList[key]["PO_total"]==null){
          resp.data.TenderList[key]["PO_null"]=1;
        }else{
          resp.data.TenderList[key]["PO_null"]=0;
        }
        if(resp.data.TenderList[key]["labour_amount_spend"]==null){
          resp.data.TenderList[key]["labour_amount_spend_null"]=1;
        }else{
          resp.data.TenderList[key]["labour_amount_spend_null"]=0;
        }
        if(resp.data.TenderList[key]["misc_amount"]==null || resp.data.TenderList[key]["misc_amount"]==0){
          resp.data.TenderList[key]["misc_amount_null"]=1;
        }else{
          resp.data.TenderList[key]["misc_amount_null"]=0;
        }





        resp.data.TenderList[key]["orderRequest"]=parseInt(resp.data.TenderList[key]["orderRequest"]);
        resp.data.TenderList[key]["UnaprovedOrderRequest"]=parseInt(resp.data.TenderList[key]["UnaprovedOrderRequest"]);
        
        resp.data.TenderList[key]["DateCheck"]=parseInt(resp.data.TenderList[key]["DateCheck"]);

        resp.data.TenderList[key]["TotalAmout"]=parseInt(resp.data.TenderList[key]["TotalAmout"]).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        resp.data.TenderList[key]["PO_total"]=parseInt(resp.data.TenderList[key]["PO_total"]).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
        // resp.data.TenderList[key]["labour_amount"]=parseInt(resp.data.TenderList[key]["labour_amount"]).toLocaleString('en-IN', {
        //     maximumFractionDigits: 2,
        //     style: 'currency',
        //     currency: 'INR'
        // });
       
        // resp.data.TenderList[key]["misc_amount"]= resp.data.TenderList[key]["labour_amount_spend"] / resp.data.TenderList[key]["misc_amount"]*100;
        resp.data.TenderList[key]["misc_amount_percentage"]= (resp.data.TenderList[key]["labour_amount_spend"] / resp.data.TenderList[key]["misc_amount"]).toFixed(2);


        percentageCalc=resp.data.TenderList[key]["tender_amount"] * resp.data.TenderList[key]["percentage"]/100;
        if(resp.data.TenderList[key]["above_below"]=='Above'){
          QuotedAmt=resp.data.TenderList[key]["tender_amount"]+percentageCalc;
        }else{
          QuotedAmt=resp.data.TenderList[key]["tender_amount"]-percentageCalc;
        }
        if(QuotedAmt!=0 ){


          resp.data.TenderList[key]["SpendPercentage"]=(resp.data.TenderList[key]["labour_amount_spend"]*100/QuotedAmt).toFixed(2);

          resp.data.TenderList[key]["labour_amount_spend"]=parseInt(resp.data.TenderList[key]["labour_amount_spend"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
          });
          resp.data.TenderList[key]["misc_amount"]=parseInt(resp.data.TenderList[key]["misc_amount"]).toLocaleString('en-IN', {
              maximumFractionDigits: 2,
              style: 'currency',
              currency: 'INR'
          });

        }

        resp.data.TenderList[key]["TenderEndDate"]= moment(resp.data.TenderList[key]["TenderEndDate"]).format('DD-MM-YYYY');



       

    }
// alert();
    resp.data.TOTALEMD = parseInt(resp.data.TOTALEMD).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });
    resp.data.TOTALASD = parseInt(resp.data.TOTALASD).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });

    resp.data.TOTALBG = parseInt(resp.data.TOTALBG).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            style: 'currency',
            currency: 'INR'
        });


    resp.data.CheckZeroEMDAmount= parseInt(resp.data.CheckZeroEMDAmount);
    resp.data.CheckZeroASDAmount= parseInt(resp.data.CheckZeroASDAmount);
    resp.data.CheckZeroBankAmount= parseInt(resp.data.CheckZeroBankAmount);
    
    resp.data.UnAproved=1;

    if(resp.data.TOTALREQUEST_UnAproved>0){
        resp.data.UnAproved=1;
    }
    loadTemplate('#dashboard_templ',toMustacheDataObj(resp), 'Dashboard');
    // BOQs(35); //Delete this
    // Tenders();//Delete this

    // Purchase_OrderedRequest(25);
    // AddLabourCost(5);
    // BOQs(5);
    // AddNewTender();//Delete this
    // EditTender(5);//Delete this
    // viewTender('5');

      // Order_BOQ(1,5);
    $('[data-toggle="tooltip"]').tooltip();


  }
  data = {"function": 'Employees', "method":"dashboard"};
  apiCall(data,successFn);

}




function logout(){
  Swal.fire({
    title: 'Are you sure to check out?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Logout'
  }).then((result) => {
    if (result.isConfirmed) {
        setTimeout(function () {
          window.location.replace("logout.php");
          Swal.close();
        }, 300)

      // Swal.fire(
      //   'Logged out!',
      //   'Your are sucessfully Logged out',
      //   'success'
      // );
      
    }
  });
}

function MakeNavActive(nav=''){
  if(nav!=''){
    $(".nav-active-control").removeClass('active');
    $("#"+nav).addClass('active');
  }
    
}



function employees(nav=''){
  MakeNavActive(nav);
  
  successFn = function(resp)  {
    loadTemplate('#employee_templ',toMustacheDataObj(resp), 'Dashboard');
    $("#Dt_Employee").DataTable({});
  }
  data = {"function": 'Employees', "method":"EmployeeList"};
  apiCall(data,successFn);
}


function EditUser(id,first_name,last_name,user_name,email,mobile,role){
  AddNewEmployee('update');
  setTimeout( function() {
    $("#ModelTitle").html('Edit');
    $("#SubmitBtnText").html('Update ');
    $("#id").val(id);
    $("#first_name").val(first_name);
    $("#last_name").val(last_name);
    $("#user_name").val(user_name);
    $("#email").val(email);
    $("#mobile").val(mobile);
    $("#role").val(role);
  },1000); 
}


function AddNewEmployee(save='add'){
  successFn = function(resp)  {
    // loadTemplate('#Register_tmpl',toMustacheDataObj(resp), 'BaseModal');
    loadTemplate('#addNewEmployee_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

    $('#EmployeeForm').validate({
      submitHandler: function(form) {
        $('#SubmitButton').addClass('d-none');
        $('#LoadingBtn').removeClass('d-none');
        var form = $("#EmployeeForm");
        var data = new FormData(form[0]);
        data.append('function', 'Employees');
        data.append('method', 'Register');
        data.append('save', save);

        successFn = function(resp)  {
          if(resp.status==0){
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: resp.data,
            });
          }else if(resp.status==1){
            employees();
            SwalRoundTick('Record Updated Successfully');
            $('#BaseModal').modal('hide');
          }
        }
       
        apiCallForm(data,successFn);
      },
      rules: {
        first_name:{
          required: true,
        },
        last_name:{
          required: true,
        },
        user_name:{
          required: true,
        },
        email:{
          required: true,
          email: true,
        },
        password:{
          required: true,
        },
        password2:{
          required: true,
          equalTo: "#password"
        },
        mobile:{
          required: true,
        },
        role:{
          required: true,
        }

      },
      messages: {
        first_name: {
          required: "Please enter the first name",
          remote: "User Name already in use!"
        },
        last_name: {
          required: "Please enter the last name"
        },
        user_name: {
          required: "Please enter the user name"
        },
        email: {
          required: "Please enter the email"
        },
        password: {
          required: "Please enter the password"
        },
        password2: {
          required: "Please enter the same password"
        },
        mobile: {
          required: "Please enter the mobie number"
        },
        role: {
          required: "Please select a role"
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
  data = {"function": 'Employees', "method":"Role"};
  apiCall(data,successFn);
    

}



function  DeleteUser(id){

  
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
          employees();
          setTimeout(function () {
            Swal.close();
          }, 1000);
        }
      }
      data = {"function": 'Employees', "method": "DeleteUser","id":id };
      apiCall(data,successFn);

      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      );
      
    }
  });

}

 


// function CheckEmail(email){

  
// }


function CheckUserName(user_name){
  $('#UserNameSpan').remove();

  if(user_name !=""){

    successFn = function(resp)  {

      if(resp.data==0){
        // $('#UserName').addClass('is-valid');
        $('#user_name').removeClass('is-invalid');
      }else{
        $('#user_name').addClass('is-invalid');
        $('#user_name').closest('.form-group').append('<span id="UserNameSpan" class="error invalid-feedback">User name unavailabele.</span>');
      
      }
    }
    data = {"function": 'Employees', "method": "CheckUserName","user_name":user_name };
    apiCall(data,successFn);
  }
}

 
//select all Checkbox 
function SACB(Checkbox) {
  $("#"+Checkbox+"_SACB").click(function () {
    $("."+Checkbox+"_CB").attr('checked', this.checked);
  });
}

//select one Checkbox
function SOCB(ClassName){
  $('.'+ ClassName).on('change', function() {
    $('.'+ ClassName).not(this).prop('checked', false);
  });
}

function PrintContent(element) {
  $("#ProjectTitle").html('TenderDetails');
  $("#"+element).printElement();
  $("#ProjectTitle").html('Devengineers - Tender');
}



function DownloadPDF(element) {
  var doc = new jsPDF();
  var specialElementHandlers = {
      '#editor': function (element, renderer) {
          return true;
      }
  };

  doc.fromHTML($('#'+element).html(), 10, 10, {
      'width': 100,
          'elementHandlers': specialElementHandlers
  });
  doc.save('sample-file.pdf');
}

// window.jsPDF = window.jspdf.jsPDF;
function PO_All_Requested_Tender(id){
  PO_All_Requested(id);
  setTimeout(function () {
    $('#dashboardbackbtn').addClass('d-none');
    $('#tenderbackbtn').removeClass('d-none');
  }, 1000);
}


function PO_All_Requested(id){
  successFn = function(resp)  {
    if(resp.status==0){
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: resp.data,
      });
    }else if(resp.status==1){
      for (var key in resp.data.BOQ) {
          resp.data.BOQ[key]["confirm"]=parseInt(resp.data.BOQ[key]["confirm"]);
          resp.data.BOQ[key]["rejected"]=parseInt(resp.data.BOQ[key]["rejected"]);
          resp.data.BOQ[key]["required_by"]= moment(resp.data.BOQ[key]["required_by"]).format('DD-MM-YYYY');
          resp.data.BOQ[key]["created_date"]= moment(resp.data.BOQ[key]["created_date"]).format('DD-MM-YYYY');
      }
      resp.data.admin=parseInt(resp.data.admin);

      loadTemplate('#Dass_PO_BOQ_templ',toMustacheDataObj(resp), 'Dashboard');
      $("#Dt_DashBOQ").DataTable({});
    }
    
    
  }
  data = {"function": 'BOQ', "method":"All_Requested_POs","tender_id":id};
  apiCall(data,successFn);
}


function viewImage(src){
   $('#imagepreview').attr('src', src); // here asign the image to the modal when the user click the enlarge link
   $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
   $('#DownloadImage').click(function(e) {
    a = document.createElement('a');
    a.href = src;
    a.download = src;
    a.click();
  });

  // setTimeout(function () {
  //   resize_image(src, 'imagepreview');
  // }, 1000);

}

function resize_image(file, img_id) {

  var width = window.innerHeight;
  var img = document.getElementById(img_id);
  iw=img.width;
  ih=img.height;
  ww=window.innerWidth;
  wh=window.innerHeight;
  if(ih>=wh){
    per=(ih-wh)/ih*100;
    nw=iw-(iw*per/100);
    $("#"+img_id).css("height", wh-200);
    $("#"+img_id).css("width", nw);
  }
  if(ih>=wh){
    $("#"+img_id).css("max-height", '450px');
  }
  if(iw>=ww){
    $("#"+img_id).css("max-width", '550px');
  }


}

function viewImageNew(src, loc='',ImageId='', table123=''){

   $('#imagepreviewNew').attr('src', src); // here asign the image to the modal when the user click the enlarge link
   $('#imagemodalNew').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
   $('#Hid_imageSrc').val(src); // here asign the image to the modal when the user click the enlarge link
   $('#Hid_ImageId').val(ImageId); // here asign the image to the modal when the user click the enlarge link
   $('#Hid_table').val(table123); // here asign the image to the modal when the user click the enlarge link


  $('#DownloadImageNew').click(function(e) {
    a = document.createElement('a');
    a.href = src;
    a.download = table123+'_'+ImageId;
    a.click();
    // checkClick(1);
  });
  $("#rotateAngle2").val(0);
   // rotateImage(0, loc);
  // setTimeout(function () {
  //   resize_image(src, 'imagepreviewNew');
  // }, 1000);
}

function  checkClick(){
  a.click();
}

function ClosePopover(){
  $(".popover").remove(); 
}


function rotateImage(degree2,loc='') {
  // $('#degreeBtn').addClass('d-none');
  // $('#LoadingBtn').removeClass('d-none');

  // Deg=$("#rotateAngle2").val();
  // degree=parseInt(Deg) + parseInt(degree2);
  degree=parseInt(degree2);

  // if(degree==360)degree=0;
  $('#imagepreviewNew').animate({
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
  if(loc=='' || $('#Hid_ImageId').val()!=''){
    successFn = function(resp)  {
      if(resp.status==1){
        $('#LoadNewImage').empty().append('<img src="'+resp.data+"?t=" + new Date().getTime()+'" id="imagepreviewNew" style="max-width: 700px; max-height: 600px" >');
      }
    }
    //Works Locally
    // data = {"function": 'TenderDetails', "method":"RotateImage","degree":degree,"src": $('#Hid_imageSrc').val()};

    data = {"function": 'TenderDetails', "method":"RotateImage","degree":degree,"Image_Id": $('#Hid_ImageId').val(),"table":$('#Hid_table').val()};
    apiCall(data,successFn);
  }

}


// function rotate(image) {
//   rotateAngle=90;

//   // .setAttribute("data", link);


//   image.setAttribute("transform", "rotate(" + rotateAngle + "deg)");
//   // rotateAngle = rotateAngle + 90;
// }