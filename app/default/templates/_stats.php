<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>STATS</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
	<style>
	table { width:100%; }
	table td { padding:5px; }
	table td.right { text-align:right; }
	</style>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
    <table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>VARIATION</th>
	    <th>TYPE</th>
	    <th>CONVERSIONS</th>
	    <th>RATE</th>
    </tr>
    </thead>
    <tbody>
	    <?php foreach ( $result as $item ) : ?>
	    <tr>
		    <td><?=$item->getVariation()?></td>
		    <td><?=$item->getConversionType()?></td>
		    <td class="right"><?=$item->getConversions()?></td>
		    <td class="right"><?=sprintf('%6.2f%%',$item->getRate()/100)?></td>
	    </tr>
	    <?php endforeach; ?>
    </tbody>
    </table>
</body>
</html>