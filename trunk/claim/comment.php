<?php
/* FILE: Handles new comments. Makes them claimable. */


/** Called to indicate that the given comment deserves a claim. */
function _clm_comment_submit_claim($id, $newStatus) {
    $comment = get_comment($id, 'OBJECT');

    // Verify that we have enough information to claim
    $blog = $comment->comment_author_url;
    
    if (strlen($blog) < 8) {
        // Nope. Doesn't look like a decent URL
        return;
    }

    // Get the URL of the claim service
    clm_loadSib('network.php');

    $claimUrl = _clm_claim_discover($blog);

    if (!$claimUrl) {
        // There's no URL
        return;
    }

    // Send the claim
    $post = get_post($comment->comment_post_ID);
    $request = array(
        'title' => $post->post_title, 
        'blog_name' => get_bloginfo('name'),
        'type' => 'comment',
        'item' => '',
        'email' => $comment->comment_author_email,
        'excerpt' => substr(strip_tags($comment->comment_content), 0, 255),
        'url' => get_permalink($comment->comment_post_ID) 
                        . '#comment-' . $comment->comment_ID
    );

    $request['charset'] = get_option('blog_charset');

    foreach (array_keys($request) as $key) {
        $request[$key] = urlencode($request[$key]);
    }

    $claimUrl .= '?';
    
    $first = true;
    foreach ($request as $key => $value) {
        if (!$first) {
            $claimUrl .= '&';
        }
        $first = false;

        $claimUrl .= ($key . '=' . $value);
    }

    $r = @file($claimUrl);
    // We should check the return type here. But why bother?
}
?>
