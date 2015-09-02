;


var toolbar = [
	['Source'],
	[
		'Cut',
		'Copy',
		'Paste',
		'Find',
		'Replace'
	],
	[
		'Bold',
		'Italic',
		'Underline',
		'StrikeThrough'
	],
	[
		'Styles',
		'Format',
		'Font',
		'FontSize'
	],
	[
		'NumberedList',
		'BulletedList'
	],
	[
		'Outdent',
		'Indent'
	]
];
var toolbar_small = [
	['Source'],
	[
		'Cut',
		'Copy',
		'Paste',
		'Find',
		'Replace'
	],
	[
		'Bold',
		'Italic',
		'Underline',
		'StrikeThrough'
	]
];
var ckeditor_config = {
	height            : '150px',
	toolbar           : toolbar,
	extraPlugins      : 'autogrow',
	autoGrow_minHeight: 150,
	autoGrow_maxHeight: 0,
	removePlugins     : 'elementspath',
	resize_enabled    : false,
	skin : 'bootstrapck,/app/_css/ckeditor/bootstrapck/'
};
var ckeditor_config_small = {
	height            : '117px',
	toolbar           : toolbar_small,
	removePlugins     : 'elementspath',
	resize_enabled    : false,
	extraPlugins      : 'autogrow',
	autoGrow_minHeight: 117,
	autoGrow_maxHeight: 0,
	skin : 'bootstrapck,/app/_css/ckeditor/bootstrapck/'
};


;$(document).ready(function () {
	
	$.fn.keepAlive();

	$('[data-toggle="tooltip"]').tooltip()
	
	
	$(document).on('click', '.resend-activation-email', function (e) {
		e.preventDefault();
		$.post("/save/profile/resend",{"p":"w"},function(data){
			data = data.data;
			//console.log(data);
			$("#form-modal").jqotesub($("#template-user-activate-sent"), data).modal("show");
		})
		
		
		
	});
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

	var selectorWidth = ($(window).width() - 55);
	$("#mobile-selector-menu").css({width: selectorWidth + "px"});

	$(".selector-dropdown .dropdown-menu").css({maxHeight: $(window).height() - 60 + "px"});

	var isMobile = window.matchMedia("only screen and (min-width: 992px)");
	$.each($('.scroll-pane'), function () {
		var api = $(this).data('jsp');
		if (isMobile.matches) {
			if (api) {
				api.reinitialise();
			} else {
				$(this).jScrollPane(settings);
			}
		} else {
			if (api) {
				api.destroy();
			}
		}
	});

}

function updatetimerlist(d, page_size) {
	//d = jQuery.parseJSON(d);

	if (!d || !typeof d == 'object') {
		return false;
	}
	//console.log(d);
	var data = d['timer'];
	var page = d['page'];
	var models = d['models'];
	var menu = d['menu'];




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

	//console.log(menu)
	
		$("#main-nav-bar").jqotesub($("#template-navbar"), menu);
		page_resize()
	
	

};

function validationErrors(data, $form) {
	if (!$.isEmptyObject(data['errors'])) {
		var i = 0;
		$.each(data.errors, function (k, v) {
			i = i + 1;
			var $field = $("#" + k);
			//console.info(k)
			var $block = $field.closest(".form-group");

			$block.addClass("has-error");
			if ($field.parent().hasClass("input-group")) $field = $field.parent();
			if (v != "") {
				
				$field.after('<span class="help-block s form-validation">' + v + '</span>');
			}
			if ($block.hasClass("has-feedback")){
				$field.after('<span class="fa fa-times form-control-feedback form-validation" aria-hidden="true"></span>')
			}


		});
		$(".has-error").get(0).scrollIntoView();
		$("button[type='submit']", $form).addClass("btn-danger").html("(" + i + ") Error(s) Found");

	} 

	submitBtnCounter($form);
	
	
}

function submitBtnCounter($form) {
	var c = $(".form-group.has-error").length;
	var $btn = $("button[type='submit']", $form);
	if (c) {
		$btn.addClass("btn-danger").html("(" + c + ") Error(s) Found");
	} else {
		
		var tx = $btn.attr("data-text")||"Save changes";
		
		$btn.html(tx).removeClass("btn-danger");
	}
}