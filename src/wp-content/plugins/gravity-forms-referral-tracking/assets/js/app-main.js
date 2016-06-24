jQuery(document).ready(function( $ ) {

	/* Set a cookie if it doesn't exist, to store the referrer url if it is in the headers */
	var tbkgf_cookie = $.cookie('tbkgf_referrer_cookie');

	if(tbkgf_cookie === undefined){
		/* Lets set the cookie */

		// check document referrer
		if (document.referrer !== ''){
			var url_referrer = document.referrer;
		}else {
			var url_referrer = 'direct';
		}

		$.cookie('tbkgf_referrer_cookie', url_referrer, { expires: 365, path: '/' });
	}
});
