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

function formatDate(input) {
  const date = new Date(input);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
  const year = String(date.getFullYear()).slice(-2); // Get last two digits
  let hours = date.getHours();
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'pm' : 'am';
  hours = hours % 12 || 12; // Convert to 12-hour format
  return `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
}




function viewMessages(po_id){
  successFn = function (resp) {

    var sender_id=resp.data.user_id;
    for (var key in resp.data.Messages) {
        if(sender_id ==parseInt(resp.data.Messages[key]["sender_id"])){
            resp.data.Messages[key]["sender"] = 1;
        }else{
            resp.data.Messages[key]["sender"] = 0;
        }
        resp.data.Messages[key]["created_at"]=formatDate(resp.data.Messages[key]["created_at"]);

    }
    loadTenderTemplate('#viewChatMessage_templ',toMustacheDataObj(resp), 'BaseModal');
    $('#BaseModal').modal('show');

  }
  data = { "function": 'Tender','method': 'getMessages','po_id': po_id }
  apiCall(data, successFn);
}


function sendMessage(po_id) {
  const input = document.getElementById('messageInput');
  const message = input.value.trim();
  if (message === '') return;

  const now = new Date();
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

  const messageHTML = `
    <div class="message sent">
      <div class="username text-success">You</div>
      <div>${message}</div>
      <div class="timestamp">${time}</div>
    </div>
  `;

  const chat = document.getElementById('chatMessages');
  chat.insertAdjacentHTML('beforeend', messageHTML);
  input.value = '';
  chat.scrollTop = chat.scrollHeight;

  addMessage(po_id, message);
}

  

function addMessage(po_id, message){
  successFn = function (resp) {

  }
  errorFn = function (resp) {

  }
  data = { "function": 'Tender','method': 'sendMessage','po_id': po_id,'message':message  };
  apiCall(data, successFn,errorFn);
}
