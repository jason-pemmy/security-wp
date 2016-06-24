jQuery(document).ready(function($) {
	
	$('#hamburger').click(function() {		
		$('.nav').css('margin-left', '0');
		$('.navoverlay').css('opacity', '1');
		$('.navoverlay').css('z-index', '60');
		
		$('.navoverlay').click(function() {
			$('.navoverlay').css('opacity', '0');
			$('.navoverlay').css('z-index', '-1');
			$('.nav').css('margin-left', '-100%');
		});
	});
	
	$('#plus').click(function() {
		$('.navoverlay').css('z-index', '100');
		$('.navoverlay').css('opacity', '1');
		$('#activityform').css('height', '500px');
		$('#activityform').css('padding', '20px');
		$('#activityform').css('opacity', '1');
		$('#activityform').css('z-index', '110');
		
		$('.navoverlay').click(function() {
			$('.navoverlay').css('z-index', '-1');
			$('.navoverlay').css('opacity', '0');
			$('#activityform').css('height', '0px');
			$('#activityform').css('padding', '0px');
			$('#activityform').css('opacity', '0');
			$('#activityform').css('z-index', '-2'); 
		});
	});
	
	var filtersOpen = false;
	
	$('#option').click(function() {
		$('.blog-post').css('display', 'block');
	});
	
	$('#settings').click(function() {
		if (filtersOpen) {
			//Move Filter Window Off screen                                    
			$('.filters').css('right','-100%');                
			filtersOpen = false;//set the boolean to false                     
		} else {
			$('.filters').css('right', '0px');  
			filtersOpen = true; //set the boolean to true                                   
		}
	});
	
	var blogOpen = false;
	
	$('#blog-filter').click(function() {
		if (!blogOpen) {
			$('.blog').css('height', '0');
			$('.blog').css('margin-bottom', '0');
			$('#blog-filter').css('color', '#DEDEDE');
			blogOpen = true;
		} else {
			$('.blog').css('height', '100px');
			$('.blog').css('margin-bottom', '10px');
			$('#blog-filter').css('color', '#222');
			blogOpen = false;
		}
	});
	
	var portfolioOpen = false;
	
	$('#portfolio-filter').click(function() {
		if (!portfolioOpen) {
			$('.portfolio').css('height', '0');
			$('.portfolio').css('margin-bottom', '0px');
			$('#portfolio-filter').css('color', '#DEDEDE');
			portfolioOpen = true;
		} else {
			$('.portfolio').css('height', '100px');
			$('.portfolio').css('margin-bottom', '10px');
			$('#portfolio-filter').css('color', '#222');
			portfolioOpen = false;
		}
	});

	var ink, d, x, y;
	
	$(".clickable").on("mouseover", function(e) {
		$(this).find('a').css("color","#fff");
	});
	
	$(".clickable").on("mouseout", function(e) {
		$(this).find('a').css("color","#000");
	});
	
	$(".clickable").click(function(e) {
		if ($(this).find(".ink").length === 0) {
			$(this).prepend("<span class='ink'></span>");
		}
		
		$(".clickable").removeClass("selected");
		$(this).addClass("selected");

		ink = $(this).find(".ink");
		ink.removeClass("animate");

		if (!ink.height() && !ink.width()) {
			d = Math.max($(this).outerWidth(), $(this).outerHeight());
			ink.css({height: d, width: d});
		}

		x = e.pageX - $(this).offset().left - ink.width()/2;
		y = e.pageY - $(this).offset().top - ink.height()/2;

		ink.css({top: y+'px', left: x+'px'}).addClass("animate");
	});
});