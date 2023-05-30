// var jtemplates;
// $( document ).ready(function() {
//   $.get('template/jtemplates.htm', function(templates) {
//       jtemplates = templates;
//       dashboard();
//       employees();
//   });
// });

// function loadTemplate(templateId, data, id="Dashboard"){
//   var template = $(jtemplates).filter(templateId).html();
//   var rendered = Mustache.render(template, data);
//   document.getElementById(id).innerHTML = rendered;
//   // $('#'+id).html(rendered); //will Work Try

// }


// function toMustacheDataObj(obj){
//   obj.resetIndex = function() {
//     window['INDEX']=0;
//   }
//   obj.index = function() {
//     return ++window['INDEX']||(window['INDEX']=0);
//   }
//   obj.showBorder= function(){
//     return ((window['INDEX'])%2 == 0) ? true: false;
//   }
//   return obj;
// }




// function dashboard(){
//   successFn = function(resp)  {
//     loadTemplate('#dashboard_templ',toMustacheDataObj(resp), 'Dashboard');

//   }
//   data = {"function": 'Employees', "method":"dashboard"};
//   apiCall(data,successFn);

// }

// function employees(){
//   successFn = function(resp)  {
//     loadTemplate('#employee_templ',toMustacheDataObj(resp), 'Dashboard');
//     $("#Dt_Employee").DataTable({});
//   }
//   data = {"function": 'Employees', "method":"EmployeeList"};
//   apiCall(data,successFn);
// }



// function EditUser(id,first_name,last_name,user_name,email,mobile,role){
//   AddNewEmployee('update');
//   setTimeout( function() {
//     $("#id").val(id);
//     $("#first_name").val(first_name);
//     $("#last_name").val(last_name);
//     $("#user_name").val(user_name);
//     $("#email").val(email);
//     $("#mobile").val(mobile);
//     $("#role").val(role);
//   }, 1000); 
// }



// function AddNewEmployee(save='add'){
//   successFn = function(resp)  {
//     // loadTemplate('#Register_tmpl',toMustacheDataObj(resp), 'BaseModal');
//     loadTemplate('#addNewEmployee_templ',toMustacheDataObj(resp), 'BaseModal');
//     $('#BaseModal').modal('show');

//     $('#EmployeeForm').validate({
//       submitHandler: function(form) {
//         var form = $("#EmployeeForm");
//         var data = new FormData(form[0]);
//         data.append('function', 'Employees');
//         data.append('method', 'Register');
//         data.append('save', save);

//         successFn = function(resp)  {
//           if(resp.status==0){
//             // alert('user not defined');
//           }else if(resp.status==1){
//             // alert('login successfull');
//             $('#BaseModal').modal('hide');
//             location.reload();
//           }
//         }
       
//         apiCallForm(data,successFn);
//       },
//       rules: {
//         first_name:{
//           required: true,
//         },
//         last_name:{
//           required: true,
//         },
//         user_name:{
//           required: true,
//         },
//         email:{
//           required: true,
//           email: true,
//         },
//         password:{
//           required: true,
//         },
//         password2:{
//           required: true,
//           equalTo: "#password"
//         },
//         mobile:{
//           required: true,
//         },
//         role:{
//           required: true,
//         }

//       },
//       messages: {
//         first_name: {
//           required: "Please enter the first name",
//           remote: "User Name already in use!"
//         },
//         last_name: {
//           required: "Please enter the last name"
//         },
//         user_name: {
//           required: "Please enter the user name"
//         },
//         email: {
//           required: "Please enter the email"
//         },
//         password: {
//           required: "Please enter the password"
//         },
//         password2: {
//           required: "Please enter the same password"
//         },
//         mobile: {
//           required: "Please enter the mobie number"
//         },
//         role: {
//           required: "Please select a role"
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
//    }
//   data = {"function": 'Employees', "method":"Role"};
//   apiCall(data,successFn);
    

// }



// function DeleteUser(id){
//     successFn = function(resp)  {
//       employees();
//     }
//     data = {"function": 'Employees', "method": "DeleteUser","id":id };
//     apiCall(data,successFn);

// }


// function CheckEmail(email){

//   // $('#UserNameSpan').remove();

//   // if(email !=""){

//   //   successFn = function(resp)  {

//   //     if(resp.data==0){
//   //       // $('#UserName').addClass('is-valid');
//   //       $('#UserName').removeClass('is-invalid');
//   //     }else{
//   //       $('#UserName').addClass('is-invalid');
//   //       $('#UserName').closest('.form-group').append('<span id="UserNameSpan" class="error invalid-feedback">UserName Unavailabele</span>');
      
//   //     }
//   //   }
//   //   data = {"Class": 'UserClass', "method": "CheckEmail","email":email };
//   //   apiCall(data,successFn);
//   // }
  
// }



 
// //select all Checkbox 
// function SACB(Checkbox) {
//   $("#"+Checkbox+"_SACB").click(function () {
//     $("."+Checkbox+"_CB").attr('checked', this.checked);
//   });
// }
// // function SACB(Checkbox) {

// //   if($('#'+Checkbox+"_SACB").is(":checked")){
// //     $('.'+Checkbox+"_CB").prop('checked',true);
// //   }else{
// //     $('.'+Checkbox+"_CB").prop('checked',false);
// //   }

// // }

// //select one Checkbox
// function SOCB(ClassName){
//   $('.'+ ClassName).on('change', function() {
//     $('.'+ ClassName).not(this).prop('checked', false);
//   });
// }

