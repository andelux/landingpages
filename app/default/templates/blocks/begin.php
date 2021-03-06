<!DOCTYPE html>
<html lang="<?=LP_LANGUAGE?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$title?></title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

    <?php if ( isset($css_file) ) : ?>
    <link rel="stylesheet" href="<?=asset("css/{$css_file}")?>">
    <?php endif; ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="<?=asset('js/stats.js')?>"></script>

    <script>
        var LP_LANGUAGE = '<?=LP_LANGUAGE?>';
        var LP_BASE_URI = '<?=LP_BASE_URI?>';
        var LP_APP_URI = '<?=LP_APP_URI?>';
        var LP_DEFAULT_APP_URI = '<?=LP_DEFAULT_APP_URI?>';
        var LP_TEMPLATE = '<?=$config->getData('template_name')?>';
        var LP_VARIATION = <?=$config->getData('template_variation')?"'{$config->getData('template_variation')}'":'null'?>;
    </script>
</head>
<body<?=isset($body_class)?(' class="'.addslashes($body_class).'"'):''?>>
