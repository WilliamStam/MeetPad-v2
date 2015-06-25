
$(document).ready(function () {
	
	$(document).on("submit","#reset-password",function(e){
		e.preventDefault();
		var $this = $(this);
		var data = $this.serialize();
		
		$(".form-validation", $this).remove();
		$(".has-error", $this).removeClass("has-error");
		
		$.post("/save/profile/resetpassword?ID="+$(this).attr("data-ID"),data,function(d){
			var d = d.data;
			validationErrors(d, $this);
			if ($.isEmptyObject(d['errors'])) {
				$("#form-modal").jqotesub($("#template-form-user-resetpassword-saved"), d).modal("show");
			}
			
		});
		return false;
	});
	
	
	

});

