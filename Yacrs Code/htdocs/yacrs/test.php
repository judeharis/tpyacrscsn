<!DOCTYPE html>
<html>
<body>

<?php
require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');
include_once('lib/lti_funcs.php');
require_once('corelib/ldap_login.php');
echo "My first PHP script!";

$uinfo=userInfo::retrieve_by_username("2229695w");
echo '<pre>'; print_r($uinfo); echo '</pre>';

$serialized_data = serialize(array('Math', 'Language', 'Science', 'asd'));
echo  $serialized_data . '<br>';

$var1 = unserialize($serialized_data);
// Show the unserialized data;
var_dump ($var1);
echo  '<br>';
$qu = question::retrieve_question('2');
$var1 = $qu->definition;
var_dump ($var1);
?>

</body>
</html>