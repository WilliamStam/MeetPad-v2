var left = $("#left-area .scroll-pane");

$(document).ready(function () {
	
	
	
	$(document).on('submit', '#user-form', function (e) {
		e.preventDefault();
		var d = $(this).serialize();

		console.log(d)
		$.post("/save/profile/user",d,function(d) {

			$("#form-modal").jqotesub($("#template-form-user"), {}).modal("show");
			
		});
		
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

	

});
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


