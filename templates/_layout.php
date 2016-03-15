<?php template('blocks/begin',array(
	'title'	=> __('Welcome'),
)); ?>

<?php template('blocks/analytics'); ?>

<div class="jumbotron">
	<div class="container">
		<h1><?=__('Welcome to Andelux Landing Pages!')?></h1>
		<p>

		</p>
	</div>
</div>

<div class="container">
	<?php template('blocks/messages'); ?>

	<?php template("views/{$request->getController()}/{$request->getAction()}"); ?>

	<hr>

	<footer>
		<p><?=__('2016 Andelux &copy;')?></p>
	</footer>
</div>

<?php template('blocks/end'); ?>