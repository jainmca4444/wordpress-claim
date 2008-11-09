<?php
/* FILE: Called by an external site to offer a claim to an artifact  */

require_once('../../../wp-config.php');

// Verify that we're allowed to run
if (!in_array('claim/claim.php', (get_option('active_plugins')))) {
    die('Claim plugin not active');
}

$wpdb = &$GLOBALS['wpdb'];
$wpdb->show_errors();
$table = $wpdb->prefix . 'claims';

$ip = $_SERVER['REMOTE_ADDR'];

// Check for request flood
$choke = 15;

$recent = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE ip='$ip' AND time>(NOW() - " . $choke . ")");
if ($recent > 0) {
    sleep(10); // No, you can wait
    die("403 - Too many requests");
}

// Check for too many recent requests
$claimsPerDay = 20;
$recent = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE ip='$ip' AND time<(NOW() - 86400)");
if ($recent > $claimsPerDay) {
    die("403 - Too many claims");
}


// Pull out the request values
$charset = $_REQUEST['charset'];

if ($charset) {
    $charset = strtoupper( trim($charset) );
} else {
    $charset = 'ASCII, UTF-8, ISO-8859-1, JIS, EUC-JP, SJIS';
}

$title = stripslashes($_REQUEST['title']);
$blog_name = stripslashes($_REQUEST['blog_name']);
$type = stripslashes($_REQUEST['type']);
$item = stripslashes($_REQUEST['item']);
$email = stripslashes($_REQUEST['email']);
$excerpt = stripslashes($_REQUEST['excerpt']);
$url = stripslashes($_REQUEST['url']);

if ( function_exists('mb_convert_encoding') ) { // For international trackbacks
    $title = mb_convert_encoding($title, get_option('blog_charset'), $charset);
    $blog_name = mb_convert_encoding($blog_name, get_option('blog_charset'), $charset);
}

$title = $wpdb->escape(strip_tags($title));
$blog_name = $wpdb->escape(strip_tags($blog_name));
$type = $wpdb->escape(strip_tags($type));
$item = $wpdb->escape(strip_tags($item));
$email = $wpdb->escape(strip_tags($email));
$excerpt = $wpdb->escape(strip_tags($excerpt));
$url = $wpdb->escape(htmlentities($url));


// Verify the claim
if (strlen($title) < 1) {
    die("400 - Need a title");
}

if (strlen($blog_name) < 1) {
    die("400 - Need a blog_name");
}

if (strlen($type) < 1) {
    die("400 - Need a type");
}

if (strlen($email) < 1) {
    die("400 - Need an email");
}

if (strlen($excerpt) < 1) {
    die("400 - Need an excerpt");
}

if (strlen($url) < 1) {
    die("400 - Need an url");
}


// Write the claim request
$claim = array(
    'title' => $title,
    'blog_name' => $blog_name,
    'type' => $type,
    'item' => $item,
    'email' => $email,
    'excerpt' => $excerpt,
    'url' => $url,
    'ip' => $ip
);

do_action('claim_request', $claim);

$wpdb->query("INSERT INTO $table SET ip='$ip', title='$title', blog_name='$blog_name', type='$type', item='$item', email='$email', excerpt='$excerpt', url='$url', time=NOW(), state='unapproved'");

print("200 - Claim provisionally accepted");

?>
