<?php
/* FILE: Widget showing claims. */

/** Show the claim widget. */
function _claim_widget($args) {
    extract($args);

    $options = get_option('widget_claim_listing');

    # Get our title
    $title = empty($options['title']) ? __('Comments Elsewhere') : $options['title'];

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

    _claim_widget_show_claims($number);

    echo $after_widget; 
}

/** Dump a row for each claim. */
function _claim_widget_show_claims($maxClaims) {
    $wpdb = &$GLOBALS['wpdb'];
    $wpdb->show_errors();

    $table = $wpdb->prefix . 'claims';

    # Run the query
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY time LIMIT $maxClaims");

    if (sizeof($rows) > 0) {
        print '<ul class="claim-list">';
    }

    $i = 0;
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
    
    if (sizeof($rows) > 0) {
        print '</ul>';
    }
}



function _claim_widget_control() {
    $options = $newoptions = get_option('widget_claim_listing');

    if ( $_POST["widget-claim-submit"] ) {
        $newoptions['title'] = strip_tags(stripslashes($_POST["widget-claim-title"]));
        $newoptions['number'] = (int) $_POST["widget-claim-number"];
    }
    if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('widget_claim_listing', $options);
        wp_delete_recent_comments_cache();
    }

    $title = attribute_escape($options['title']);
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
        <input type="hidden" id="widget-claim-submit" name="widget-claim-submit" value="1" />
<?php
}

?>
