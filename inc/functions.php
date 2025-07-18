<?php
// Core functions for filtering, AJAX handlers, etc.

// Example: function to get candidate meta
function tadbeer_get_candidate_meta($post_id, $key) {
    return get_post_meta($post_id, $key, true);
}

// Add more functions as needed
