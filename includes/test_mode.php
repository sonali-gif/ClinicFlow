<?php
if (!defined('TEST_LOGIN_BYPASS')) {
    define('TEST_LOGIN_BYPASS', true);
}
if (!defined('TEST_BYPASS_PASSWORD')) {
    define('TEST_BYPASS_PASSWORD', 'testpass');
}
if (!defined('TEST_SESSION_BYPASS')) {
    define('TEST_SESSION_BYPASS', true);
}
function testLoginBypass($inputPassword) {
    if (!TEST_LOGIN_BYPASS) {
        return false;
    }
    return $inputPassword === TEST_BYPASS_PASSWORD;
}
?>
