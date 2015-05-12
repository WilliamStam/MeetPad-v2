
$(document).ready(function () {

	$(window).resize(function () {
		$.doTimeout('resize', 250, function () {
			resize();
		});
	});

	resize();

});
function resize() {
	//console.log($("#left-area").height())
	$("#whole-area").css({minHeight: $(window).height() - 60 + "px"});

	$.each($('.scroll-pane'), function () {
		var api = $(this).data('jsp');
		if (api) {
			api.reinitialise();
		} else {
			$(this).jScrollPane(settings);
		}

	});
}