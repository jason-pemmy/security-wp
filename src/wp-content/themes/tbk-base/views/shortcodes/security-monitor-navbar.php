<div class="navoverlay"></div>
<div class="nav">
	<div id="logo-center">
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png">
	</div><!--end of logo-->
	<ul>
		<li class="clickable selected"> 
			<i class="material-icons">bug_report</i>
			<a href="#">Options</a>
		</li>
		<li class="clickable"> 
			<i class="material-icons">directions_bike</i>
			<a href="#">More options</a> 
		</li>
		<li class="clickable">
			<i class="material-icons">traffic</i>
			<a href="#">Even more options</a>
		</li>
	</ul>
	<div class="search-icon-container">
		<i class="material-icons">search</i>
	</div>
	<div class="search-bar-container">		
		<md-input-container>	  		
			<label>Search</label>
			<input type="text" ng-model="searchText">
		</md-input-container>
	</div>	
</div><!--end of nav--> 