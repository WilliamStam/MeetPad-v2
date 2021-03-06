var left = $("#left-area .scroll-pane");

$(document).ready(function () {
	$(document).on('click', '[data-toggle="offcanvas"]', function () {
		$('#right-area').toggleClass('active')
	});
	
	$(document).on('click', '.pagination li a', function (e) {
		e.preventDefault();
		var page = $(this).attr("data-page");
		$.bbq.pushState({"page":page});
		getData();
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
	var page = $.bbq.getState("page") || '1';
	
	$(".loadingmask").show();
	
	$.getData("/data/company_meetings/data?companyID=" + _data['ID'], {"page":page}, function (data) {
	

		$("#right-area-content").jqotesub($("#template-right"), data);
		$("#left-area-content").jqotesub($("#template-left"), data);
		$("#pagination").jqotesub($("#template-pagination"), data['meetings']['pagination']);

		$("#loading-mask").fadeOut();


		$.doTimeout(400,function(){
			resize();
			

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


