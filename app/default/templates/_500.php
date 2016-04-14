<?php template('blocks/begin',array(
	'title' => __('Error 500 - Internal Server Error'),
)); ?>

<?php template('blocks/analytics'); ?>

<div class="jumbotron">
	<div class="container">
		<h1>500 Internal Server Error</h1>
		<p>
			Unknown error
		</p>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>We apologize!</h2>
		</div>
	</div>

	<hr>

	<footer>
		<p>2016 Your Company Ltd &copy;</p>
	</footer>
</div>

<?php template('blocks/end'); ?>