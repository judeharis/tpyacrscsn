<?php

date_default_timezone_set('UTC');
//if((!isset($noSSLok))||($noSSLok==false)) include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once('corelib/templateMerge.php');
include('lib/login.php');
//include_once('lib/libfuncs.php');

$TEMPLATE = 'html/template.html';
$MOBILETEMPLATE = 'html/mtemplate.html';
$CFG['appname'] = 'yacrs';  //Used for cookie name, so no spaces etc.
$CFG['sitetitle'] = 'University of Glasgow - Class Response';

$CFG['smsnumber'] = '';
$CFG['sms_phone_field'] = '';
$CFG['sms_message_field'] = '';

// cookiehash is used for various codings - as well as for cookie security.
// It is best to be set to a new random value for each new installation
$CFG['cookiehash'] = "ffwds]d]fslgkj";
$CFG['cookietimelimit'] =  10800; // seconds

// LDAP server IP
$CFG['ldaphost'] = '130.209.13.173';
// LDAP context or list of contexts
$CFG['ldapcontext'] = 'o=Gla';
// LDAP Bind details
#$CFG['ldapbinduser'] = '';
#$CFG['ldapbindpass'] = '';
// LDAP fields and values that result in sessionCreator (teacher) status
$CFG['ldap_sessionCreator_rules'] = array();
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'dn', 'contains'=>'ou=staff');
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'homezipcode', 'match'=>'PGR');
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'uid', 'regex'=>'/^[a-z]{2,3}[0-9]+[a-z]$/');
//$CFG['ldap_sessionCreator_rules'][] = array('field'=>'mail', 'regex'=>'/[a-zA-Z]+\.[a-zA-Z]+.*?@glasgow\.ac\.uk/');

// Alternative login methods

// URL where users are returned after exiting a YACRS session
$CFG['defaultLogoutURL'] = 'https://www.gla.ac.uk';
$CFG['breadCrumb'] = '<ul class="breadcrumb">';

$CFG['screenshotpath'] = "userimages";

// Database settings
$DBCFG['type']='MySQL';
$DBCFG['host']="127.0.0.1"; // Host name
$DBCFG['username']="yacrs"; // Mysql username
$DBCFG['password']="yacrs"; // Mysql password
$DBCFG['db_name']="yacrs"; // Database name

//There probably needs to be someone who can set up LTI, make users into sessionCreaters etc.
//Set one username to be this - probably the LDAP username of the person setting this up.
$CFG['adminname'] = 'admin';
//Ideally don't set this field - rely on LDAP. If you're not using LDAP you'll need to set
//a password here. It can be plain text, or (prefereably) the value returned by md5($CFG['cookiehash'].'your_password');
$CFG['adminpwd'] = 'password';

?>
