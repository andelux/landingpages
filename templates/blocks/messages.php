<?php
$error_messages = \LandingPages\Mvc::getSession()->getErrorMessages();
$success_messages = \LandingPages\Mvc::getSession()->getSuccessMessages();
?>

<?php if ( $success_messages ) : ?>
    <div class="alert alert-success" role="alert">
        <?php foreach ( $success_messages as $message ) : ?>
            <p><?=$message?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ( $error_messages ) : ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ( $error_messages as $message ) : ?>
            <p><?=$message?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
