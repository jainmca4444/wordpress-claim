<?php

/** Show the claim management page */
function _clm_show_manage_page() {
    global $wpdb;
?>
<div class="wrap">
<h2>Manage Claims</h2>

<table class="widefat">
    <thead><tr>
        <th scope="col">Date</th>
        <th scope="col">Blog</th>
        <th scope="col">Type</th>
        <th scope="col">Excerpt</th>
        <th scope="col">Actions</th>
    </tr></thead>
<?php
    $table = $wpdb->prefix . "claims";
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY time");

    $i = 0;
    foreach ($rows as $row) {
        _clm_show_manage_page_row($row, 0 == ($i++ % 2));
    }
?>
</table>
</div><!-- wrap -->
<?php
}


function _clm_show_manage_page_row($row, $isAlternate) {
    $type = '';
    if ($row->type == 'comment') {
        $type = __('Comment');
    }

    $class = 'unapproved';
    switch ($row->state) {
    case 'unapproved':
        $class = 'unapproved';
        break;
    case 'spam':
        $class = 'spam';
        break;
    case 'approved':
        $class = 'approved';
        break;
    case 'denied':
        $class = 'denied';
        break;
    default:
        $status = __('Unknown'); break;
    }
?>
    <tr class="<?= $class ?> <?= $isAlternate ? '' : 'alternate'?>">
        <td><?= $row->time ?></td>
        <td><a href="<?= $row->url ?>"><?= $row->blog_name ?></a></td>
        <td><?= $row->type ?></td>
        <td><i><?= htmlentities($row->excerpt) ?></i></td>
        <td>
<?php
    $actionBase = get_bloginfo('wpurl') 
            . '/wp-content/plugins/claim/update.php?'
            . 'to=' . urlencode("http://" . $_SERVER["SERVER_NAME"]
                        . $_SERVER["REQUEST_URI"])
            . '&claim=' . $row->claim_ID;

    // Show actions
    if ($row->state != 'approved') {
        $url = clean_url(wp_nonce_url($actionBase . '&action=approve', 'claim-approve_' . $row->claim_ID));
?>
    <a href="<?= $url ?>"><?= __('Approve')?></a>
<?php
    } else {
        $url = clean_url(wp_nonce_url($actionBase . '&action=unapprove', 'claim-unapprove_' . $row->claim_ID));
?>
    <a href="<?= $url ?>"><?= __('Unapprove')?></a>
<?php
    }

    print ' | ';

    if ($row->state != 'spam') {
        print __('Spam');
    }
?>
        </td>
    </tr>
<?php
}


/** Show the notifier in the dashboard. */
function _claim_notifier_dashboard_show() {
    $tally = claim_tally();
    $o = $tally->unapproved;

    $style = '';
    $text = __('No Pending Claims');
    if ($o > 0) {
        $style = 'style="color: #FFF;"';
    }

    if ($o == 1) {
        $text = __('One Pending Claim');
    }
    else if ($o > 1) {
        $text = __("$o Pending Claims");
    }

    echo "<li><a href='" . wp_nonce_url( 'edit.php?page=_clm_show_manage_page', 'claim') . "' title='Show claims' $style>$text</a></li>";
}

?>
