<?php

/* FILE: Perform changes and updates to the claim table. */


require_once('../../../wp-config.php');

if (!current_user_can('edit_posts')) {
    die('insufficient authority');
}

$wpdb = &$GLOBALS['wpdb'];
$wpdb->show_errors();

$table = $wpdb->prefix . 'claims';

switch($_REQUEST['action']) {
    case 'approve':
        $id = $_REQUEST['claim'];
        check_admin_referer('claim-approve_' . $id);

        $id = $wpdb->escape($id);
        $wpdb->query("UPDATE $table SET state='approved' WHERE claim_ID='$id'");
    break;
    
    case 'unapprove':
        $id = $_REQUEST['claim'];
        check_admin_referer('claim-unapprove_' . $id);

        $id = $wpdb->escape($id);
        $wpdb->query("UPDATE $table SET state='unapproved' WHERE claim_ID='$id'");
    break;
}

header("Location: " . urldecode($_REQUEST['to']));

?>
