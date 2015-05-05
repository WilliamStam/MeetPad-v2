var left = $("#left-area .scroll-pane");

$(document).ready(function () {
	$(document).on('click', '[data-toggle="offcanvas"]', function () {
		$('#right-area').toggleClass('active')
	});

	$(window).resize(function () {
		$.doTimeout('resize', 250, function () {
			resize();
		});
	});

	resize();

	$(document).on('click', "#left-area .table .record", function () {
		var $this = $(this);

		$.bbq.pushState({"ID": $this.attr("data-ID")});
		$('#right-area').removeClass("active");
		getData();
	});
	$(document).on('click', ".meeting-info-btn", function (e) {
		e.preventDefault();
		var $this = $(this);

		$.bbq.removeState("ID");
		$('#right-area').removeClass("active");
		getData();
	});
	$(document).on('click', ".scroll-paneaaa", function () {
		//getData();
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

	$(".loadingmask").show();
	$.getData("/data/meeting/data?meetingID=" + _data['ID'] + "&itemID=" + ID, {}, function (data) {

		var right_template = "#template-meeting-home";
		if (data['item']['ID']) {
			right_template = "#template-item";
		}

		$("#right-area-content").jqotesub($(right_template), data);
		$("#left-area-content").jqotesub($("#template-agenda"), data);




		$("#loading-mask").fadeOut();


		resize()

		
	});


}


var settings = {
	maintainPosition: true, // add your settings here, below are examples
	arrowButtonSpeed: 1, 
	showArrows: false, 
	animateScroll: true
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