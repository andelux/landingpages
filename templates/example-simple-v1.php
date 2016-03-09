<?php template('blocks/begin',array(
	'title'	=> __('Welcome | Register'),
)); ?>
<?php template('blocks/analytics'); ?>

<div class="jumbotron">
	<div class="container">
		<h1><strong><em><span style="color:#48c">Top</span><span style="color:#000">Buceo</span></em></strong>, la web de los buceadores</h1>
		<p>
			Queremos ser el portal de referencia para los aficionados al buceo recreativo.
			<br/>
			Un lugar donde los buceadores puedan encontrar centros de buceo, cursos, eventos, viajes, etc.
		</p>
	</div>
</div>

<div class="container">
	<!-- Example row of columns -->
	<div class="row">
		<div class="col-md-12" id="confirm">
			<h2>Te hemos enviado un email para que nos confirmes tu correo electrónico</h2>
		</div>
	</div>

	<hr>

	<footer>
		<p>2016 Your Company Ltd &copy;</p>
	</footer>
</div>

<?php template('blocks/end'); ?>