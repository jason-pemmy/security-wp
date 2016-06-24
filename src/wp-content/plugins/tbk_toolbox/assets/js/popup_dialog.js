jQuery(document).ready(function($){

    $('.tbkcp_main_popup').live('click',function(){

	var htm = $(this).html();
	var url = this.href;
	var title = $(this).attr('title');
	var dialog = $("#tbkcp_dialog");
	var cls = $(this).attr('id');

	var t = $(this);
	if ($("#tbkcp_dialog").length == 0) {
	    dialog = $('<div id="tbkcp_dialog" style="display:hidden"></div>').appendTo('body');
	}
	//$(this).html('<img style ="height:15px;width:74px" class = "loader" src="<?php bloginfo('template_url') ?>/images/loader.gif"/>');




	dialog.dialog({
	    // add a close listener to prevent adding multiple divs to the document
	    close: function(event, ui) {
		// remove div with all data and events
		dialog.remove();
	    },
	    modal: true,
	    title: title,
	    width:782,
	    closeText: '',

	    show: "fade",
	    hide: "fade",
	    dialogClass : cls
	});

	dialog.load(
	    url,
	    {}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
	    function (responseText, textStatus, XMLHttpRequest) {
	    // remove the loading class
	    //alert(responseText);
	    //t.html(htm);
	    }
	    );

		return false;

    })
})
