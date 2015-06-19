var left = $("#left-area .scroll-pane");

$(document).ready(function () {
	
	$(document).on("submit","#add-user",function(e){
		e.preventDefault();
		var $this = $(this);
		var data = $this.serialize();
		
		$(".form-validation", $this).remove();
		$(".has-error", $this).removeClass("has-error");
		
		$.post("/save/company/find_user?companyID="+$(this).attr("data-companyID"),data,function(d){
			var d = d.data;
			validationErrors(d, $this);
			
			if (d['ID']){
				
				$("#form-modal").jqotesub($("#template-user-to-company"), d).modal("show");
			} else {
				$.bbq.pushState({"modal": "user-" + d['ID'] + "-" + d['companyID']});
				formUser('',d.email);
			}
			
			
		});
		return false;
	});
	
	
	$(document).on('submit', '#user-company-add-groups', function (e) {
		e.preventDefault();
		var d = $(this).serialize();

		$.post("/save/company/add_user?userID="+$(this).attr("data-userID")+"&companyID="+$(this).attr("data-companyID"),d,function(d) {

			alert("User added");
			$("#form-modal").modal("hide");
			
		});
		
	});
	
	$(document).on('submit', '#search-form', function (e) {
		e.preventDefault();
		$.bbq.pushState({"search-group":$("#search-group").val(),"search":$("#search").val()})

		getData();
		
	});
	
	
	
	
	
	
	
	$(document).on('click', '#search-clear-btn', function () {
		$.bbq.removeState("search");
		$.bbq.removeState("search-group");
		getData();
	});
	
	
	$(document).on('click', '[data-toggle="offcanvas"]', function () {
		$('#right-area').toggleClass('active')
	});

	$(window).resize(function () {
		$.doTimeout('resize', 250, function () {
			resize();
		});
	});

	resize();

	
	$(document).on('hide.bs.modal', "#form-modal", function () {
		getData();
	});

	$("#right-area").swipe({
		//Generic swipe handler for all directions
		swipe: function (event, direction, distance, duration, fingerCount, fingerData) {

			switch (direction) {
				case "right":
					$(this).addClass("active");
					break;
				case "left":
					$(this).removeClass("active");
					break;
			}


		}, //Default is 75px, set to 0 for demo so any distance triggers swipe
		threshold: 75, allowPageScroll: "auto"
	});
	$("#left-area").swipe({
		//Generic swipe handler for all directions
		swipe: function (event, direction, distance, duration, fingerCount, fingerData) {

			switch (direction) {

				case "left":
					$("#right-area").removeClass("active");
					break;
			}

		}, //Default is 75px, set to 0 for demo so any distance triggers swipe
		threshold: 75, allowPageScroll: "auto"
	}).addClass("affix-bottom");

	getData();

});
function getData() {
	var ID = $.bbq.getState("ID") || '';
	var search = $.bbq.getState("search") || '';
	var searchgroup = $.bbq.getState("search-group") || '';

	$(".loadingmask").show();
	
	$.getData("/data/company_users/data?companyID=" + _data['ID'], {"search":search,"search-group":searchgroup}, function (data) {

		

		$("#right-area-content").jqotesub($("#template-right"), data);
		$("#left-area-content").jqotesub($("#template-left"), data);


		$("#loading-mask").fadeOut();

		

		$.doTimeout(400,function(){
			resize();
		//	$("#search-group").select2();

		})
		resize()
		
		

	});


}


var settings = {
	maintainPosition: true,
	arrowButtonSpeed: 1,
	showArrows: false,
};

function resize() {
	//console.log($("#left-area").height())
	$("#right-area").css({minHeight: $("#left-area").height() - 2 + "px"});




	$.each($('.scroll-pane'), function () {
		var api = $(this).data('jsp');
		if (api) {
			api.reinitialise();
		} else {
			$(this).jScrollPane(settings);
		}

	});




}


