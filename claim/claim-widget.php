<?php
/* FILE: Widget showing claims. */

/** Show the claim widget. */
function _claim_widget($args) {
    extract($args);

    $options = get_option('widget_claim_listing');

    # Get our title
    $title = isset($options['title']) ? $options['title'] : __('Comments Elsewhere');

    $showLocal = true;
    if (isset($options['show_local'])) {
        $showLocal = ("1" == $options['show_local']);
    }

    # Get the number of claims to show
    if ( !$number = (int) $options['number'] )
        $number = 5;
    else if ( $number < 1 )
        $number = 1;
    else if ( $number > 15 )
        $number = 15;

    # Display the widget
    echo $before_widget;
    echo $before_title . $title . $after_title; 

    _claim_widget_show_claims($number, $showLocal);

    echo $after_widget; 
}

/** Dump a row for each claim. */
function _claim_widget_show_claims($maxClaims, $showLocal) {
    $wpdb = &$GLOBALS['wpdb'];
    $wpdb->show_errors();

    $table = $wpdb->prefix . 'claims';

    # Run the query
    $localClause = '';
    if (!$showLocal) {
        $localClause = "WHERE local='0'";
    }
    $rows = $wpdb->get_results("SELECT * FROM $table $localClause ORDER BY time LIMIT $maxClaims");

    if (sizeof($rows) == 0) {
        _e('No known comments on other blogs');
    }

    print '<ul class="claim-list">';

    foreach ($rows as $row) {
?>
        <li class="comment-claim claim-type-<?= $row->type ?>">
                <a href="<?= $row->url ?>" rel="nofollow">
                        <span class="blog-name"><?= $row->blog_name ?></span>: 
                        <span class="title-name"><?= $row->title ?></span>
                </a>
            <div class="excerpt"><?= $row->excerpt ?></div>
        </li>
<?php
    }
    
    print '</ul>';
}



function _claim_widget_control() {
    $options = $newoptions = get_option('widget_claim_listing');

    if ( $_POST["widget-claim-submit"] ) {
        $newoptions['title'] = strip_tags(stripslashes($_POST["widget-claim-title"]));
        $newoptions['number'] = (int) $_POST["widget-claim-number"];
        $newoptions['show_local'] = (int) $_POST["widget-claim-show-local"];
    }
    if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('widget_claim_listing', $options);
        wp_delete_recent_comments_cache();
    }

    $title = attribute_escape($options['title']);
    $showLocal = $options['show_local'];

    if ( !$number = (int) $options['number'] )
        $number = 5;
?>
        <p><label for="widget-claim-title">
            <?php _e('Title:'); ?> 
            <input class="widefat" id="widget-claim-title" name="widget-claim-title" type="text" value="<?php echo $title; ?>" />
        </label></p>
        <p><label for="widget-claim-number">
            <?php _e('Number of claims to show:'); ?> 
            <input style="width: 25px; text-align: center;" id="widget-claim-number" name="widget-claim-number" type="text" value="<?php echo $number; ?>" />
        </label>
            <br />
            <small><?php _e('(at most 15)'); ?></small>
        </p>
        <p>
            <?php _e('Include comments on this blog:'); ?>
        </p>
        <div id="claim-include-local">
            <div class="option"><label>
                <input type="radio" name="widget-claim-show-local" value="1"<?php 
                    if ($showLocal == 1) {
                        echo ' checked="true"';
                    }
                ?>>
                <?= __('Show your comments on this blog and others') ?>
            </label></div>

            <div class="option"><label>
                <input type="radio" name="widget-claim-show-local" value="0"<?php
                    if ($showLocal == 0) {
                        echo ' checked="true"';
                    }
                ?>>
                <?= __('Only show your comments on other blogs') ?>
            </label></div>
        </div>

        <input type="hidden" id="widget-claim-submit" name="widget-claim-submit" value="1" />
<?php
}

?>
