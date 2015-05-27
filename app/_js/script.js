;



;$(document).ready(function () {

	$(document).on('click', '.btn-row-details', function (e) {
		var $this = $(this), $table = $this.closest("table");
		var $clicked = $(e.target).closest("tr.btn-row-details");
		var active = true;

		if ($this.hasClass("active") && $clicked) active = false;

		$("tr.btn-row-details.active", $table).removeClass("active");
		if (active) {
			$this.addClass("active");
		}

		var show = $("tr.btn-row-details.active", $table).nextAll("tr.row-details");

		$("tr.row-details", $table).hide();
		if (show.length) {
			show = show[0];
			$(show).show();
		}

	});

	
	page_resize();

	$('#form-modal').on('hidden.bs.modal', function () {
		$.bbq.removeState("modal")
	});
	
	
	$(window).resize(function () {
		$.doTimeout(250, function () {
			page_resize();
			
		});
	});
	
	
	
});
function page_resize() {

	var $body = $("body");
	var bH = $body.height();
	var wH = $(window).height();
	
	
	if (bH > wH){
		$body.addClass("sbhide")
	} else {
		$body.removeClass("sbhide")
	}
	var maxH = $(window).height() - $(".navbar-header").height()
	$(".navbar-collapse").css({maxHeight: maxH + "px"});

	var selectorWidth = ($(window).width() - 220);
	$("#mobile-selector-menu").css({width: selectorWidth + "px"});

	$(".selector-dropdown .dropdown-menu").css({maxHeight: $(window).height() - 60 + "px"});

	$.each($('.scroll-pane'), function () {
		var api = $(this).data('jsp');
		if (api) {
			api.reinitialise();
		} else {
			$(this).jScrollPane(settings);
		}

	});

};

function updatetimerlist(d, page_size) {
	//d = jQuery.parseJSON(d);

	if (!d || !typeof d == 'object') {
		return false;
	}
	//console.log(d);
	var data = d['timer'];
	var page = d['page'];
	var models = d['models'];

//console.log(models)


	if (data) {
		var highlight = "";
		if (page['time'] > 0.5)    highlight = 'style="color: red;"';

		var th = '<tr class="heading" style="background-color: #fdf5ce;"><td >' + page['page'] + '</td><td class="s g"' + highlight + '>' + page['time'] + '</td></tr>', thm;
		if (models) {
			thm = $("#template-timers-tr-models").jqote(models);
		} else {
			thm = "";
		}
		//console.log(thm)
		var timers = $("#template-timers-tr").jqote(data);
		//console.log(timers)

		//console.log($("#template-timers-tr"))
		$("#systemTimers").prepend(th + timers + thm);

		// console.log($("#systemTimers").prepend(th + $("#template-timers-tr").jqote(data, "*")));
	}

};