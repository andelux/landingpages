<?php template('blocks/begin',array(
	'title'	=> __('Welcome | Register'),
)); ?>

<?php template('blocks/analytics'); ?>

<div class="jumbotron">
	<div class="container">
		<h1><?=__('Welcome to our company!')?></h1>
		<p>

		</p>
	</div>
</div>

<div class="container">
	<?php template('blocks/messages'); ?>

	<div class="row">
		<div class="col-md-12">
			<p></p>

			<?php form_begin('lead'); ?>

			<div class="form-group">
				<label for="input-name"><?=__('Center name')?> <span class="asterisk">*</span></label>
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-registration-mark"></span></span>
					<input type="text" class="form-control validate-mandatory" id="input-name" placeholder="<?=__('Center')?>" name="CENTRONAME">
				</div>
			</div>

			<div class="form-group">
				<label for="input-name">Email <span class="asterisk">*</span></label>
				<div class="input-group">
					<span class="input-group-addon">@</span>
					<input type="email" class="form-control validate-mandatory" id="input-name" placeholder="hello@email.com" name="EMAIL">
				</div>
			</div>

			<div class="form-group">
				<label for="input-name"><?=__('Your name')?></label>
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
					<input type="text" class="form-control" id="input-name" placeholder="<?=__('Your name')?>" name="FNAME">
				</div>
			</div>

			<button type="submit" class="btn btn-primary btn-lg btn-block"><?=__('Join us!')?></button>
			<div><em><span class="asterisk">*</span> <?=__('mandatory fields')?></em></div>

			<?php form_end(); ?>

		</div>
	</div>

	<hr>

	<footer>
		<p><?=__('2016 Your Company Ltd &copy;')?></p>
	</footer>
</div>

<?php template('blocks/end'); ?>