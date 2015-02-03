$(document).ready(function(){
	
});

$(document).ready(function () {
	$('[data-toggle="offcanvas"]').click(function () {
		$('#right-area').toggleClass('active')
	});
	
	resize()
	$( window ).resize(function() {
		resize()
	});
	
	$(".test-nav").on("click",function(){
		$('#right-area').removeClass("active");
	});

	$("#right-area").swipe( {
		//Generic swipe handler for all directions
		swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
			
			switch(direction){
				case "right":
					$(this).addClass("active");
					break;
				case "left":
					$(this).removeClass("active");
					break;
			}
			
			
		},
		//Default is 75px, set to 0 for demo so any distance triggers swipe
		threshold:0
	});
	$("#left-area").swipe( {
		//Generic swipe handler for all directions
		swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
			
			switch(direction){
				
				case "left":
					$("#right-area").removeClass("active");
					break;
			}
			
		},
		//Default is 75px, set to 0 for demo so any distance triggers swipe
		threshold:0
	}).addClass("affix-bottom");
	
	
});

function resize(){
	console.log($("#left-area").height())
	$("#right-area").css({ minHeight: $("#left-area").height() + "px" });
}