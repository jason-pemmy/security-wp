jQuery(document).ready(function($) {	
	
	var desktopView;
	
	$(window).resize(function(){	
		desktopView = Modernizr.mq('(min-width: 1000px)');	
		
		if (desktopView) {				
			$('.secure').css('margin-bottom', '10px');
			$('.vulnerable').css('margin-bottom', '10px');			
		} else {
			$('.secure').css('margin-bottom', '1px');	
			$('.vulnerable').css('margin-bottom', '1px');							  
		}
	});
	
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
	
	var feedItemClosed = true;
	$(".status-indicator1-vulnerable").click(function(){		
		if(feedItemClosed) {
			$(this).parent().parent().parent().css("height","300px");	
			feedItemClosed = false;
		} else {
			$(this).parent().parent().parent().css("height","100px");	
			feedItemClosed = true;
		}
		
	});
	
	var filtersOpen = false;
	
	$('#option').click(function() {
		$('.blog-post').css('display', 'block');
	});
	
	$('#settings').click(function() {
		if (filtersOpen) {
			//Move Filter Window Off screen                                    
			$('.filters').css('right','-100%');                
			filtersOpen = false;                   
		} else {
			$('.filters').css('right', '0px');  
			filtersOpen = true;
		}
	});	
	
	var blogOpen = false;
	
	$('#vulnerable-filter').click(function() {
		if (!blogOpen) {
			$('.vulnerable').css('height', '0');
			$('.vulnerable').css('margin-bottom', '0');
			$('#vulnerable-filter').css('color', '#DEDEDE');
			blogOpen = true;
		} else {
			$('.vulnerable').css('height', '100px');
			if (desktopView) {
				console.log("sup*");
				$('.vulnerable').css('margin-bottom', '10px');	
			} else {
				$('.vulnerable').css('margin-bottom', '1px');	
			}			
			
			$('#vulnerable-filter').css('color', '#222');
			blogOpen = false;
		}
	});
	
	var portfolioOpen = false;
	
	$('#secure-filter').click(function() {
		if (!portfolioOpen) {
			$('.secure').css('height', '0');
			$('.secure').css('margin-bottom', '0px');
			$('#secure-filter').css('color', '#DEDEDE');
			portfolioOpen = true;
		} else {
			$('.secure').css('height', '100px');
			if (desktopView) {
				console.log("sup***");
				$('.secure').css('margin-bottom', '10px');	
			} else {
				$('.secure').css('margin-bottom', '1px');	
			}
			$('#secure-filter').css('color', '#222');
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