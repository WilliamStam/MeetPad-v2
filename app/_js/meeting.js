var left = $("#left-area .scroll-pane");

$(document).ready(function () {
	$(document).on('click', '[data-toggle="offcanvas"]', function () {
		$('#right-area').toggleClass('active')
	});
	
	
	$(document).on('click', '.comment-button', function () {
		var $this = $(this);
		var ID = $this.attr("data-ID");
		var itemID = $this.attr("data-itemID");
		$.getData("/data/form/item_comment?ID=" + ID+"&itemID="+itemID, {}, function (data) {
			$("#form-modal").jqotesub($("#template-item-comment-form"), data).modal("show");
		//	console.log(ckeditor_config)
			var on_ckeditor_config = ckeditor_config;
			on_ckeditor_config['startupFocus'] = true;
			on_ckeditor_config['on'] = {
				'instanceReady': function(evt) {
//Set the focus to your editor
					//console.log("ready");
					//CKEDITOR.instances['item-comment'].focus();
				}
			};
			//console.log(on_ckeditor_config)
			CKEDITOR.replace('item-comment',on_ckeditor_config);

			CKEDITOR.instances['item-comment'].on('instanceReady', function (event) {
				CKEDITOR.instances['item-comment'].focus();
			});
			
			
		});
		
	});
	

	

	$(window).resize(function () {
		$.doTimeout('resize', 250, function () {
			resize();
		});
	});


	$(document).on("keyup","#comments-list textarea, #comment-input-open textarea",function(e) {
		while($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
			$(this).height($(this).height()+1);
			$.doTimeout('resize', 250, function () {
				show_comment_button();
				resize();
			});
		};
	});
	$(document).on("keyup change","#comments-list textarea",function(e) {
					
			$.doTimeout('resize', 250, function () {
				show_comment_button();
				resize();
			});
		
	});
	$(document).on("submit","#form-modal .comment-form",function(e){
		$("#form-modal").modal("hide");
		
	});


	$(document).on("reset",".comment-form",function(){
		$(this).find("textarea").animate({"height":30},500);
		$.doTimeout('resize', 250, function () {
			show_comment_button();
			resize();
		});
	});
	$(document).on("submit",".comment-form, .comment-form-input",function(e){
		e.preventDefault();
		var $form = $(this);
		var ID = $form.attr("data-ID");
		var parentID = $form.attr("data-parentID");
		var itemID = $form.attr("data-itemID");
		var html = $form.attr("data-html");
		var data = $form.serialize();
		
		
		$(".loadingmask").show();
		$.post("/save/comment/item?ID="+ID+"&itemID="+itemID+"&parentID="+parentID+"&html="+html,data,function(d){
			if ($("#comments-list").length){
				$("#comments-list").jqotesub($("#template-comments"), d.data.comments);
				show_comment_button();
				resize();
				$("#comment-input-close").show();
				$("#comment-input-open").hide();

				$form.find("textarea").val("");

				$("#loading-mask").fadeOut();
			} else {
				getData();
			}
		});
		
		
		
		
	})
	

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

	$(".loadingmask").show();
	$.getData("/data/meeting/data?meetingID=" + _data['ID'] + "&itemID=" + ID, {}, function (data) {

		var right_template = "#template-meeting-home";
		if (data['item']['ID']) {
			right_template = "#template-item";
			
		}

		$("#right-area-content").jqotesub($(right_template), data);
		$("#left-area-content").jqotesub($("#template-agenda"), data);


		$("#loading-mask").fadeOut();

		

		$.doTimeout(400,function(){
			resize();
			if (right_template == "#template-item"){
				$("#left-area-content .scroll-pane").data("jsp").scrollToElement("tr[data-id='"+data['item']['ID']+"']",false,false);
			}
			
		})
		resize()
		if (right_template == "#template-item"){
			$("#left-area-content .scroll-pane").data("jsp").scrollToElement("tr[data-id='"+data['item']['ID']+"']",false,false);
		}
		showContent_state();
		
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

	
	$(".btn-breadcrumb").each(function(){
		var $this = $(this);
		var width = $this.parent().innerWidth();
		var n = 0;
		var i = $("a",$this).length;

		var bl = width / $("a",$this).length;
		
		
		$("a",$this).each(function(){
			var $this = $(this);
			var tw = $this.outerWidth();
			n = n + tw;

				
			$this.css({"width":bl-8});
			
			
		});

		

	});
	
	



}
function show_comment_button(){
	
	$("#comments-list textarea").each(function(){
		var $this = $(this);
		var $btns = $this.closest("div").find(".comment-btn-bar");
		if ($this.val()!=""){
			$btns.show(500);
		} else {
			$btns.hide(500);
		}
	});
	
	
}

$(document).on('click', '#details-record-prev', function () {
	prevRecord();
});
$(document).on('click', '#details-record-next', function () {
	nextRecord();
});


function nextRecord() {
	var ID = $.bbq.getState("ID");
	var $item = $("#record-list .record[data-ID='" + ID + "']");
	var $new = $item.nextAll("tr.record:first");
	$.bbq.pushState({ID:$new.attr("data-ID")});
	getData();
}
function prevRecord() {
	var ID = $.bbq.getState("ID");
	var $item = $("#record-list .record[data-ID='" + ID + "']");
	var $new = $item.prevAll("tr.record:first");
	$.bbq.pushState({ID:$new.attr("data-ID")});
	getData();
}

function showContent_state() {
	var ID = $.bbq.getState("ID");
	if ($("#record-list .record[data-ID='" + ID + "']").prevAll("tr.record:first").length == 0) {
		$("#details-record-prev").attr("disabled", "disabled");
	} else {
		$("#details-record-prev").removeAttr("disabled");
	}
	if ($("#record-list .record[data-ID='" + ID + "']").nextAll("tr.record:first").length == 0) {
		$("#details-record-next").attr("disabled", "disabled");
	} else {
		$("#details-record-next").removeAttr("disabled");
	}
	var tab = $.bbq.getState("details-tab"), $details_modal = $('#ab-details-modal');
	if (!tab) tab = "details-pane-details";
	$('.nav-tabs li a[href="#' + tab + '"]', $details_modal).parent().addClass("active");
	$('#' + tab + '', $details_modal).addClass("active");

}

