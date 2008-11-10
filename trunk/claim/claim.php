<?php
/*
Plugin Name: Claim
Plugin URI: http://piepalace.ca/blog/projects/claim
Description: Allow other sites to register comments you have created. 
Version: 0.1.0 (Anguished Anteater) 
Author: Erigami Scholey-Fuller
Author URI: http://piepalace.ca/blog/
*/

/* FILE: Entrypoint into chameleon. */

/*
SQL:
CREATE TABLE `wp_claims` (
    `claim_ID` bigint(20) unsigned NOT NULL auto_increment,
    `ip` varchar(15) NOT NULL,
    `title` varchar(100) NOT NULL, 
    `blog_name` varchar(100) NOT NULL,
    `blog_url` varchar(256) NOT NULL,
    `type` varchar(10) NOT NULL,
    `item` varchar(255) NOT NULL, 
    `email` varchar(255) NOT NULL, 
    `excerpt` varchar(255) NOT NULL, 
    `url` varchar(255) NOT NULL, 
    `local` boolean NOT NULL, 
    `time` datetime NOT NULL,
    `state` ENUM('unapproved', 'spam', 'approved', 'denied'),
    `user_id` bigint(20) NOT NULL,
    PRIMARY KEY (`claim_ID`),
    KEY `ip` (`ip`), 
    KEY `time` (`time`),
    KEY `state` (`state`),
    KEY `user_id` (`user_id`),
    KEY `local` (`local`)
);
*/

/**
 * Return counts for the known claims. 
 */
function &claim_tally() {
    global $wpdb;
    $table = $wpdb->prefix . "claims";

    $unapproved = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE state='unapproved'");

    $o = array('unapproved' => $unapproved);

    return (object)$o;
}


/** Called to display the admin page. */
function _clm_add_manage_page() {
    $adminFile = dirname(__FILE__) . '/manage.php';
    require_once($adminFile);

    add_submenu_page('edit.php', __('Claims'), __('Claims'), 'edit_posts', '_clm_show_manage_page', '_clm_show_manage_page');
}

/** Called when a comment's status changes. */
function _clm_comment_post($id, $newState) {
    if (strcmp($newState, 'approve') == 0 || $newState === 1) {
        clm_loadSib('/comment.php');
        _clm_comment_submit_claim($id);
    }
}

/** Called to insert our link tag. */
function _clm_wp_head() {
?>
    <link rel="claim" href="<?= bloginfo('wpurl') ?>/wp-content/plugins/claim/request.php" title="Claim URL"/>
    <link type="text/css" rel="stylesheet" href="<?= bloginfo('wpurl') ?>/wp-content/plugins/claim/ui/style.css"/>
<?php
}

function clm_loadSib($name) {
    $adminFile = dirname(__FILE__) . '/' . $name;
    require_once($adminFile);
}

add_action('admin_menu', '_clm_add_manage_page');
add_action('wp_set_comment_status', '_clm_comment_post', 10, 3);
add_action('comment_post', '_clm_comment_post', 10, 3);
add_action('wp_head', '_clm_wp_head');
add_action('admin_head', '_clm_wp_head');


function _clm_comment_form() {
?>
<div id="claim-brag"><small><?= __('If your website is <a href="http://piepalace.ca/blog/projects/claim">claim enabled</a>, it will be notified that you have posted here.')?></small></div>
<?php
}
add_action('comment_form', '_clm_comment_form');

/** Install the claim widget. */
function _claim_plugins_loaded() {
    $widgetFile = dirname(__FILE__) . '/claim-widget.php';
    require($widgetFile);

    wp_register_sidebar_widget('claim-listing-widget', 
        __('Comments Elsewhere'), '_claim_widget', 
        array('description' => 'Lists your comments on other blogs'));

    wp_register_widget_control('claim-listing-widget', 
        __('Comments Elsewhere'), '_claim_widget_control');

}
add_action('plugins_loaded', '_claim_plugins_loaded');


/** Show the dashboard notification of new claims. */
function _claim_notifier_dashboard() {
    clm_loadSib('/manage.php');

    _claim_notifier_dashboard_show();
}
add_action( 'dashmenu', '_claim_notifier_dashboard' );


?>
