<?php
/**
 * Travel theme setup and assets.
 */

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'travel'),
        'footer' => __('Footer Menu', 'travel'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'travel-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'travel-site',
        get_theme_file_uri('/assets/css/site.css'),
        ['travel-fonts'],
        $theme_version
    );

    wp_enqueue_script(
        'travel-main',
        get_theme_file_uri('/assets/js/main.js'),
        [],
        $theme_version,
        true
    );
});

add_filter('body_class', function ($classes) {
    $classes[] = 'travel-shell';
    return $classes;
});

function travel_fallback_menu() {
    echo '<ul class="menu">';
    wp_list_pages(['title_li' => '']);
    echo '</ul>';
}

function travel_get_deals_link() {
    $category = get_category_by_slug('hotel-deals');
    if ($category) {
        return get_category_link($category->term_id);
    }

    $posts_page = (int) get_option('page_for_posts');
    if ($posts_page) {
        return get_permalink($posts_page);
    }

    return home_url('/');
}

function travel_get_rapidapi_key() {
    if (defined('TRAVEL_RAPIDAPI_KEY') && TRAVEL_RAPIDAPI_KEY) {
        return TRAVEL_RAPIDAPI_KEY;
    }

    return '58cf7feee1mshe0fbc631637a6e4p1a5242jsn22bf7a6335bb';
}

function travel_get_rapidapi_host() {
    if (defined('TRAVEL_RAPIDAPI_HOST') && TRAVEL_RAPIDAPI_HOST) {
        return TRAVEL_RAPIDAPI_HOST;
    }

    $host = getenv('TRAVEL_RAPIDAPI_HOST');
    return $host ? $host : 'hotels-com-provider.p.rapidapi.com';
}

function travel_get_rapidapi_locale() {
    if (defined('TRAVEL_RAPIDAPI_LOCALE') && TRAVEL_RAPIDAPI_LOCALE) {
        return TRAVEL_RAPIDAPI_LOCALE;
    }

    $locale = getenv('TRAVEL_RAPIDAPI_LOCALE');
    if (!$locale) {
        $locale = get_locale();
    }

    return $locale ? $locale : 'en_US';
}

function travel_get_rapidapi_domain() {
    if (defined('TRAVEL_RAPIDAPI_DOMAIN') && TRAVEL_RAPIDAPI_DOMAIN) {
        return TRAVEL_RAPIDAPI_DOMAIN;
    }

    $domain = getenv('TRAVEL_RAPIDAPI_DOMAIN');
    return $domain ? $domain : 'US';
}

function travel_get_hotels_base_url() {
    if (defined('TRAVEL_HOTELS_BASE_URL') && TRAVEL_HOTELS_BASE_URL) {
        return TRAVEL_HOTELS_BASE_URL;
    }

    $base = getenv('TRAVEL_HOTELS_BASE_URL');
    if ($base) {
        return $base;
    }

    return 'https://www.hotels.com';
}

function travel_get_query_value($key) {
    if (!isset($_GET[$key])) {
        return '';
    }

    $value = wp_unslash($_GET[$key]);
    if (is_array($value)) {
        return '';
    }

    return sanitize_text_field($value);
}

function travel_get_query_array($key) {
    if (!isset($_GET[$key])) {
        return [];
    }

    $value = wp_unslash($_GET[$key]);
    if (is_array($value)) {
        return array_values(array_filter(array_map('sanitize_text_field', $value)));
    }

    $value = sanitize_text_field($value);
    if ($value === '') {
        return [];
    }

    $parts = array_map('trim', explode(',', $value));
    $parts = array_values(array_filter($parts));
    return $parts;
}

function travel_get_query_list($key, array $allowed = []) {
    $values = travel_get_query_array($key);
    if (!$values) {
        return '';
    }

    $values = array_map('strtoupper', $values);

    if ($allowed) {
        $values = array_values(array_intersect($values, $allowed));
    }

    $values = array_values(array_unique($values));
    return implode(',', $values);
}

function travel_get_query_int($key, $default, $min, $max) {
    if (!isset($_GET[$key])) {
        return $default;
    }

    $raw = travel_get_query_value($key);
    if ($raw === '') {
        return $default;
    }

    $value = (int) $raw;
    if ($value < $min) {
        return $min;
    }
    if ($value > $max) {
        return $max;
    }
    return $value;
}

function travel_get_query_locale($default) {
    $locale = travel_get_query_value('locale');
    if (!$locale) {
        return $default;
    }

    $locale = str_replace('-', '_', $locale);
    $locale = preg_replace('/[^A-Za-z_]/', '', $locale);

    if (preg_match('/^[A-Za-z]{2}_[A-Za-z]{2}$/', $locale)) {
        $locale = strtolower(substr($locale, 0, 2)) . '_' . strtoupper(substr($locale, 3, 2));
        return $locale;
    }

    return $default;
}

function travel_get_query_domain($default) {
    $domain = strtoupper(travel_get_query_value('domain'));
    if ($domain && preg_match('/^[A-Z]{2}$/', $domain)) {
        return $domain;
    }

    return $default;
}

function travel_get_children_ages_param() {
    if (!isset($_GET['children_ages'])) {
        return '';
    }

    $raw = wp_unslash($_GET['children_ages']);
    if (is_array($raw)) {
        $raw = implode(',', $raw);
    }

    $raw = sanitize_text_field($raw);
    $raw = preg_replace('/[^0-9,]/', '', $raw);
    $raw = trim($raw, ',');
    return $raw;
}

function travel_set_hotel_debug(array $debug) {
    $GLOBALS['travel_hotel_debug'] = $debug;
}

function travel_get_hotel_debug() {
    return isset($GLOBALS['travel_hotel_debug']) && is_array($GLOBALS['travel_hotel_debug'])
        ? $GLOBALS['travel_hotel_debug']
        : [];
}

function travel_get_cache_dir() {
    $upload_dir = wp_upload_dir(null, false);
    if (!is_array($upload_dir) || empty($upload_dir['basedir'])) {
        return '';
    }

    return trailingslashit($upload_dir['basedir']) . 'travel-cache';
}

function travel_get_cache_file() {
    $dir = travel_get_cache_dir();
    if (!$dir) {
        return '';
    }

    return trailingslashit($dir) . 'hotel-results.json';
}

function travel_read_cache() {
    $file = travel_get_cache_file();
    if (!$file || !file_exists($file)) {
        return [];
    }

    $contents = file_get_contents($file);
    if ($contents === false || $contents === '') {
        return [];
    }

    $data = json_decode($contents, true);
    return is_array($data) ? $data : [];
}

function travel_write_cache(array $data) {
    $dir = travel_get_cache_dir();
    $file = $dir ? trailingslashit($dir) . 'hotel-results.json' : '';
    if (!$dir || !$file) {
        return;
    }

    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $json = wp_json_encode($data);
    if ($json === false) {
        return;
    }

    file_put_contents($file, $json);
}

function travel_hotel_cache_key(array $item) {
    $key_parts = [
        isset($item['link']) ? $item['link'] : '',
        isset($item['name']) ? $item['name'] : '',
        isset($item['location']) ? $item['location'] : '',
    ];

    return md5(implode('|', $key_parts));
}

function travel_cache_hotel_results(array $items) {
    $items = array_values(array_filter($items, function ($item) {
        return is_array($item) && !empty($item['name']);
    }));

    if (!$items) {
        return;
    }

    $cache = travel_read_cache();
    $stored = isset($cache['items']) && is_array($cache['items']) ? $cache['items'] : [];

    $indexed = [];
    foreach ($stored as $item) {
        if (!is_array($item)) {
            continue;
        }
        $indexed[travel_hotel_cache_key($item)] = $item;
    }

    foreach ($items as $item) {
        $indexed[travel_hotel_cache_key($item)] = $item;
    }

    $merged = array_values($indexed);
    if (count($merged) > 200) {
        $merged = array_slice($merged, 0, 200);
    }

    travel_write_cache([
        'updated' => gmdate('c'),
        'items' => $merged,
    ]);
}

function travel_get_cached_hotel_results($limit = 20) {
    $cache = travel_read_cache();
    $items = isset($cache['items']) && is_array($cache['items']) ? $cache['items'] : [];
    if (!$items) {
        return [];
    }

    shuffle($items);

    $limit = (int) $limit;
    if ($limit < 1) {
        return [];
    }

    return array_slice($items, 0, $limit);
}

function travel_parse_date($value) {
    if (!$value || !is_string($value)) {
        return '';
    }

    if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
        return '';
    }

    $timestamp = strtotime($value . ' 00:00:00 UTC');
    if (!$timestamp) {
        return '';
    }

    return gmdate('Y-m-d', $timestamp);
}

function travel_get_search_dates() {
    $checkin_input = travel_get_query_value('checkin_date');
    if ($checkin_input === '') {
        $checkin_input = travel_get_query_value('checkin');
    }
    $checkout_input = travel_get_query_value('checkout_date');
    if ($checkout_input === '') {
        $checkout_input = travel_get_query_value('checkout');
    }
    $checkin = travel_parse_date($checkin_input);
    $checkout = travel_parse_date($checkout_input);

    $checkin_ts = $checkin ? strtotime($checkin . ' 00:00:00 UTC') : strtotime('+30 days');
    if (!$checkin_ts) {
        $checkin_ts = strtotime('+30 days');
    }

    $checkout_ts = $checkout ? strtotime($checkout . ' 00:00:00 UTC') : strtotime('+7 days', $checkin_ts);
    if (!$checkout_ts || $checkout_ts <= $checkin_ts) {
        $checkout_ts = strtotime('+7 days', $checkin_ts);
    }

    return [
        gmdate('Y-m-d', $checkin_ts),
        gmdate('Y-m-d', $checkout_ts),
    ];
}

function travel_remote_get($path, array $params, array $headers) {
    $host = travel_get_rapidapi_host();
    $url = 'https://' . $host . $path;
    $url = add_query_arg($params, $url);

    $response = wp_remote_get($url, [
        'timeout' => 12,
        'headers' => $headers,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    if ($status < 200 || $status >= 300) {
        return new WP_Error('travel_api_error', 'Hotel API request failed.', [
            'status' => $status,
        ]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (!is_array($data)) {
        return new WP_Error('travel_api_invalid', 'Hotel API response was invalid.');
    }

    return $data;
}

function travel_extract_region_candidate($data) {
    $lists = [];

    if (isset($data['data']) && is_array($data['data'])) {
        $lists[] = $data['data'];

        if (isset($data['data']['regions']) && is_array($data['data']['regions'])) {
            $lists[] = $data['data']['regions'];
        }

        if (isset($data['data']['body']) && is_array($data['data']['body']) && isset($data['data']['body']['regions']) && is_array($data['data']['body']['regions'])) {
            $lists[] = $data['data']['body']['regions'];
        }
    }

    if (isset($data['regions']) && is_array($data['regions'])) {
        $lists[] = $data['regions'];
    }

    if (isset($data['sr']) && is_array($data['sr'])) {
        $lists[] = $data['sr'];
    }

    if (isset($data['suggestions']) && is_array($data['suggestions']) && isset($data['suggestions'][0]['entities']) && is_array($data['suggestions'][0]['entities'])) {
        $lists[] = $data['suggestions'][0]['entities'];
    }

    foreach ($lists as $list) {
        foreach ($list as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $id = '';
            foreach (['gaiaId', 'regionId', 'destinationId', 'id'] as $key) {
                if (!empty($candidate[$key])) {
                    $id = (string) $candidate[$key];
                    break;
                }
            }

            if (!$id && isset($candidate['regionId'])) {
                $id = (string) $candidate['regionId'];
            }

            if ($id) {
                $name = '';
                if (isset($candidate['regionNames']) && is_array($candidate['regionNames'])) {
                    $name = $candidate['regionNames']['fullName'] ?? $candidate['regionNames']['shortName'] ?? '';
                }
                if (!$name && isset($candidate['name'])) {
                    $name = $candidate['name'];
                }
                if (!$name && isset($candidate['shortName'])) {
                    $name = $candidate['shortName'];
                }

                return [
                    'id' => $id,
                    'name' => $name,
                ];
            }
        }
    }

    return [];
}

function travel_fetch_region($query, $locale, $domain, $headers, $search_terms = null) {
    $query = trim((string) $query);
    if ($query === '') {
        return new WP_Error('travel_missing_query', 'Search query missing.');
    }

    $cache_key = 'travel_region_' . md5($locale . '|' . $domain . '|' . strtolower($query));
    $cached = get_transient($cache_key);
    if ($cached && is_array($cached)) {
        return $cached;
    }

    if ($search_terms === null) {
        $search_terms = array_merge([$query], travel_region_fallback_terms($query));
    }

    foreach ($search_terms as $term) {
        $data = travel_remote_get('/v2/regions', [
            'locale' => $locale,
            'domain' => $domain,
            'query' => $term,
        ], $headers);

        if (is_wp_error($data)) {
            continue;
        }

        $region = travel_extract_region_candidate($data);
        if (!empty($region['id'])) {
            $region['term'] = $term;
            set_transient($cache_key, $region, 6 * HOUR_IN_SECONDS);
            return $region;
        }
    }

    return new WP_Error('travel_region_missing', 'Region search did not return a match.');
}

function travel_region_fallback_terms($query) {
    $query = strtolower((string) $query);
    $fallbacks = [];

    if (preg_match('/\\bbeach(es)?\\b|\\bcoast\\b|\\bisland\\b/', $query)) {
        $fallbacks = ['Cancun', 'Maldives', 'Bali'];
    } elseif (preg_match('/\\bworld\\b|\\bglobal\\b|\\banywhere\\b|\\beverywhere\\b/', $query)) {
        $fallbacks = ['London', 'Tokyo', 'New York'];
    }

    return $fallbacks;
}

function travel_extract_hotel_items($data) {
    $lists = [];

    if (isset($data['data']) && is_array($data['data'])) {
        if (isset($data['data']['properties']) && is_array($data['data']['properties'])) {
            $lists[] = $data['data']['properties'];
        }

        if (isset($data['data']['propertySearch']['properties']) && is_array($data['data']['propertySearch']['properties'])) {
            $lists[] = $data['data']['propertySearch']['properties'];
        }

        if (isset($data['data']['body']['searchResults']['results']) && is_array($data['data']['body']['searchResults']['results'])) {
            $lists[] = $data['data']['body']['searchResults']['results'];
        }

        if (isset($data['data']['searchResults']['results']) && is_array($data['data']['searchResults']['results'])) {
            $lists[] = $data['data']['searchResults']['results'];
        }
    }

    if (isset($data['searchResults']['results']) && is_array($data['searchResults']['results'])) {
        $lists[] = $data['searchResults']['results'];
    }

    if (isset($data['results']) && is_array($data['results'])) {
        $lists[] = $data['results'];
    }

    return $lists ? $lists[0] : [];
}

function travel_normalize_hotel_item(array $item) {
    $name = $item['name'] ?? $item['propertyName'] ?? $item['hotelName'] ?? '';

    $price = '';
    if (isset($item['price']['priceSummary']['definition']['displayPrice'])) {
        $price = $item['price']['priceSummary']['definition']['displayPrice'];
    } elseif (isset($item['price']['priceSummary']['displayPrices']) && is_array($item['price']['priceSummary']['displayPrices'])) {
        foreach ($item['price']['priceSummary']['displayPrices'] as $display_price) {
            if (isset($display_price['price']['formatted'])) {
                $price = $display_price['price']['formatted'];
                break;
            }
        }
    } elseif (isset($item['price']['lead']['formatted'])) {
        $price = $item['price']['lead']['formatted'];
    } elseif (isset($item['ratePlan']['price']['current'])) {
        $price = $item['ratePlan']['price']['current'];
    } elseif (isset($item['price']['options'][0]['formatted'])) {
        $price = $item['price']['options'][0]['formatted'];
    } elseif (isset($item['price']['displayMessages'][0]['lineItems'][0]['price']['formatted'])) {
        $price = $item['price']['displayMessages'][0]['lineItems'][0]['price']['formatted'];
    }

    $rating = '';
    if (isset($item['guestRating']['rating'])) {
        $rating = $item['guestRating']['rating'];
    } elseif (isset($item['reviews']['score'])) {
        $rating = $item['reviews']['score'];
    } elseif (isset($item['guestReviews']['rating'])) {
        $rating = $item['guestReviews']['rating'];
    }

    $review_count = '';
    if (isset($item['guestRating']['phrases']) && is_array($item['guestRating']['phrases'])) {
        foreach ($item['guestRating']['phrases'] as $phrase) {
            if (!is_string($phrase)) {
                continue;
            }
            if (preg_match('/([0-9,]+)\\s+reviews?/i', $phrase, $matches)) {
                $review_count = str_replace(',', '', $matches[1]);
                break;
            }
        }
    } elseif (isset($item['reviews']['total'])) {
        $review_count = $item['reviews']['total'];
    } elseif (isset($item['guestReviews']['total'])) {
        $review_count = $item['guestReviews']['total'];
    }

    $location = '';
    if (isset($item['messages'][0]) && is_string($item['messages'][0])) {
        $location = $item['messages'][0];
    } elseif (isset($item['neighborhood']['name'])) {
        $location = $item['neighborhood']['name'];
    } elseif (isset($item['neighborhood'])) {
        $location = $item['neighborhood'];
    } elseif (isset($item['address']['locality'])) {
        $location = $item['address']['locality'];
    } elseif (isset($item['address']['city'])) {
        $location = $item['address']['city'];
    } elseif (isset($item['regionNames']['fullName'])) {
        $location = $item['regionNames']['fullName'];
    }

    $image = '';
    if (isset($item['mediaSection']['media'][0]['url'])) {
        $image = $item['mediaSection']['media'][0]['url'];
    } elseif (isset($item['propertyImage']['image']['url'])) {
        $image = $item['propertyImage']['image']['url'];
    } elseif (isset($item['optimizedThumbUrls']['srpDesktop'])) {
        $image = $item['optimizedThumbUrls']['srpDesktop'];
    } elseif (isset($item['optimizedThumbUrls']['srpDesktopEnlarged'])) {
        $image = $item['optimizedThumbUrls']['srpDesktopEnlarged'];
    }

    $link = $item['landingPageUrl'] ?? $item['propertyUrl'] ?? $item['link'] ?? $item['url'] ?? '';
    $external = false;
    if ($link) {
        if (strpos($link, '/') === 0) {
            $link = rtrim(travel_get_hotels_base_url(), '/') . $link;
        }
        $external = true;
    }

    if (!$link && $name) {
        $link = add_query_arg('s', $name, home_url('/'));
    }

    return [
        'name' => (string) $name,
        'price' => (string) $price,
        'rating' => (string) $rating,
        'reviews' => (string) $review_count,
        'location' => (string) $location,
        'image' => (string) $image,
        'link' => (string) $link,
        'external' => $external,
    ];
}

function travel_get_hotel_results($query) {
    $query = trim((string) $query);
    $query = sanitize_text_field($query);
    if ($query === '') {
        travel_set_hotel_debug([]);
        return [];
    }

    $api_key = travel_get_rapidapi_key();
    if (!$api_key) {
        travel_set_hotel_debug([
            'query' => $query,
            'error' => 'RapidAPI key is not configured.',
        ]);
        return new WP_Error('travel_missing_key', 'RapidAPI key is not configured.');
    }

    $headers = [
        'x-rapidapi-key' => $api_key,
        'x-rapidapi-host' => travel_get_rapidapi_host(),
    ];

    $locale = travel_get_query_locale(travel_get_rapidapi_locale());
    $domain = travel_get_query_domain(travel_get_rapidapi_domain());

    list($checkin, $checkout) = travel_get_search_dates();
    $adults = travel_get_query_int('adults_number', 1, 1, 7);
    if (!isset($_GET['adults_number']) && isset($_GET['adults'])) {
        $adults = travel_get_query_int('adults', 1, 1, 7);
    }

    $region_override = 0;
    if (isset($_GET['region_id'])) {
        $region_override = (int) travel_get_query_value('region_id');
        if ($region_override < 1) {
            $region_override = 0;
        }
    }

    $region_terms = array_merge([$query], travel_region_fallback_terms($query));

    $debug = [
        'query' => $query,
        'locale' => $locale,
        'domain' => $domain,
        'checkin_date' => $checkin,
        'checkout_date' => $checkout,
        'adults_number' => $adults,
        'region_terms' => $region_terms,
    ];
    $debug['cache_file'] = travel_get_cache_file();

    if ($region_override) {
        $region = [
            'id' => (string) $region_override,
            'name' => '',
            'term' => 'manual',
        ];
        $debug['region_source'] = 'manual';
    } else {
        $region = travel_fetch_region($query, $locale, $domain, $headers, $region_terms);
        if (is_wp_error($region)) {
            $debug['error'] = $region->get_error_message();
            travel_set_hotel_debug($debug);
            return $region;
        }
        $debug['region_source'] = 'lookup';
    }

    $debug['region'] = $region;

    $host = travel_get_rapidapi_host();
    if (!empty($region['term']) && $region['term'] !== 'manual') {
        $debug['region_request_url'] = add_query_arg([
            'locale' => $locale,
            'domain' => $domain,
            'query' => $region['term'],
        ], 'https://' . $host . '/v2/regions');
    }

    $allowed_payment_types = ['GIFT_CARD', 'PAY_LATER', 'FREE_CANCELLATION'];
    $allowed_lodging_types = ['HOSTAL', 'APARTMENT', 'APART_HOTEL', 'CHALET', 'HOTEL', 'RYOKAN', 'BED_AND_BREAKFAST', 'HOSTEL'];
    $allowed_amenities = [
        'SPA_ON_SITE',
        'WIFI',
        'HOT_TUB',
        'FREE_AIRPORT_TRANSPORTATION',
        'POOL',
        'GYM',
        'OCEAN_VIEW',
        'WATER_PARK',
        'BALCONY_OR_TERRACE',
        'KITCHEN_KITCHENETTE',
        'ELECTRIC_CAR',
        'PARKING',
        'CRIB',
        'RESTAURANT_IN_HOTEL',
        'PETS',
        'WASHER_DRYER',
        'CASINO',
        'AIR_CONDITIONING',
    ];
    $allowed_accessibility = [
        'SIGN_LANGUAGE_INTERPRETER',
        'STAIR_FREE_PATH',
        'SERVICE_ANIMAL',
        'IN_ROOM_ACCESSIBLE',
        'ROLL_IN_SHOWER',
        'ACCESSIBLE_BATHROOM',
        'ELEVATOR',
        'ACCESSIBLE_PARKING',
    ];
    $allowed_sort = ['REVIEW', 'PRICE_LOW_TO_HIGH', 'PRICE_HIGH_TO_LOW'];

    $available_filter = strtoupper(travel_get_query_value('available_filter'));
    $available_filter = $available_filter === 'SHOW_AVAILABLE_ONLY' ? $available_filter : '';

    $star_ratings = travel_get_query_array('star_rating_ids');
    if (!$star_ratings) {
        $star_ratings = ['3', '4', '5'];
    }
    $star_ratings = array_values(array_unique(array_filter($star_ratings, function ($value) {
        $value = (int) $value;
        return $value >= 1 && $value <= 5;
    })));
    $star_rating_ids = $star_ratings ? implode(',', $star_ratings) : '';

    $payment_type = travel_get_query_list('payment_type', $allowed_payment_types);
    $lodging_type = travel_get_query_list('lodging_type', $allowed_lodging_types);
    if (!$lodging_type) {
        $lodging_type = 'HOTEL,HOSTEL,APART_HOTEL';
    }
    $amenities = travel_get_query_list('amenities', $allowed_amenities);
    $accessibility = travel_get_query_list('accessibility', $allowed_accessibility);
    $meal_plan = travel_get_query_list('meal_plan', ['FREE_BREAKFAST']);

    $sort_order = strtoupper(travel_get_query_value('sort_order'));
    if (!in_array($sort_order, $allowed_sort, true)) {
        $sort_order = 'REVIEW';
    }

    $page_number = travel_get_query_int('page_number', 1, 1, 500);
    $price_max = travel_get_query_int('price_max', 5000, 1, 1000000);
    $price_min = travel_get_query_int('price_min', 10, 0, 1000000);
    $guest_rating_min = travel_get_query_int('guest_rating_min', 8, 7, 9);
    $children_ages = travel_get_children_ages_param();

    $params = [
        'price_max' => $price_max,
        'locale' => $locale,
        'available_filter' => $available_filter,
        'star_rating_ids' => $star_rating_ids,
        'page_number' => $page_number,
        'payment_type' => $payment_type,
        'children_ages' => $children_ages,
        'adults_number' => $adults,
        'domain' => $domain,
        'region_id' => $region['id'],
        'checkout_date' => $checkout,
        'sort_order' => $sort_order,
        'lodging_type' => $lodging_type,
        'checkin_date' => $checkin,
        'amenities' => $amenities,
        'guest_rating_min' => $guest_rating_min,
        'price_min' => $price_min,
        'accessibility' => $accessibility,
        'meal_plan' => $meal_plan,
    ];

    $params = array_filter($params, function ($value) {
        return $value !== '' && $value !== null;
    });

    $debug['hotel_params'] = $params;
    $debug['hotel_request_url'] = add_query_arg($params, 'https://' . $host . '/v3/hotels/search');

    $cache_key = 'travel_hotels_' . md5(wp_json_encode($params));
    $cached = get_transient($cache_key);
    if ($cached && is_array($cached)) {
        $debug['cached'] = true;
        if (isset($cached['items']) && is_array($cached['items'])) {
            $debug['result_count'] = count($cached['items']);
            travel_cache_hotel_results($cached['items']);
        }
        travel_set_hotel_debug($debug);
        return $cached;
    }

    $data = travel_remote_get('/v3/hotels/search', $params, $headers);
    if (is_wp_error($data)) {
        $debug['error'] = $data->get_error_message();
        $debug['error_data'] = $data->get_error_data();
        travel_set_hotel_debug($debug);
        return $data;
    }

    $items = [];
    foreach (travel_extract_hotel_items($data) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $normalized = travel_normalize_hotel_item($item);
        if (!$normalized['name']) {
            continue;
        }
        $items[] = $normalized;
    }

    $cache_items = array_slice($items, 0, 60);
    travel_cache_hotel_results($cache_items);

    $debug['result_count'] = count($items);
    travel_set_hotel_debug($debug);

    $results = [
        'query' => $query,
        'region' => $region,
        'checkin' => $checkin,
        'checkout' => $checkout,
        'items' => array_slice($items, 0, 8),
    ];

    set_transient($cache_key, $results, 15 * MINUTE_IN_SECONDS);

    return $results;
}
