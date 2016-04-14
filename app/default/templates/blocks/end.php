<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

<?php if ( is_form() ) : ?>
    <script src="<?=asset('js/form.js')?>"></script>
<?php endif; ?>

<?php stats_pixel(); ?>
</body>
</html>
