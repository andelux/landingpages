<?php
/**
 * Receive the form post, and send webhooks, integrate with MailChimp, etc
 *
 */

if ( MAILCHIMP_API_KEY && MAILCHIMP_API_LIST ) {
    lib('mailchimp');

    $mailchimp = new LP_Mailchimp();

    // TODO: at this point, we need a mapping system, from form post to mailchimp variables
    $mailchimp->addMember(MAILCHIMP_API_LIST, $_POST['email'], array());
}
