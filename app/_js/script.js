$(document).ready(function(){

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
	mobileview()
	$( window ).resize(function() {
		mobileview()
	});
});
function mobileview(){
	var maxH = $(window).height() - $(".navbar-header").height()
	$(".navbar-collapse").css({ maxHeight: maxH + "px" });
	
	//$("#mobile-selector-menu ").css({width:($(window).width()-300) + "px" })
	$("#mobile-selector-menu").css({width:($(window).width()-300) + "px" });
		
		$(".selector-dropdown .dropdown-menu").css({maxHeight: $(window).height() - 60 + "px"})
	
}

function updatetimerlist(d, page_size) {
	//d = jQuery.parseJSON(d);

	if (!d || !typeof d == 'object') {
		return false;
	}
console.log(d);
	var data = d['timer'];
	var page = d['page'];
	var models = d['models'];

//console.log(models)


	if (data) {
		var highlight = "";
		if (page['time'] > 0.5)    highlight = 'style="color: red;"';

		var th = '<tr class="heading" style="background-color: #fdf5ce;"><td >' + page['page'] + '</td><td class="s g"' + highlight + '>' + page['time'] + '</td></tr>', thm;
		if (models) {
			thm = $("#template-timers-tr-models").jqote(models, "*");
		} else {
			thm = "";
		}
		//console.log(thm)

		$("#systemTimers").prepend(th + $("#template-timers-tr").jqote(data, "*") + thm);

		// console.log($("#systemTimers").prepend(th + $("#template-timers-tr").jqote(data, "*")));
	}

};