<?php
$search_id = function_exists('wp_unique_id') ? wp_unique_id('search-') : uniqid('search-');
$search_id = esc_attr($search_id);

$search_query = get_search_query();
$date_defaults = function_exists('travel_get_search_dates') ? travel_get_search_dates() : ['', ''];
$checkin_value = isset($_GET['checkin_date']) ? sanitize_text_field(wp_unslash($_GET['checkin_date'])) : '';
$checkout_value = isset($_GET['checkout_date']) ? sanitize_text_field(wp_unslash($_GET['checkout_date'])) : '';
if ($checkin_value === '') {
    $checkin_value = $date_defaults[0];
}
if ($checkout_value === '') {
    $checkout_value = $date_defaults[1];
}

$adults_value = isset($_GET['adults_number']) ? (int) $_GET['adults_number'] : 1;
if ($adults_value < 1) {
    $adults_value = 1;
}
if ($adults_value > 7) {
    $adults_value = 7;
}

$children_ages_value = isset($_GET['children_ages']) ? sanitize_text_field(wp_unslash($_GET['children_ages'])) : '';
$price_min_value = isset($_GET['price_min']) ? sanitize_text_field(wp_unslash($_GET['price_min'])) : '10';
$price_max_value = isset($_GET['price_max']) ? sanitize_text_field(wp_unslash($_GET['price_max'])) : '500';
$guest_rating_value = isset($_GET['guest_rating_min']) ? sanitize_text_field(wp_unslash($_GET['guest_rating_min'])) : '8';
$page_number_value = isset($_GET['page_number']) ? sanitize_text_field(wp_unslash($_GET['page_number'])) : '1';
$region_id_value = isset($_GET['region_id']) ? sanitize_text_field(wp_unslash($_GET['region_id'])) : '';

$default_locale = function_exists('travel_get_rapidapi_locale') ? travel_get_rapidapi_locale() : 'en_US';
$default_domain = function_exists('travel_get_rapidapi_domain') ? travel_get_rapidapi_domain() : 'US';
$locale_value = isset($_GET['locale']) ? sanitize_text_field(wp_unslash($_GET['locale'])) : $default_locale;
$domain_value = isset($_GET['domain']) ? sanitize_text_field(wp_unslash($_GET['domain'])) : $default_domain;
$sort_order_value = isset($_GET['sort_order']) ? sanitize_text_field(wp_unslash($_GET['sort_order'])) : 'REVIEW';

$parse_list = function ($key) {
    if (!isset($_GET[$key])) {
        return [];
    }
    $raw = wp_unslash($_GET[$key]);
    if (is_array($raw)) {
        $items = $raw;
    } else {
        $items = explode(',', $raw);
    }
    $items = array_map('sanitize_text_field', array_map('trim', $items));
    return array_values(array_filter($items));
};

$star_selected = $parse_list('star_rating_ids');
if (!$star_selected) {
    $star_selected = ['3', '4', '5'];
}
$lodging_selected = $parse_list('lodging_type');
if (!$lodging_selected) {
    $lodging_selected = ['HOTEL', 'HOSTEL', 'APART_HOTEL'];
}
$payment_selected = $parse_list('payment_type');
if (!$payment_selected) {
    $payment_selected = ['PAY_LATER', 'FREE_CANCELLATION'];
}
$amenities_selected = $parse_list('amenities');
if (!$amenities_selected) {
    $amenities_selected = ['WIFI', 'PARKING'];
}
$accessibility_selected = $parse_list('accessibility');
$meal_plan_value = isset($_GET['meal_plan']) ? sanitize_text_field(wp_unslash($_GET['meal_plan'])) : 'FREE_BREAKFAST';
$available_only = isset($_GET['available_filter']) ? sanitize_text_field(wp_unslash($_GET['available_filter'])) === 'SHOW_AVAILABLE_ONLY' : true;
$debug_checked = isset($_GET['debug']) && $_GET['debug'];
$filters_open = function_exists('is_front_page') && is_front_page();
?>
<form role="search" method="get" class="travel-search" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text" for="<?php echo $search_id; ?>"><?php esc_html_e('Search for:', 'travel'); ?></label>
    <div class="search-input">
        <input type="search" id="<?php echo $search_id; ?>" class="search-field" placeholder="<?php esc_attr_e('Search destinations, hotels, or experiences', 'travel'); ?>" value="<?php echo esc_attr($search_query); ?>" name="s">
        <button type="submit" class="button search-submit"><?php esc_html_e('Search', 'travel'); ?></button>
    </div>
    <div class="search-fields">
        <div class="field">
            <label for="checkin-date"><?php esc_html_e('Check-in', 'travel'); ?></label>
            <input type="date" id="checkin-date" name="checkin_date" value="<?php echo esc_attr($checkin_value); ?>">
        </div>
        <div class="field">
            <label for="checkout-date"><?php esc_html_e('Check-out', 'travel'); ?></label>
            <input type="date" id="checkout-date" name="checkout_date" value="<?php echo esc_attr($checkout_value); ?>">
        </div>
        <div class="field">
            <label for="adults-number"><?php esc_html_e('Adults', 'travel'); ?></label>
            <input type="number" id="adults-number" name="adults_number" min="1" max="7" value="<?php echo esc_attr($adults_value); ?>">
        </div>
    </div>
    <p class="search-hint"><?php esc_html_e('Try: Lisbon, beach, boutique hotel', 'travel'); ?></p>

    <details class="search-filters"<?php echo $filters_open ? ' open' : ''; ?>>
        <summary><?php esc_html_e('More filters', 'travel'); ?></summary>
        <div class="filter-grid">
            <div class="filter-group">
                <h3><?php esc_html_e('Budget and rating', 'travel'); ?></h3>
                <div class="field">
                    <label for="price-min"><?php esc_html_e('Price min', 'travel'); ?></label>
                    <input type="number" id="price-min" name="price_min" min="0" max="1000000" value="<?php echo esc_attr($price_min_value); ?>" placeholder="10">
                </div>
                <div class="field">
                    <label for="price-max"><?php esc_html_e('Price max', 'travel'); ?></label>
                    <input type="number" id="price-max" name="price_max" min="1" max="1000000" value="<?php echo esc_attr($price_max_value); ?>" placeholder="500">
                </div>
                <div class="field">
                    <label for="guest-rating"><?php esc_html_e('Guest rating min', 'travel'); ?></label>
                    <input type="number" id="guest-rating" name="guest_rating_min" min="7" max="9" value="<?php echo esc_attr($guest_rating_value); ?>" placeholder="8">
                </div>
                <div class="field">
                    <label for="star-rating"><?php esc_html_e('Star ratings', 'travel'); ?></label>
                    <div class="option-grid" id="star-rating">
                        <?php foreach (['5', '4', '3', '2', '1'] as $rating) : ?>
                            <label>
                                <input type="checkbox" name="star_rating_ids[]" value="<?php echo esc_attr($rating); ?>" <?php checked(in_array($rating, $star_selected, true)); ?>>
                                <?php echo esc_html($rating); ?>+
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Stay type', 'travel'); ?></h3>
                <div class="option-grid">
                    <?php
                    $lodging_options = [
                        'HOTEL' => 'Hotel',
                        'HOSTEL' => 'Hostel',
                        'APART_HOTEL' => 'Apart hotel',
                        'APARTMENT' => 'Apartment',
                        'BED_AND_BREAKFAST' => 'B&B',
                        'CHALET' => 'Chalet',
                        'RYOKAN' => 'Ryokan',
                    ];
                    foreach ($lodging_options as $value => $label) :
                        ?>
                        <label>
                            <input type="checkbox" name="lodging_type[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $lodging_selected, true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Deals and payment', 'travel'); ?></h3>
                <div class="option-grid">
                    <?php
                    $payment_options = [
                        'PAY_LATER' => 'Pay later',
                        'FREE_CANCELLATION' => 'Free cancellation',
                        'GIFT_CARD' => 'Gift card',
                    ];
                    foreach ($payment_options as $value => $label) :
                        ?>
                        <label>
                            <input type="checkbox" name="payment_type[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $payment_selected, true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="field">
                    <label for="meal-plan"><?php esc_html_e('Meal plan', 'travel'); ?></label>
                    <select id="meal-plan" name="meal_plan">
                        <option value=""><?php esc_html_e('Any', 'travel'); ?></option>
                        <option value="FREE_BREAKFAST" <?php selected($meal_plan_value, 'FREE_BREAKFAST'); ?>><?php esc_html_e('Free breakfast', 'travel'); ?></option>
                    </select>
                </div>
                <div class="field">
                    <label>
                        <input type="checkbox" name="available_filter" value="SHOW_AVAILABLE_ONLY" <?php checked($available_only); ?>>
                        <?php esc_html_e('Show available only', 'travel'); ?>
                    </label>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Amenities', 'travel'); ?></h3>
                <div class="option-grid">
                    <?php
                    $amenities_options = [
                        'WIFI' => 'Wifi',
                        'PARKING' => 'Parking',
                        'POOL' => 'Pool',
                        'GYM' => 'Gym',
                        'SPA_ON_SITE' => 'Spa',
                        'OCEAN_VIEW' => 'Ocean view',
                        'AIR_CONDITIONING' => 'Air conditioning',
                        'RESTAURANT_IN_HOTEL' => 'Restaurant',
                        'PETS' => 'Pets',
                        'KITCHEN_KITCHENETTE' => 'Kitchen',
                    ];
                    foreach ($amenities_options as $value => $label) :
                        ?>
                        <label>
                            <input type="checkbox" name="amenities[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $amenities_selected, true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Accessibility', 'travel'); ?></h3>
                <div class="option-grid">
                    <?php
                    $accessibility_options = [
                        'ELEVATOR' => 'Elevator',
                        'ACCESSIBLE_PARKING' => 'Accessible parking',
                        'IN_ROOM_ACCESSIBLE' => 'In-room accessible',
                        'ROLL_IN_SHOWER' => 'Roll-in shower',
                        'ACCESSIBLE_BATHROOM' => 'Accessible bathroom',
                        'STAIR_FREE_PATH' => 'Stair-free path',
                        'SERVICE_ANIMAL' => 'Service animal',
                        'SIGN_LANGUAGE_INTERPRETER' => 'Sign language interpreter',
                    ];
                    foreach ($accessibility_options as $value => $label) :
                        ?>
                        <label>
                            <input type="checkbox" name="accessibility[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $accessibility_selected, true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Guests and locale', 'travel'); ?></h3>
                <div class="field">
                    <label for="children-ages"><?php esc_html_e('Children ages', 'travel'); ?></label>
                    <input type="text" id="children-ages" name="children_ages" value="<?php echo esc_attr($children_ages_value); ?>" placeholder="4,0,15">
                </div>
                <div class="field">
                    <label for="locale"><?php esc_html_e('Locale', 'travel'); ?></label>
                    <input type="text" id="locale" name="locale" value="<?php echo esc_attr($locale_value); ?>" list="locale-options" placeholder="en_US">
                    <datalist id="locale-options">
                        <option value="en_US"></option>
                        <option value="es_AR"></option>
                        <option value="en_GB"></option>
                    </datalist>
                </div>
                <div class="field">
                    <label for="domain"><?php esc_html_e('Domain', 'travel'); ?></label>
                    <input type="text" id="domain" name="domain" value="<?php echo esc_attr($domain_value); ?>" list="domain-options" placeholder="US">
                    <datalist id="domain-options">
                        <option value="US"></option>
                        <option value="AR"></option>
                        <option value="GB"></option>
                    </datalist>
                </div>
                <div class="field">
                    <label for="sort-order"><?php esc_html_e('Sort order', 'travel'); ?></label>
                    <select id="sort-order" name="sort_order">
                        <option value="REVIEW" <?php selected($sort_order_value, 'REVIEW'); ?>><?php esc_html_e('Top reviews', 'travel'); ?></option>
                        <option value="PRICE_LOW_TO_HIGH" <?php selected($sort_order_value, 'PRICE_LOW_TO_HIGH'); ?>><?php esc_html_e('Price low to high', 'travel'); ?></option>
                        <option value="PRICE_HIGH_TO_LOW" <?php selected($sort_order_value, 'PRICE_HIGH_TO_LOW'); ?>><?php esc_html_e('Price high to low', 'travel'); ?></option>
                    </select>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php esc_html_e('Advanced', 'travel'); ?></h3>
                <div class="field">
                    <label for="region-id"><?php esc_html_e('Region ID (optional)', 'travel'); ?></label>
                    <input type="number" id="region-id" name="region_id" min="1" value="<?php echo esc_attr($region_id_value); ?>" placeholder="2872">
                </div>
                <div class="field">
                    <label for="page-number"><?php esc_html_e('Page number', 'travel'); ?></label>
                    <input type="number" id="page-number" name="page_number" min="1" max="500" value="<?php echo esc_attr($page_number_value); ?>">
                </div>
                <?php if (current_user_can('manage_options')) : ?>
                    <div class="field">
                        <label>
                            <input type="checkbox" name="debug" value="1" <?php checked($debug_checked); ?>>
                            <?php esc_html_e('Show debug output', 'travel'); ?>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </details>
</form>
