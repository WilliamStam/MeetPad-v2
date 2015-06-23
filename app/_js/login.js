
$(document).ready(function () {
	
	$(document).on("click",".user-forgot-password",function(e){
		e.preventDefault();
		$("#form-modal").jqotesub($("#template-user-forgot"), {}).modal("show");
	})
	
	$(document).on('submit', '#user-form', function (e) {
		e.preventDefault();
		var d = $(this).serialize();

		console.log(d)
		$.post("/save/profile/user",d,function(d) {

			$("#form-modal").jqotesub($("#template-form-user"), {}).modal("show");
			
		});
		
	});
	
	
	
	
	

});


