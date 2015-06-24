
$(document).ready(function () {
	
	$(document).on("click",".user-forgot-password",function(e){
		e.preventDefault();
		var data = {};
		data.email = $("#login_email").val();
		$("#form-modal").jqotesub($("#template-user-forgot"), data).modal("show");
	})
	
	$(document).on('submit', '#forgot-password-form', function (e) {
		e.preventDefault();
		var $this = $(this);
		var data = $this.serialize();
		var $submit = $("button[type='submit']", $this).html("Save changes").removeClass("btn-danger");
		$(".form-validation", $this).remove();
		$(".has-error", $this).removeClass("has-error");
		
		$.post("/data/forgot_password/data",data,function(r) {
			var data = r.data;
			validationErrors(data, $this);
			
			if ($.isEmptyObject(data['errors'])) {
				$("#form-modal").jqotesub($("#template-user-forgot-sent"), data);
			}
			
			
			
			
		});
		
	});
	
	
	
	
	

});


