<!doctype html>
<html class="no-js" <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php wp_title( '|', true, 'right' );?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />
		<?php wp_head(); ?>
	</head>
	<body ng-app="tbk-security" ng-controller="MainController">
		<div class="allholder">			
			<div class="bodyholder">				
				<div class="toolbar">
					<div id="hamburger">
						<i class="material-icons">menu</i>
					</div><!--end of hamburger-->
					<div id="apptitle"><h1>TBK Site Security Monitor</h1></div>
					<div class="tools">
						<ul>
							<!--<li><i class="fa fa-search" id="search"></i></li>-->
							<li class="clickable">
								<!--<i class="fa fa-ellipsis-v" id="settings"></i>-->
								<i class="material-icons" id="settings">more_vert</i>
							</li>
						</ul>
					</div><!--end of tools-->
				</div><!--end of toolbar-->
				<div class="filters">
					<ul>
						<li class="clickable" id="secure-filter">
							<i class="material-icons">sentiment_very_satisfied</i>
							<span>Secure</span>
						</li>
						<li class="clickable" id="vulnerable-filter">
							<i class="material-icons">sentiment_very_dissatisfied</i>
							<span>Vulnerable</span>
						</li>
					</ul>
				</div><!--end of filters-->			
				<div class="clickable" id="plus">
					<i class="material-icons">assessment</i>
				</div>
			</div><!--end of bodyholder-->
			<div id="activityform">
				
			</div><!--End ActivityForm-->