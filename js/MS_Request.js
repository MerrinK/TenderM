function requests(nav='',req=0){
  MakeNavActive(nav);
 
  successFn = function(resp)  {
    loadTenderTemplate('#RequestedPO_templ',toMustacheDataObj(resp), 'Dashboard');
    RequestedPOs();
  }
  data = {"function": 'Tender', "method":"AllTenders"};
  apiCall(data,successFn);
}

function RequestedPOs(id=''){
  successFn = function(resp)  {
    for (var key in resp.data.RequestedPO) {
      resp.data.RequestedPO[key]["required_by"]= moment(resp.data.RequestedPO[key]["required_by"]).format('DD-MM-YYYY');
    }
    loadTenderTemplate('#RequestedPOMat_templ',toMustacheDataObj(resp), 'TdBody');
    $("#Dt_RequestList").DataTable();
    
    $('[data-toggle="tooltip"]').tooltip();
  }
  data = {"function": 'Tender', "method":"RequestedPOs","tender_id":id};
  apiCall(data,successFn);
}


function viewRequestedPO(id, RequestMaterial,RequestSubMaterial,RequestQuantity,RequestMaterialUnitName,RequestCustomMaterial,RequestCustomSubMaterial,RequestCustomQuantity,RequestCustomMaterialUnitName, requested_by, required_by){
  // var data=[1]
  loadTenderTemplate('#requestMaterial_templ',{}, 'BaseModal');
  $('#BaseModal').modal('show');

  if(RequestMaterial!=''){
    $('#StandardMaterial').removeClass('d-none');
    StandardMaterial
    var materialArray = RequestMaterial.split(',');
    var subMaterialArray = RequestSubMaterial.split(',');
    var quantityArray = RequestQuantity.split(',');
    var unitNameArray = RequestMaterialUnitName.split(',');
    for(i=0;i<materialArray.length;i++){
      $('#stdMat_body').append(`<tr><td>${i+1}</td><td>${materialArray[i]} - ${subMaterialArray[i]}</td><td>${quantityArray[i]} ${unitNameArray[i]}</td></tr>`)
    }
  }else{
    $('#StandardMaterial').addClass('d-none');
  }

  if(RequestCustomMaterial!=''){
    $('#CustomMaterial').removeClass('d-none');
    var materialArray = RequestCustomMaterial.split(',');
    var subMaterialArray = RequestCustomSubMaterial.split(',');
    var quantityArray = RequestCustomQuantity.split(',');
    var unitNameArray = RequestCustomMaterialUnitName.split(',');
    for(i=0;i<materialArray.length;i++){
      $('#cusMat_body').append(`<tr><td>${i+1}</td><td>${materialArray[i]} - ${subMaterialArray[i]}</td><td>${quantityArray[i]} ${unitNameArray[i]}</td></tr>`)
    }
  }else{
    $('#CustomMaterial').addClass('d-none');
  }
  

}
