<?php
if ( ! array_key_exists('_form_key', $_SESSION) ) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 16; $i++) $str .= $chars[mt_rand(0, $lc)];
    $_SESSION['_form_key'] = $str;
}

return $_SESSION['_form_key'];
