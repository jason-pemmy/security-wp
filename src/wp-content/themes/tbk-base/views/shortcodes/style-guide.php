<?php
$colors = array(
	array(
		'name' => 'blue',
		'hex' => '#1fbaff',
	),
	array(
		'name' => 'blue-dark',
		'hex' => '#006792',
	),
	array(
		'name' => 'white',
		'hex' => '#fff',
	),
	array(
		'name' => 'grey',
		'hex' => '#636363',
	),
	array(
		'name' => 'grey-dark',
		'hex' => '#222',
	),
	array(
		'name' => 'black',
		'hex' => '#000',
	),
);
?>
<div class="container">
	<section id="tabs">
		<h2>Tabs</h2>
		<div role="tabpanel">
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Home</a></li>
				<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profile</a></li>
				<li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Messages</a></li>
				<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="home">
					<p>Home tab</p>
				</div>
				<div role="tabpanel" class="tab-pane" id="profile">
					<p>Profile tab</p>
				</div>
				<div role="tabpanel" class="tab-pane" id="messages">
					<p>Messages tab</p>
				</div>
				<div role="tabpanel" class="tab-pane" id="settings">
					<p>Settings tab</p>
				</div>
			</div>
		</div>
	</section>
	<section id="breadcrumbs">
		<h2>Breadcrumbs</h2>
		<ol class="breadcrumb">
			<li><a href="#">Home</a></li>
			<li><a href="#">Library</a></li>
			<li class="active">Data</li>
		</ol>
	</section>
	<section id="buttons">
		<h2>Buttons</h2>
		<button class="btn" role="button">Submit</button>
	</section>
	<section id="forms">
		<h2>Forms</h2>
		<form role="form">
			<div class="form-group has-required">
				<label class="control-label" for="input-field">Input Field:</label>
				<input type="text" class="form-control" id="input-field" placeholder="Text sample" required>
			</div>
			<div class="form-group">
				<label class="control-label" for="dropdown">Dropdown:</label>
				<select class="form-control" id="dropdown">
					<option>An option</option>
					<option>Another option</option>
					<option>Options!</option>
				</select>
			</div>
			<div class="form-group has-error">
				<label class="control-label" for="sample-error-message">Sample Error Message:</label>
				<input type="text" class="form-control" id="sample-error-message" placeholder="Text sample">
				<span class="help-block">Lorem ipsum dolor sit amet</span>
			</div>
			<button class="btn" type="submit">Submit</button>
		</form>
	</section>
	<section id="type">
		<h2>Type</h2>
		<h1>Heading 1</h1>
		<h2>Heading 2</h2>
		<h3>Heading 3</h3>
		<h4>Heading 4</h4>
		<h5>Heading 5</h5>
		<h6>Heading 6</h6>
		<p>Lorem ipsum dolor site amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh.</p>
	</section>
	<section id="ol">
		<h3>Ordered List <small>(ol)</small></h3>
		<ol>
			<li>Pellentesque femerntum dolor. Aliquam lectus, facilisis auctor, ultrices ut</li>
			<li>Sed adipiscing ornare risus</li>
			<li>Morbi est est, blandit amet, sagittis vel, pellentesque egestas sem</li>
		</ol>
	</section>
	<section id="ul">
		<h3>Unordered List <small>(ul)</small></h3>
		<ul>
			<li>Pellentesque femerntum dolor. Aliquam lectus, facilisis auctor, ultrices ut</li>
			<li>Sed adipiscing ornare risus</li>
			<li>Morbi est est, blandit amet, sagittis vel, pellentesque egestas sem</li>
		</ul>
	</section>
	<section id="news">
		<h3>News</h3>
		<ul class="news">
			<li class="news-item">
				<a class="news-item-heading" href="#">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros.</a>
				<time class="news-item-date" datetime="2015-01-12">January 12, 2015</time>
			</li>
			<li class="news-item">
				<a class="news-item-heading" href="#">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros.</a>
				<time class="news-item-date" datetime="2015-01-12">January 12, 2015</time>
			</li>
		</ul>
	</section>
	<section id="tips">
		<h3>Tips</h3>
		<div class="tip">
			<p>Lorem ipsum dolor site amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh.</p>
		</div>
	</section>
	<section id="image-hover">
		<h3>Image Hover</h3>
		<a class="image-hover" href="#">
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-hover.jpg">
		</a>
	</section>
</div>
