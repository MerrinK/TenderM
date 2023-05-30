

function TestingSweetAlert(){
    // Swal.fire('Testing SWAL...');

Swal.fire({
  icon: 'success',
  title: 'Your work has been saved',
  showConfirmButton: false,
  timer: 1500
});

}

function SwalRoundTick(message='Just Sweet Alert'){
	Swal.fire({
	  icon: 'success',
	  title: message,
	  showConfirmButton: false,
	  timer: 1500
	});
}


function SweetAlertTest(){
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
      Swal.fire(
        'Deleted!',
        'Your file has been deleted.',
        'success'
      )
    }
  });
}