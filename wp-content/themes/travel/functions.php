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
    $checkin = travel_parse_date(isset($_GET['checkin']) ? $_GET['checkin'] : '');
    $checkout = travel_parse_date(isset($_GET['checkout']) ? $_GET['checkout'] : '');

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

function travel_fetch_region($query, $locale, $domain, $headers) {
    $query = trim((string) $query);
    if ($query === '') {
        return new WP_Error('travel_missing_query', 'Search query missing.');
    }

    $cache_key = 'travel_region_' . md5($locale . '|' . $domain . '|' . strtolower($query));
    $cached = get_transient($cache_key);
    if ($cached && is_array($cached)) {
        return $cached;
    }

    $search_terms = array_merge([$query], travel_region_fallback_terms($query));

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
        return [];
    }

    $api_key = travel_get_rapidapi_key();
    if (!$api_key) {
        return new WP_Error('travel_missing_key', 'RapidAPI key is not configured.');
    }

    $headers = [
        'x-rapidapi-key' => $api_key,
        'x-rapidapi-host' => travel_get_rapidapi_host(),
    ];

    $locale = travel_get_rapidapi_locale();
    $domain = travel_get_rapidapi_domain();

    list($checkin, $checkout) = travel_get_search_dates();
    $adults = isset($_GET['adults']) ? (int) $_GET['adults'] : 1;
    if ($adults < 1) {
        $adults = 1;
    }
    if ($adults > 7) {
        $adults = 7;
    }

    $cache_key = 'travel_hotels_' . md5($query . '|' . $checkin . '|' . $checkout . '|' . $adults . '|' . $locale . '|' . $domain);
    $cached = get_transient($cache_key);
    if ($cached && is_array($cached)) {
        return $cached;
    }

    $region = travel_fetch_region($query, $locale, $domain, $headers);
    if (is_wp_error($region)) {
        return $region;
    }

    $params = [
        'price_max' => isset($_GET['price_max']) ? (int) $_GET['price_max'] : 500,
        'locale' => $locale,
        'available_filter' => 'SHOW_AVAILABLE_ONLY',
        'star_rating_ids' => isset($_GET['star_rating_ids']) ? sanitize_text_field($_GET['star_rating_ids']) : '3,4,5',
        'page_number' => 1,
        'payment_type' => isset($_GET['payment_type']) ? sanitize_text_field($_GET['payment_type']) : '',
        'children_ages' => isset($_GET['children_ages']) ? sanitize_text_field($_GET['children_ages']) : '',
        'adults_number' => $adults,
        'domain' => $domain,
        'region_id' => $region['id'],
        'checkout_date' => $checkout,
        'sort_order' => isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'REVIEW',
        'lodging_type' => isset($_GET['lodging_type']) ? sanitize_text_field($_GET['lodging_type']) : 'HOTEL,HOSTEL,APART_HOTEL',
        'checkin_date' => $checkin,
        'amenities' => isset($_GET['amenities']) ? sanitize_text_field($_GET['amenities']) : '',
        'guest_rating_min' => isset($_GET['guest_rating_min']) ? (int) $_GET['guest_rating_min'] : 0,
        'price_min' => isset($_GET['price_min']) ? (int) $_GET['price_min'] : 10,
        'meal_plan' => isset($_GET['meal_plan']) ? sanitize_text_field($_GET['meal_plan']) : '',
    ];

    $params = array_filter($params, function ($value) {
        return $value !== '' && $value !== null;
    });

    $data = travel_remote_get('/v3/hotels/search', $params, $headers);
    if (is_wp_error($data)) {
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
