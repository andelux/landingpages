<?php \LandingPages\Template::parse('blocks/begin',array(
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
	<div class="row">
		<div class="col-md-12">
			<p></p>

			<form id="lead" action="<?=\LandingPages\Template::getFormAction()?>" method="post" onsubmit="return validate() && stats('<?=\LandingPages\Stats::getConversionUrl('lead')?>')">

                <?=\LandingPages\Template::getFormKeyHtml()?>

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

			</form>

		</div>
	</div>

	<hr>

	<footer>
		<p><?=__('2016 Your Company Ltd &copy;')?></p>
	</footer>
</div>

<script type="text/javascript">
	function validate()
	{
		var $inputs = $('form input');
		var ok = true;
		for ( var i = 0; i < $inputs.length; i++ ) {
			var inp = $($inputs[i]);
			if ( inp.hasClass('validate-mandatory') ) {
				if ( inp.val().trim() == '' ) {
					// ERROR
					inp.parents('.form-group').addClass('has-error has-feedback');
					if ( ok ) inp.focus();
					ok = false;
				} else {
					// OK
					inp.parents('.form-group').removeClass('has-error has-feedback');
				}
			}
		}

		return ok;
	}
</script>

<?php template('blocks/end'); ?>