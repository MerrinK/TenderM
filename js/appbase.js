var table=null;

function apiCall(data,successFn,errorFn,sync=false){
  $.ajax({
    url: "ajaxHandler.php",
    type:"POST",
    data : data,
    async: !sync,
    success: function(result){
      try {
          result = JSON.parse(result);
      } catch(e) {
          // alert(e); // error in the above string (in this case, yes)!
          Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: e,
          });
      }
      // result=JSON.parse(result);
      if ( typeof successFn != "undefined" ) successFn(result); 
    },
    error: function(result) {
      if ( typeof successFn != "undefined" ) errorFn(result); 
    }
  });
}


// function apiCall(data,successFn,errorFn,sync=false){
//   $.ajax({
//     url: "ajaxHandler.php?",
//     type:"POST",
//     data : data,
//     async: !sync,
//     success: function(result){
//       // console.log(result);
//         try {
//           result = JSON.parse(result);
//         } catch(e) {
//           // alert(e); // error in the above string (in this case, yes)!
//           Swal.fire({
//               icon: 'error',
//               title: 'Oops...',
//               text: e,
//           });
//         }
//       // result=JSON.parse(result);
//       if ( typeof successFn != "undefined" ) successFn(result); 
//     },
//     error: function(result) {
//       if ( typeof successFn != "undefined" ) errorFn(result); 
//     }
//   });
// }

function apiCallForm(data,successFn,errorFn,sync=false){
  $.ajax({
    url: "ajaxHandler.php?",
    type:"POST",
    data : data,
    async: !sync,
    contentType:false,
    processData:false,
    success: function(result){
      result=JSON.parse(result);
      if ( typeof successFn != "undefined" ) successFn(result); 
    },
    error: function(result) {
      if ( typeof successFn != "undefined" ) errorFn(result); 
    }
  });
}


function printElement(element) {
    $("#"+element).printElement();
}

jQuery.fn.extend({
    printElement: function() {
        var cloned = this.clone();
        var printSection = $('#printSection');
        if (printSection.length == 0) {
            printSection = $('<div id="printSection"></div>')
            $('body').append(printSection);
        }
        printSection.append(cloned);
        var toggleBody = $('body *:visible');
        toggleBody.hide();
        $('#printSection, #printSection *').show();
        window.print();
        printSection.remove();
        toggleBody.show();
    }
});






function clearTable(tblId)
{
  $("#"+tblId).html("");
}
// function clearDiv(divId)
// {  
//  $("#"+divId).empty();
// }


function createTableHeader(tblId,cols,conds)
{
  var str="<thead><tr>";
  var i;
  //alert(cols.length);
  for (i = 0; i < cols.length; i++) {
    var cls="";
    if ( typeof conds != "undefined" )  {
      cls = conds[i];
    } 
    str += "<th "+cls+">"+cols[i] + "</th>";
  }
  str+="</tr></thead>";
  $("#"+tblId).append(str);
}

function createTableRow(tblId,cols,conds)
{
  var str="<tr>";
  var i;
  //alert(cols.length);
  for (i = 0; i < cols.length; i++) {
    var v = cols[i];
    if ( v == null ) v="";
    var cls="";
    if ( typeof conds != "undefined" )  {
      cls = conds[i];
    } 
    //alert(cls);
    str += "<td "+cls+">"+v+ "</td>";
  }
  str+="</tr>";
  $("#"+tblId).append(str);
}

function showError(msg,errorClass,time=2500)
{
  $("#"+errorClass).html(msg);
  $("#"+errorClass).removeClass("d-none");
  $("#"+errorClass).addClass("show");
  setTimeout( function() { $("#"+errorClass).removeClass("show"); $("#"+errorClass).addClass("d-none"); },time );
}

function showSuccess(msg,successClass,time=2500)
{
  showError(msg,successClass,time);
}

function logout()
{
  successFn = function(resp)  {
    window.location.assign("index.php");
  }
    
  data = { "action": "logout" };
  apiCall(data,successFn);
}

function closeModal(modalSel,tm=2000)
{
  setTimeout( function() { $(modalSel+" .close").trigger( "click" ) } ,tm) ;
}

function initTable(dataSet=Array(),cols,colDefs)
{
  if ( table != null )   table.destroy();
  
  table = $('#Tbl').DataTable( {
      data: dataSet,
      columns: cols,
      columnDefs: colDefs,
      dom: 'Bfrtip',
      buttons: [
         { extend: 'copyHtml5', title: "Fee Summary Report",footer: true },
        { extend: 'excelHtml5', title: "Fee Summary Report",footer: true },
        { extend: 'print', title: "Fee Summary Report",footer: true }
      ],
      "aaSorting": []
  } );
}

