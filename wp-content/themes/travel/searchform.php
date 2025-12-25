<?php
$search_id = function_exists('wp_unique_id') ? wp_unique_id('search-') : uniqid('search-');
$search_id = esc_attr($search_id);
?>
<form role="search" method="get" class="travel-search" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text" for="<?php echo $search_id; ?>"><?php esc_html_e('Search for:', 'travel'); ?></label>
    <div class="search-input">
        <input type="search" id="<?php echo $search_id; ?>" class="search-field" placeholder="<?php esc_attr_e('Search destinations, hotels, or experiences', 'travel'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s">
        <button type="submit" class="button search-submit"><?php esc_html_e('Search', 'travel'); ?></button>
    </div>
    <p class="search-hint"><?php esc_html_e('Try: Lisbon, mountain, boutique hotel', 'travel'); ?></p>
</form>
