<?php

/**
 *  ____                           ____  _             _
 * |  _ \ _____      _____ _ __   |  _ \| |_   _  __ _(_)_ __  ___
 * | |_) / _ \ \ /\ / / _ \ '__|  | |_) | | | | |/ _` | | '_ \/ __|
 * |  __/ (_) \ V  V /  __/ |     |  __/| | |_| | (_| | | | | \__ \
 * |_|   \___/ \_/\_/ \___|_|     |_|   |_|\__,_|\__, |_|_| |_|___/
 *                                               |___/
 *
 * What is this:   PP-Core
 * in more detail: The core framework for Power Plugins, with useful core
 *                 tools for settings & meta-box management, a splash of
 *                 minimal branding and some MVC stuff.
 *
 *  - Make sure to set the namespace to the correct namespace for this
 *    Power Plugin.
 *  - If you don't set __NAMESPACE__\SUPPORT_URL to the plugin's main URL,
 *    PP-Core will derive the URL: __NAMESPACE__\PP_HOST_PLUGIN_NAME
 *
 * Legals **********************************************************************
 *
 * This file (pp-core.php), and the support files in the pp-assets directory,
 * are covered by the MIT Licence, which may be different to the licence for the
 * rest of this software package. This means you can take pp-core.php and the
 * assets, hack about wit hthem and use them in your own projects. Just don't
 * expect any support if you run into problems.
 *
 * That said, feel free to drop us an email at mailto:hello@power-plugins.com
 * we're always up for a chat.
 *
 * Begin license text **********************************************************
 *
 * Copyright 2025 Create Element Ltd. (UK)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * End license text ************************************************************
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

const PP_CORE_NAME    = 'pwpl';
const PP_CORE_VERSION = '1.13.10';
const PP_CORE_DATE    = '2025-04-12';

define( __NAMESPACE__ . '\\PP_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( __NAMESPACE__ . '\\PP_BASE_URL', plugin_dir_url( __FILE__ ) );

define( __NAMESPACE__ . '\\PP_ASSETS_DIR', trailingslashit( PP_BASE_DIR . 'pp-assets' ) );
define( __NAMESPACE__ . '\\PP_ASSETS_URL', trailingslashit( PP_BASE_URL . 'pp-assets' ) );

const PP_DEFAULT_POST_CACHE_SIZE = 20;

const PP_QUICK_POPUP_TTL = 1000;

const PP_BOT_REGEX = '/Unknown Bot|^Ruby|Crawlson|^facebookexternalhit|WordPress|^SeobilityBot|BotLink|bingbot|AhrefsBot|ahoy|Twitterbot|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|PetalBot|Neevabot|coccocbot|applebot|DataForSeoBot|YandexImages|MojeekBot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|DomainStatsBot|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|Screaming Frog SEO|fastcrawler|FDSE|FELIX IDE|ESI|fido|H�m�h�kki|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|^wget|^curl|^HTTPie|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|SeznamBot|mod_pagespeed|Go-http-client|^dcrawl|lua-resty-http|^Python|^Statically|^got\s|^Slack|CCBot|openstat.ru\/Bot|m2e/i';

const PP_SPINNER_CSS_CLASS = 'pp-spinner';

const PP_RADIO_TYPE_TEXT  = 'text';
const PP_RADIO_TYPE_IMAGE = 'image';

function get_next_control_id() {
	global $pp_control_index;

	if ( is_null( $pp_control_index ) ) {
		$pp_control_index = 1;
	}

	$control_id = 'ppctx' . $pp_control_index;

	++$pp_control_index;

	return $control_id;
}

function is_woocommerce_available() {
	return function_exists( 'WC' );
}

/**
 * Utility functions.
 */
function pp_is_user_agent_a_bot() {
	global $pp_is_a_bot;

	if ( is_null( $pp_is_a_bot ) ) {
		$pp_is_a_bot = false;

		if ( ! empty( ( $user_agent = $_SERVER['HTTP_USER_AGENT'] ) ) ) {
			$pp_is_a_bot = preg_match( PP_BOT_REGEX, $user_agent ) > 0;
		}

		$pp_is_a_bot = (bool) apply_filters( 'pp_is_client_a_bot', $pp_is_a_bot, $user_agent );
	}

	return $pp_is_a_bot;
}

function pp_insert_into_array_before_key( array $source_array, string $key, $new_element ) {
	// Get position of the seach key
	if ( array_key_exists( $key, $source_array ) ) {
		$position = array_search( $key, array_keys( $source_array ) );
	} else {
		$position = 0;
	}

	// Extract array parts before and after proposed position
	$before = array_slice( $source_array, 0, $position, true );
	$after  = array_slice( $source_array, $position, null, true );

	// Merge arrays and return
	return array_merge( $before, $new_element, $after );
}

function pp_insert_into_array_after_key( array $source_array, string $key, $new_element ) {
	// Get position of the seach key
	if ( array_key_exists( $key, $source_array ) ) {
		$position = array_search( $key, array_keys( $source_array ) ) + 1;
	} else {
		$position = count( $source_array );
	}

	// Extract array parts before and after proposed position
	$before = array_slice( $source_array, 0, $position, true );
	$after  = array_slice( $source_array, $position, null, true );

	// Merge arrays and return
	return array_merge( $before, $new_element, $after );
}

function pp_generate_random_alpha_string( int $length ) {
	$random_string = '';

	$characters        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );

	$length = max( 1, min( 1024, $length ) );

	for ( $index = 0; $index < $length; ++$index ) {
		$random_string .= $characters[ rand( 0, $characters_length - 1 ) ];
	}

	return $random_string;
}

function pp_generate_random_string( int $length, bool $only_alphanumeric = false ) {
	$random_string = '';

	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_____----0123456789';
	if ( $only_alphanumeric ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
	}

	$characters_length = strlen( $characters );

	$length = max( 1, min( 1024, $length ) );

	for ( $index = 0; $index < $length; ++$index ) {
		$random_string .= $characters[ rand( 0, $characters_length - 1 ) ];
	}

	return $random_string;
}

function parse_html_tag( string $tag ) {
	$tag_meta = array(
		'raw'        => $tag,
		'tag'        => '',
		'attributes' => null,
		'classes'    => null,
	);

	$length = strlen( $tag );
	$chars  = str_split( $tag );

	if ( $length < 3 ) {
		error_log( __FUNCTION__ . ' Not enough text to be a valid tag' );
	} elseif ( $chars[0] != '<' ) {
		error_log( __FUNCTION__ . ' First character is not less-than' );
	} elseif ( $chars[ $length - 1 ] != '>' ) {
		error_log( __FUNCTION__ . ' First character is not greater-than' );
	} else {
		$tag_name       = null;
		$raw_attributes = null;
		$matches        = null;
		if ( preg_match( '/^<[a-zA-Z]+/', $tag, $matches, PREG_OFFSET_CAPTURE ) && strlen( $matches[0][0] ) > 1 ) {
			$tag_name       = substr( $matches[0][0], 1 );
			$raw_attributes = substr( $tag, $matches[0][1] + strlen( $tag_name ) + 1 );
			$raw_attributes = substr( $raw_attributes, 0, -1 );
		}

		if ( empty( $tag_name ) ) {
			error_log( __FUNCTION__ . ' Failed to determine the tag name' );
		} else {
			$tag_meta['tag']        = $tag_name;
			$tag_meta['attributes'] = parse_string_for_key_value_pairs( $raw_attributes );
		}

		if ( array_key_exists( 'class', $tag_meta['attributes'] ) && ! empty( $tag_meta['attributes']['class']['value'] ) ) {
			$tag_meta['classes'] = array_map( 'trim', explode( ' ', $tag_meta['attributes']['class']['value'] ) );
		}
	}

	if ( ! is_array( $tag_meta['attributes'] ) ) {
		$tag_meta['attributes'] = array();
	}

	if ( ! is_array( $tag_meta['classes'] ) ) {
		$tag_meta['classes'] = array();
	}

	return $tag_meta;
}

function write_tag_html_from_meta( array $tag_meta ) {
	$html  = '<';
	$html .= $tag_meta['tag'];

	foreach ( $tag_meta['attributes'] as $attribute_name => $attribute_meta ) {
		$html .= sprintf( ' %s=%s%s%s', $attribute_name, $attribute_meta['delimiter'], $attribute_meta['value'], $attribute_meta['delimiter'] );
	}

	$html .= '>';

	return $html;
}

function parse_string_for_key_value_pairs( string $source ) {
	$key_value_pairs = array();

	$padded_source = ' ' . $source . ' ';
	$chars         = str_split( $padded_source );
	$length        = count( $chars );

	$index            = 0;
	$last_space_index = 0;
	$valid_delimiters = array( '"', "'" );
	$key_name         = null;

	while ( $index < $length ) {
		$char       = $chars[ $index ];
		$char_lower = strtolower( $char );

		if ( empty( $key_name ) ) {
			if ( $char == ' ' ) {
				$last_space_index = $index;
			} elseif ( $char != '=' ) {
				// Not interested.
			} else {
				$key_name = substr( $padded_source, $last_space_index + 1, $index - $last_space_index - 1 );

				++$index;
				$char = $chars[ $index ];
				if ( ! empty( $valid_delimiters ) && in_array( $char, $valid_delimiters ) ) {
					$value_delimiter = $char;
					// ++$index;
				} else {
					$value_delimiter = ' ';
				}

				$key_value_pairs[ $key_name ] = array(
					'delimiter' => $value_delimiter,
					'value'     => '',
				);
			}
		} elseif ( $char == $value_delimiter ) {
				$key_name = null;
		} else {
			if ( $char == '\\' ) {
				$key_value_pairs[ $key_name ]['value'] .= $char;
				++$index;

				$char = $chars[ $index ];
			}

			$key_value_pairs[ $key_name ]['value'] .= $char;
		}

		++$index;
	}

	return $key_value_pairs;
}

function pp_get_now() {
	global $pp_date_time_now;

	if ( is_null( $pp_date_time_now ) ) {
		$pp_date_time_now = new \DateTime( 'now', wp_timezone() );
	}

	return $pp_date_time_now;
}

function pp_get_now_h( string $format = 'Y-m-d H:i:s T' ) {
	$now = pp_get_now();

	return $now->format( $format );
}

function pp_time_span_h( ?\DateTime $reference, ?\DateTime $now = null ) {
	$label = '--';

	if ( empty( $now ) ) {
		$now = new \DateTime( 'now', wp_timezone() );
	}

	if ( empty( $now ) ) {
		// ...
	} elseif ( empty( $reference ) ) {
		// ...
	} else {
		// time periods
		$second    = 1; // 1 second in Unix Time
		$minute    = $second * 60; // 1 minut in Unix Time
		$hour      = $minute * 60; // 1 hour in Unix Time
		$day       = $hour * 24; // 1 day in Unix Time
		$week      = $day * 7; // 1 week in Unix Time
		$month     = $day * 30; // 1 month in Unix Time
		$year      = $day * 365; // 1 year in Unix Time
		$decade    = $year * 10; // 10 years in Unix Time
		$century   = $decade * 10; // 100 years in Unix Time
		$millenium = $century * 10; // 1,000 years in Unix Time

		// initialize vars to store our time and name
		$name  = '';
		$total = 0;

		// get seconds since the date given to the function
		$since = $now->getTimestamp() - $reference->getTimestamp();

		// create array to store textual representation and Unix second representations
		$checks = array(
			array( 'second', $second ),
			array( 'minute', $minute ),
			array( 'hour', $hour ),
			array( 'day', $day ),
			array( 'week', $week ),
			array( 'month', $month ),
			array( 'year', $year ),
			array( 'decade', $decade ),
			array( 'century', $century ),
			array( 'millenium', $millenium ),
		);

		$is_gt_required = false;

		// iterate through checks to find the largest value for relative time display
		foreach ( $checks as $key => $check ) {
			$seconds = $check[1];
			$count   = floor( $since / $seconds );
			// if we can't go any further, exit loop
			if ( $count < 1 ) {
				if ( $check[0] == 'month' && $total == 1 ) {
					$total = round( $since / DAY_IN_SECONDS );
					$name  = 'day';
				}
				break;
				// if we can, update the name and the total
			} else {
				$name           = $check[0];
				$total          = $count;
				$is_gt_required = $key > 2;
			}
		}

		// begin creating our return value by adding total
		$label = $total;

		// if it's greater than 1
		if ( $total > 1 ) {
			// if this is millenia, change the pluralization accordingly
			if ( $name == 'millenium' ) {
				$label .= ' ' . 'millenia';
				// if this is centuries, change the pluralization accordingly
			} elseif ( $name == 'century' ) {
				$label .= ' ' . 'centuries';
				// otherwise just add an s
			} else {
				$label .= ' ' . $name . 's';
			}
			// if it isn't, just return the singular name
		} else {
			$label .= ' ' . $name;
		}

		if ( $is_gt_required ) {
			$label = '> ' . $label;
		}

		// append 'ago' to the return string
		// $label .= ' ago';
	}

	return $label;
}

// Remove this when PHP 8 is everywhere.
function pp_str_ends_with( string $haystack, string $needle ) {
	$length   = strlen( $needle );
	$is_found = null;

	if ( $length <= 0 ) {
		$is_found = true;
	} else {
		$is_found = substr( $haystack, -$length ) === $needle;
	}

	return $is_found;
}

function get_current_request_url() {
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

function get_this_ip_address( int $ip_protocol = 0 ) {
	global $pwpl_this_ip_address;

	if ( ! is_null( $pwpl_this_ip_address ) ) {
		// error_log('This IP already set: ' . $pwpl_this_ip_address);
	} elseif ( ! empty( ( $pwpl_this_ip_address = filter_var( get_transient( 'pwpl_this_ip_address' ), FILTER_VALIDATE_IP ) ) ) ) {
		// error_log('Found it in the cache: ' . $pwpl_this_ip_address);
	} else {
		// Make a quick outbound connection to the IP Echo service.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'http://ipecho.net/plain' );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		if ( ! defined( 'CURLOPT_IPRESOLVE' ) ) {
			// ...
		} elseif ( $ip_protocol == 4 && defined( 'CURL_IPRESOLVE_V4' ) ) {
			curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		} elseif ( $ip_protocol == 6 && defined( 'CURL_IPRESOLVE_V6' ) ) {
			curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6 );
		} else {
			// ...
		}

		$pwpl_this_ip_address = filter_var( curl_exec( $ch ), FILTER_VALIDATE_IP );

		curl_close( $ch );

		if ( empty( $pwpl_this_ip_address ) && array_key_exists( 'LOCAL_ADDR', $_SERVER ) ) {
			$pwpl_this_ip_address = filter_var( $_SERVER['LOCAL_ADDR'], FILTER_VALIDATE_IP );
		}

		if ( empty( $pwpl_this_ip_address ) && array_key_exists( 'SERVER_ADDR', $_SERVER ) ) {
			$pwpl_this_ip_address = filter_var( $_SERVER['SERVER_ADDR'], FILTER_VALIDATE_IP );
		}

		if ( empty( $pwpl_this_ip_address ) ) {
			error_log( __FUNCTION__ . ' : Failed to get this server\'s IP address' );
		}

		if ( ! empty( $pwpl_this_ip_address ) ) {
			set_transient( 'pwpl_this_ip_address', $pwpl_this_ip_address, HOUR_IN_SECONDS * 12 );
		}
	}

	return $pwpl_this_ip_address;
}

function pp_get_my_ip_addresses() {
	global $pwpl_my_ip_addresses;

	if ( ! is_null( $pwpl_my_ip_addresses ) ) {
		// ...
	} elseif ( ! empty( ( $pwpl_my_ip_addresses = get_transient( 'pwpl_my_ip_addresses' ) ) ) ) {
		// Cache hit
	} else {
		$pwpl_my_ip_addresses = array();
		$protocols            = array( 0, 4, 6 );

		foreach ( $protocols as $protocol ) {
			$ip_address = get_this_ip_address( $protocol );

			if ( empty( $ip_address ) ) {
				// ...
			} elseif ( in_array( $ip_address, $pwpl_my_ip_addresses ) ) {
				// ...
			} else {
				$pwpl_my_ip_addresses[] = $ip_address;
			}
		}

		if ( ! empty( $pwpl_my_ip_addresses ) ) {
			set_transient( 'pwpl_my_ip_addresses', $pwpl_my_ip_addresses, HOUR_IN_SECONDS * 12 );
		}
	}

	return $pwpl_my_ip_addresses;
}

if ( ! defined( 'PWPL_MY_IP_ADDRESSES' ) ) {
	define( 'PWPL_MY_IP_ADDRESSES', true );
	add_action( 'pwpluc_my_ip_addresses', '\\' . __NAMESPACE__ . '\\pp_get_my_ip_addresses' );
}

function pp_extract_ip_address( string $ip_address ) {
	$potential_address    = null;
	$raw_address          = null;
	$matches              = null;
	$extracted_ip_address = null;

	if ( empty( ( $raw_address = strtolower( trim( $ip_address ) ) ) ) ) {
		// ...
	} elseif ( empty( preg_match( '/[^0-9a-f\.\:]/', $raw_address, $matches, PREG_OFFSET_CAPTURE ) ) ) {
		$potential_address = $raw_address;
	} elseif ( ! is_array( $matches ) || count( $matches ) < 1 ) {
		// ...
	} elseif ( empty( ( $potential_address = trim( substr( $raw_address, 0, $matches[0][1] ) ) ) ) ) {
		// ...
	} else {
		// Found a ptential IP address.
	}

	if ( ! empty( $potential_address ) && ! empty( ( $potential_address = filter_var( $potential_address, FILTER_VALIDATE_IP ) ) ) ) {
		$extracted_ip_address = $potential_address;
		// error_log('Found IP address: ' . $raw_address . ' => ' . $extracted_ip_address);
	}

	return $extracted_ip_address;
}

function pp_get_browser_ip_address() {
	global $pp_browser_ip;

	if ( is_null( $pp_browser_ip ) ) {
		$sources = array( 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

		foreach ( $sources as $source ) {
			$raw_address = null;

			if ( ! array_key_exists( $source, $_SERVER ) ) {
				// ...
			} elseif ( empty( ( $raw_address = strtolower( trim( $_SERVER[ $source ] ) ) ) ) ) {
				// ...
			} elseif ( empty( ( $extracted_ip_address = pp_extract_ip_address( $raw_address ) ) ) ) {
				// ...
			} else {
				$pp_browser_ip = $extracted_ip_address;
				break;
			}
		}
	}

	return $pp_browser_ip;
}

function get_this_geo_data() {
	global $pwpl_this_geo_data;

	if ( ! is_null( $pwpl_this_geo_data ) ) {
		// ...
	} elseif ( empty( ( $pwpl_this_geo_data_raw = get_transient( 'pwpl_this_geo_data' ) ) ) ) {
		$pwpl_this_geo_data = null;
	} elseif ( ! is_array( $pwpl_this_geo_data = json_decode( $pwpl_this_geo_data_raw, true ) ) ) {
		$pwpl_this_geo_data = null;
	} elseif ( ! array_key_exists( 'geoplugin_countryCode', $pwpl_this_geo_data ) ) {
		$pwpl_this_geo_data = null;
	} elseif ( empty( $pwpl_this_geo_data['geoplugin_countryCode'] ) ) {
		$pwpl_this_geo_data = null;
	} else {
		// error_log('GeoData : Hit');
	}

	if ( ! is_null( $pwpl_this_geo_data ) ) {
		// ...
	} elseif ( ! empty( get_transient( 'pwpl_this_geo_data_last_checked' ) ) ) {
		error_log( 'Do not check for new geo data yet' );
	} else {
		// error_log('Lookup Geodata now');

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'http://geoplugin.net/json.gp' );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		try {
			if ( ! is_array( $pwpl_this_geo_data = json_decode( curl_exec( $ch ), true ) ) ) {
				$pwpl_this_geo_data = null;
			} elseif ( ! array_key_exists( 'geoplugin_countryCode', $pwpl_this_geo_data ) ) {
				$pwpl_this_geo_data = null;
			} elseif ( empty( $pwpl_this_geo_data['geoplugin_countryCode'] ) ) {
				$pwpl_this_geo_data = null;
			} else {
				// We've got a valid record.
			}
		} catch ( \Exception $e ) {
			error_log( __FUNCTION__ . ' : Failed to decode geolocation data' );
			$pwpl_this_geo_data = null;
		}
		curl_close( $ch );

		set_transient( 'pwpl_this_geo_data_last_checked', new \DateTime( 'now', wp_timezone() ), MINUTE_IN_SECONDS );

		if ( ! empty( $pwpl_this_geo_data ) ) {
			set_transient( 'pwpl_this_geo_data', json_encode( $pwpl_this_geo_data ), WEEK_IN_SECONDS );
		}
	}

	return $pwpl_this_geo_data;
}

function get_this_country() {
	global $pwpl_this_country_code;

	if ( ! is_null( $pwpl_this_country_code ) ) {
		// Already got it.
	} elseif ( ! is_woocommerce_available() ) {
		// ...
	} elseif ( empty( ( $shop_location = get_option( 'woocommerce_default_country' ) ) ) ) {
		// ...
	} elseif ( empty( ( $shop_location_parts = explode( ':', $shop_location ) ) ) ) {
		// ...
	} elseif ( ! is_string( $shop_location_parts[0] ) || strlen( $shop_location_parts[0] ) !== 2 ) {
		// ...
	} else {
		$pwpl_this_country_code = $shop_location_parts[0];
	}

	if ( ! is_null( $pwpl_this_country_code ) ) {
		// Already got it.
	} elseif ( empty( ( $geo_data = get_this_geo_data() ) ) ) {
		// ...
	} else {
		$pwpl_this_country_code = $geo_data['geoplugin_countryCode'];
	}

	return $pwpl_this_country_code;
}

function pp_is_referrer_this_site() {
	$site_url     = site_url( '/' );
	$referrer_url = wp_get_raw_referer(); // wp_get_referer();
	return ! empty( $referrer_url ) && parse_url( $site_url, PHP_URL_HOST ) === parse_url( $referrer_url, PHP_URL_HOST );
}

function pp_die_if_bad_referrer() {
	if ( ! pp_is_referrer_this_site() ) {
		die( 'Bad referrer' );
	}
}

function pp_get_public_template_path( string $template ) {
	$full_path = null;

	$scope              = 'public';
	$plugin_base_dir    = __DIR__;
	$local_template_dir = trailingslashit( $plugin_base_dir ) . $scope . '-templates' . DIRECTORY_SEPARATOR;
	$component_dir      = trailingslashit( basename( $plugin_base_dir ) );

	if ( ! empty( ( $full_path = locate_template( $component_dir . $template . '.php' ) ) ) ) {
		// Found
	} elseif ( is_readable( $test = $local_template_dir . $template ) ) {
		$full_path = $test;
	} elseif ( is_readable( $test = $local_template_dir . $template . '.php' ) ) {
		$full_path = $test;
	} else {
		// Not found.
		error_log( 'Template not found: ' . $template );
	}

	return $full_path;
}



function pp_get_click_to_copy_html( string $text, string $message_when_copied = '', bool $is_monospace = false, bool $is_full_width = false ) {
	if ( empty( $message_when_copied ) ) {
		$message_when_copied = __( 'Copied', 'power-plugins' );
	}

	$args = array(
		'textToCopy'        => $text,
		'messageWhenCopied' => $message_when_copied,
	);

	$internal_classes = array( 'text' );
	if ( $is_monospace ) {
		$internal_classes[] = 'pp-mono';
	}

	$outer_props = '';
	if ( $is_full_width ) {
		$outer_props .= ' class="w-100"';
	}

	return sprintf(
		'<span data-click-to-copy="%s" %s><span class="%s">%s</span><span class="dashicons dashicons-admin-page"></span></span>',
		esc_attr( json_encode( $args ) ),
		$outer_props,
		esc_attr( implode( ' ', $internal_classes ) ),
		esc_html( $text )
	);
}

function pp_gueess_plugin_support_url() {
	if ( defined( __NAMESPACE__ . '\\SUPPORT_URL' ) && ! empty( SUPPORT_URL ) ) {
		$support_url = SUPPORT_URL;
	} else {
		$support_url = 'https://power-plugins.com/';

		if ( defined( __NAMESPACE__ . '\\PP_HOST_PLUGIN_NAME' ) && ! empty( PP_HOST_PLUGIN_NAME ) ) {
			$support_url .= 'plugin/' . PP_HOST_PLUGIN_NAME;
		}

		define( __NAMESPACE__ . '\\SUPPORT_URL', $support_url );
	}

	return $support_url;
}

function pp_get_header_logo_html( string $support_url = '', string $support_link_tooltip = '' ) {
	if ( empty( $support_url ) ) {
		$support_url = pp_gueess_plugin_support_url();
	}

	if ( empty( $support_link_tooltip ) ) {
		$support_link_tooltip = __( 'Online Help', 'power-plugins' );
	}

	return sprintf(
		'<a href="%s" title="%s" class="pp-help-link" target="_blank"><img src="%s" /></a>',
		esc_url( $support_url ),
		esc_attr( $support_link_tooltip ),
		esc_url( PP_ASSETS_URL . 'pp-logo.png' )
	);
}

function pp_get_spinner_url() {
	return sanitize_url( (string) apply_filters( 'pp_spinner_url', PP_ASSETS_URL . 'spinner.svg' ) );
}

function pp_get_spinner_html( bool $is_visible = false ) {
	$props = '';
	if ( ! $is_visible ) {
		$props .= 'style="display:none;"';
	}

	$url = pp_get_spinner_url();

	return sprintf( '<div class="%s" %s><img src="%s" alt="loading" /></div>', esc_attr( PP_SPINNER_CSS_CLASS ), $props, esc_url( pp_get_spinner_url() ) );
}

function pp_get_button_with_spinner_html( string $label, string $button_classes = '', string $button_props = '' ) {
	$classes   = explode( ' ', $button_classes );
	$classes[] = 'button';

	return sprintf(
		'<span class="pp-button-with-spinner"><button %s class="%s">%s</button><img src="%s" style="display:none;" alt="loading" class="%s-img" /></span>',
		$button_props,
		esc_attr( implode( ' ', $classes ) ),
		esc_html( $label ),
		esc_url( PP_ASSETS_URL . 'spinner.svg' ),
		esc_attr( PP_SPINNER_CSS_CLASS )
	);
}

function pp_get_item_chooser_html( array $args, string $label, string $field_name, string $field_value, string $search_placeholder = '' ) {
	$data_attribute = 'data-pp-post-chooser';

	if ( array_key_exists( 'taxonomy', $args ) ) {
		$data_attribute = 'data-pp-term-chooser';
	}

	$html_text = '';
	if ( ! array_key_exists( 'postType', $args ) ) {
		// Do nothing
	} elseif ( is_array( $args['postType'] ) && ! empty( $args['postType'] ) ) {
		$html_text .= sprintf( '%s: %s', __( 'Content Types', 'power-plugins' ), implode( ', ', $args['postType'] ) );
	} else {
		$html_text .= sprintf( '%s: %s', __( 'Content Type', 'power-plugins' ), $args['postType'] );
	}

	$control_id = get_next_control_id();
	$html       = sprintf( '<span id="%s" %s="%s">', esc_attr( $control_id ), $data_attribute, esc_attr( json_encode( $args ) ) );

	$control_id = get_next_control_id();
	$html      .= sprintf(
		'<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" type="text" class="search-items" placeholder="%s" />',
		esc_attr( $control_id ),
		esc_html( $label ),
		esc_html( $html_text ),
		esc_attr( $control_id ),
		esc_attr( $search_placeholder )
	);

	$control_id = get_next_control_id();
	$html      .= sprintf( '<span class="list-pills"></span><input id="%s" name="%s" type="hidden" value="%s" />', esc_attr( $control_id ), esc_attr( $field_name ), esc_attr( $field_value ) );

	$html .= '</span>';

	return $html;
}

function pp_get_select_list_html( string $field_name, string $label, array $values, string $current_value = '', string $help_text = '', string $additional_classes = '' ) {
	$classes    = explode( ' ', $additional_classes );
	$control_id = get_next_control_id();

	$html = sprintf( '<label for="%s">%s</label><span class="pp-help">%s</span>', esc_attr( $control_id ), esc_html( $label ), esc_html( $help_text ) );

	$html .= sprintf( '<select id="%s" name="%s" class="%s">', esc_attr( $control_id ), esc_attr( $field_name ), esc_attr( implode( ' ', $classes ) ) );

	foreach ( $values as $value_key => $value_meta ) {
		$value_label = '';
		$is_valid    = false;
		if ( is_string( $value_meta ) ) {
			$value_label = (string) $value_meta;
			$is_valid    = true;
		} elseif ( is_array( $value_meta ) && array_key_exists( 'label', $value_meta ) ) {
			$value_label = (string) $value_meta['label'];
			$is_valid    = true;
		} else {
			error_log( __FUNCTION__ . ' Invalid options' );
		}

		if ( $is_valid ) {
			$props = '';
			if ( ! empty( $current_value ) && $current_value == $value_key ) {
				$props .= ' selected';
			}

			$html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $value_key ), $props, esc_html( $value_label ) );
		}
	}

	$html .= '</select>';

	return $html;
}

function pp_get_radio_buttons_html(
	string $field_name,
	string $label,
	array $values,
	string $current_value = '',
	string $radio_type = PP_RADIO_TYPE_TEXT,
	string $additional_classes = ''
) {
	$classes   = explode( ' ', $additional_classes );
	$classes[] = sprintf( 'pp-%s-radios', $radio_type );

	$html = sprintf( '<label>%s</label>', esc_html( $label ) );

	$html .= sprintf( '<span class="%s">', esc_attr( implode( ' ', $classes ) ) );

	foreach ( $values as $value_key => $value_meta ) {
		$value_label = '';
		$is_valid    = false;
		if ( is_string( $value_meta ) ) {
			$value_label = (string) $value_meta;
			$is_valid    = true;
		} elseif ( is_array( $value_meta ) && array_key_exists( 'label', $value_meta ) ) {
			$value_label = (string) $value_meta['label'];
			$is_valid    = true;
		} else {
			error_log( __FUNCTION__ . ' Invalid options' );
		}

		if ( $is_valid ) {
			$control_id = get_next_control_id();

			$props = '';
			if ( ! empty( $current_value ) && $current_value == $value_key ) {
				$props .= ' checked';
			}

			$html .= sprintf(
				'<span class="pp-radio-option"><input type="radio" id="%s" name="%s" value="%s" %s/><label for="%s">%s</label></span>',
				esc_attr( $control_id ),
				esc_attr( $field_name ),
				esc_attr( $value_key ),
				$props,
				esc_attr( $control_id ),
				$value_label // esc_html($value_label)
			);
		}
	}

	echo '</span>'; // .pp-text-radios | .pp-image-radios

	return $html;
}

function pp_get_checkbox_toggle_html( string $field_name, string $label, bool $is_checked = false, bool $has_following_section = false, string $additional_classes = '' ) {
	$classes = explode( ' ', $additional_classes );
	if ( ! in_array( 'pp-toggle', $classes ) ) {
		$classes[] = 'pp-toggle';
	}

	return pp_get_admin_checkbox_html( $field_name, $label, $is_checked, $has_following_section, implode( ' ', $classes ) );
}

function pp_get_admin_checkbox_html( string $field_name, string $label, bool $is_checked = false, bool $has_following_section = false, string $additional_classes = '' ) {
	$control_id = get_next_control_id();
	$props      = '';
	if ( $is_checked ) {
		$props .= ' checked';
	}

	$classes = explode( ' ', $additional_classes );
	if ( $has_following_section ) {
		$classes[] = 'cb-section';
	}

	if ( ! empty( $classes ) ) {
		$props .= sprintf( ' class="%s"', trim( esc_attr( implode( ' ', $classes ) ) ) );
	}

	$html = sprintf( '<input id="%s" name="%s" type="checkbox" %s/>', esc_attr( $control_id ), esc_attr( $field_name ), $props );

	if ( ! empty( $label ) ) {
		$html .= sprintf( '<label for="%s">%s</label>', esc_attr( $control_id ), esc_html( $label ) );
	}

	return $html;
}

function pp_get_input_html( string $field_name, string $label, array $props = array(), string $help_text = '' ) {
	$control_id = get_next_control_id();

	$html = '';

	if ( ! array_key_exists( 'type', $props ) ) {
		$props['type'] = 'text';
	}

	if ( ! empty( $label ) ) {
		$html .= sprintf( '<label for="%s">%s</label>', esc_attr( $control_id ), esc_html( $label ) );
	}

	$html .= sprintf( '<span class="pp-help">%s</span>', esc_html( $help_text ) );

	$html .= sprintf( '<input id="%s" name="%s" ', esc_attr( $control_id ), esc_attr( $field_name ) );

	foreach ( $props as $prop_name => $prop_value ) {
		$html .= sprintf( ' %s="%s"', esc_attr( $prop_name ), esc_attr( $prop_value ) );
	}

	$html .= ' />';

	return $html;
}

function pp_get_text_input_html( string $field_name, string $label, string $value = '', string $help_text = '', string $additional_classes = '' ) {
	$props = array(
		'type'  => 'text',
		'value' => $value,
	);

	$classes = explode( ' ', $additional_classes );
	if ( ! empty( $classes ) ) {
		$props['class'] = implode( ' ', $classes );
	}

	return pp_get_input_html( $field_name, $label, $props, $help_text );
}

function pp_enqueue_public_assets() {
	global $power_plugins_have_core_public_assets_been_enqueued;

	if ( is_null( $power_plugins_have_core_public_assets_been_enqueued ) ) {
		$handle = 'pp-' . PP_HOST_PLUGIN_NAME . '-pub';

		wp_enqueue_style( $handle, PP_ASSETS_URL . 'pp-public.css', array( 'dashicons' ), PP_CORE_VERSION );

		wp_enqueue_script( $handle, PP_ASSETS_URL . 'pp-public.js', array( 'jquery' ), PP_CORE_VERSION, false );

		$power_plugins_have_core_public_assets_been_enqueued = true;
	}
}

function pp_enqueue_admin_assets() {
	global $power_plugins_have_core_admin_assets_been_enqueued;

	if ( is_null( $power_plugins_have_core_admin_assets_been_enqueued ) ) {
		$power_plugins_have_core_admin_assets_been_enqueued = false;
	}

	// Are these checks necessary?
	if ( ! is_admin() || wp_doing_ajax() ) {
		// ...
	} elseif ( $power_plugins_have_core_admin_assets_been_enqueued ) {
		// ...
	} else {
		$handle = 'pp-' . PP_HOST_PLUGIN_NAME;

		wp_enqueue_style( $handle, PP_ASSETS_URL . 'pp-admin.css', null, PP_CORE_VERSION );

		$theme_base_dir = trailingslashit( get_stylesheet_directory() );
		$theme_base_uri = trailingslashit( get_stylesheet_directory_uri() );
		$theme_version  = wp_get_theme()->get( 'Version' );

		$frontend_data = array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'quickPopupTtl' => PP_QUICK_POPUP_TTL,
		);

		if ( current_user_can( 'edit_posts' ) ) {
			$frontend_data['searchPostOrTerms'] = array(
				'action' => PP_SEARCH_POSTS_OR_TERMS_ACTION,
				'nonce'  => wp_create_nonce( PP_SEARCH_POSTS_OR_TERMS_ACTION ),
			);

			$frontend_data['getPostAndTermMeta'] = array(
				'action' => PP_GET_POST_AND_TERM_META_ACTION,
				'nonce'  => wp_create_nonce( PP_GET_POST_AND_TERM_META_ACTION ),
			);
		}

		wp_enqueue_script( $handle, PP_ASSETS_URL . 'pp-admin.js', array( 'jquery', 'jquery-ui-autocomplete' ), PP_CORE_VERSION, false );

		wp_localize_script( $handle, 'pwplData', $frontend_data );

		$power_plugins_have_core_admin_assets_been_enqueued = true;
	}
}

function pp_get_post_and_term_metas() {
	if ( ! array_key_exists( 'nonce', $_POST ) || ! wp_verify_nonce( $_POST['nonce'], PP_GET_POST_AND_TERM_META_ACTION ) ) {
		die();
	}

	$search_posts_cap = 'edit_posts';

	// if (!is_user_logged_in() || !current_user_can(SETTINGS_CAP)) {
	if ( ! is_user_logged_in() || ! current_user_can( $search_posts_cap ) ) {
		die();
	}

	$response_code = 200;
	$response_data = array(
		'posts'      => array(),
		'taxonomies' => array(),
	);

	if ( array_key_exists( 'postIds', $_POST ) && is_array( $_POST['postIds'] ) && ! empty( ( $post_ids = array_map( 'intval', $_POST['postIds'] ) ) ) ) {
		$args = array(
			'post_type'   => get_post_types(), // 'any',
			'post__in'    => $post_ids,
			'numberposts' => -1,
		);

		// error_log('Post IDS: ' . json_encode($post_ids));

		if ( is_array( $posts = get_posts( $args ) ) ) {
			foreach ( $posts as $post ) {
				$post_id  = $post->ID;
				$post_key = strval( $post->ID );

				if ( ! array_key_exists( $post_key, $response_data['posts'] ) ) {
					$response_data['posts'][ $post_key ] = array(
						'id'    => $post_id,
						'title' => get_the_title( $post_id ),
						'slug'  => $post->post_name,
					);
				}
			}
		}
	}

	if ( array_key_exists( 'taxonomies', $_POST ) && is_array( $_POST['taxonomies'] ) ) {
		$requested_taxonomies = $_POST['taxonomies'];
		foreach ( $requested_taxonomies as $taxonomy_slug => $term_ids ) {
			if ( is_array( $term_ids ) ) {
				$term_ids = array_map( 'intval', $term_ids );
				foreach ( $term_ids as $term_id ) {
					$term_key = strval( $term_id );
					$term     = get_term_by( 'ID', $term_id, $taxonomy_slug );

					if ( ! empty( $term ) ) {
						if ( ! array_key_exists( $taxonomy_slug, $response_data['taxonomies'] ) ) {
							$response_data['taxonomies'][ $taxonomy_slug ] = array();
						}

						$response_data['taxonomies'][ $taxonomy_slug ][ $term_key ] = array(
							'id'    => $term->term_id,
							'slug'  => $term->slug,
							'title' => $term->name,
						);
					}
				}
			}
		}
	}

	wp_send_json( $response_data, $response_code );
}

function pp_search_posts_or_terms() {
	if ( ! array_key_exists( 'nonce', $_POST ) || ! wp_verify_nonce( $_POST['nonce'], PP_SEARCH_POSTS_OR_TERMS_ACTION ) ) {
		die();
	}

	$search_posts_cap = 'edit_posts';

	// if (!is_user_logged_in() || !current_user_can(SETTINGS_CAP)) {
	if ( ! is_user_logged_in() || ! current_user_can( $search_posts_cap ) ) {
		die();
	}

	// error_log('Search posts or terms');

	$response_code = 400;
	$response_data = array(
		'posts'      => array(),
		'taxonomies' => array(),
	);

	if ( ! array_key_exists( 'searchType', $_POST ) || empty( ( $search_type = $_POST['searchType'] ) ) ) {
		error_log( __FUNCTION__ . ' : Bad search request' );
	} elseif ( ! array_key_exists( 'searchQuery', $_POST ) || empty( ( $search_query = sanitize_text_field( $_POST['searchQuery'] ) ) ) ) {
		error_log( __FUNCTION__ . ' : Missing search query' );
	} elseif ( $search_type == 'posts' ) {
		$is_post_type_ok = false;

		if ( ! array_key_exists( 'postType', $_POST ) ) {
			// ...
		} elseif ( is_string( $_POST['postType'] ) ) {
			$is_post_type_ok = post_type_exists( $_POST['postType'] );
		} elseif ( is_array( $_POST['postType'] ) ) {
			$good_count = 0;
			foreach ( $_POST['postType'] as $test_post_type ) {
				if ( post_type_exists( $test_post_type ) ) {
					++$good_count;
				}
			}

			$is_post_type_ok = $good_count > 0 && $good_count === count( $_POST['postType'] );
		} else {
			// ...
		}

		// if (!array_key_exists('postType', $_POST) || !post_type_exists($_POST['postType'])) {
		if ( ! $is_post_type_ok ) {
			error_log( __FUNCTION__ . ' : Post type not found: ' . json_encode( $_POST['postType'] ) );
		} else {
			$args = array(
				'post_type'   => $_POST['postType'],
				'numberposts' => -1,
				'post_status' => array( 'publish', 'private' ),
				's'           => $search_query,
			);

			if ( is_array( $posts = get_posts( $args ) ) ) {
				foreach ( $posts as $post ) {
					$post_id = $post->ID;
					$key     = strval( $post->ID );

					if ( ! array_key_exists( $key, $response_data['posts'] ) ) {
						$response_data['posts'][ $key ] = array(
							'id'    => $post_id,
							'title' => get_the_title( $post_id ),
							'slug'  => $post->post_name,
						);
					}
				}
			}
		}

		$response_code = 200;
	} elseif ( $search_type == 'terms' ) {
		if ( ! array_key_exists( 'taxonomy', $_POST ) || empty( ( $taxonomy = get_taxonomy( $_POST['taxonomy'] ) ) ) ) {
			error_log( __FUNCTION__ . ' : Taxonomy not found: ' . sanitize_text_field( $_POST['taxonomy'] ) );
		} else {
			$args = array(
				'taxonomy'   => $taxonomy->name,
				'fields'     => 'all', // searches in the slug and returns an array of matching slugs
				'name__like' => $search_query,
				'hide_empty' => false,
			);

			$found_terms = get_terms( $args );
			// error_log('Terms: ' . count($found_terms) . ' ' . json_encode($args));
			foreach ( $found_terms as $found_term ) {
				$term_key = strval( $found_term->term_id );
				$response_data['taxonomies'][ $taxonomy->name ][ $term_key ] = array(
					'id'    => $found_term->term_id,
					'slug'  => $taxonomy->name,
					'title' => $found_term->name,
				);
			}
		}

		$response_code = 200;
	} else {
		error_log( __FUNCTION__ . ' : Bad search type' );
	}

	wp_send_json( $response_data, $response_code );
}

function pp_die_if_bad_nonce_or_cap( string $action, string $required_cap, string $nonce_field = 'nonce' ) {
	if ( ! array_key_exists( $nonce_field, $_POST ) ) {
		die();
	}

	if ( ! wp_verify_nonce( $_POST[ $nonce_field ], $action ) ) {
		die();
	}

	if ( ! empty( $required_cap ) && ! is_user_logged_in() ) {
		die();
	}

	if ( ! empty( $required_cap ) && ! current_user_can( $required_cap ) ) {
		die();
	}
}

/**
 * Core component
 */
class Component {

	protected $name;
	protected $version;

	public function __construct( string $name, string $version ) {
		$this->name    = $name;
		$this->version = $version;

		if ( ! defined( __NAMESPACE__ . '\\PP_HOST_PLUGIN_NAME' ) ) {
			// error_log('Init Power Plugins');

			define( __NAMESPACE__ . '\\PP_HOST_PLUGIN_NAME', $name );
			define( __NAMESPACE__ . '\\PP_HOST_PLUGIN_VERSION', $version );

			$get_posts_and_terms_action = 'getpstsandtrms_' . $name;
			define( __NAMESPACE__ . '\\PP_GET_POST_AND_TERM_META_ACTION', $get_posts_and_terms_action );
			add_action( 'wp_ajax_' . $get_posts_and_terms_action, '\\' . __NAMESPACE__ . '\\pp_get_post_and_term_metas' );

			$search_posts_or_terms_action = 'srchpsts_' . $name;
			define( __NAMESPACE__ . '\\PP_SEARCH_POSTS_OR_TERMS_ACTION', $search_posts_or_terms_action );
			add_action( 'wp_ajax_' . $search_posts_or_terms_action, '\\' . __NAMESPACE__ . '\\pp_search_posts_or_terms' );

			// $search_terms_action = 'srchtrms_' . $name;
			// define(__NAMESPACE__ . '\\PP_SEARCH_TERMS_ACTION', $search_terms_action);
			// add_action('wp_ajax_' . $search_terms_action, '\\' . __NAMESPACE__ . '\\pp_search_terms');
		}
	}
}

abstract class Settings_Core extends Component {

	private $settings_action;
	private $settings_nonce;
	protected $settings_cap;
	protected $settings_page_name;

	public function __construct( string $name, string $version ) {
		parent::__construct( $name, $version );

		$this->settings_action    = 'svestngsact' . $name;
		$this->settings_nonce     = 'svestngsnce' . $name;
		$this->settings_cap       = 'manage_options'; // TODO: Make this configurable
		$this->settings_page_name = $name;
	}

	/**
	 * Abstract methods.
	 */
	abstract public function save_settings();

	public function get_settings_cap() {
		return $this->settings_cap;
	}

	public function set_settings_cap( string $value ) {
		$this->settings_cap = $value;
	}

	public function get_settings_page_name() {
		return $this->settings_page_name;
	}

	public function get_settings_page_url() {
		return admin_url( 'options-general.php?page=' . $this->settings_page_name );
	}

	public function render_page_title( string $page_title = '', string $support_url = '', string $support_url_tooltip = '' ) {
		if ( empty( $page_title ) ) {
			$page_title = get_admin_page_title();
		}

		printf( '<h1>%s%s</h1>', esc_html( $page_title ), pp_get_header_logo_html( $support_url, $support_url_tooltip ) );
	}

	public function open_wrap() {
		echo '<div class="wrap pp-wrap">';
	}

	public function open_form() {
		echo '<form method="post">';

		wp_nonce_field( $this->settings_action, $this->settings_nonce );
	}

	public function close_form() {
		echo '</form>'; // .pp-wrap
	}

	public function close_wrap() {
		echo '</div>'; // .pp-wrap
	}

	public function maybe_save_settings() {
		if ( ! is_admin() || wp_doing_ajax() ) {
			// ...
		} elseif ( ! array_key_exists( $this->settings_nonce, $_POST ) ) {
			// ...
		} elseif ( ! wp_verify_nonce( $_POST[ $this->settings_nonce ], $this->settings_action ) ) {
			// ...
		} elseif ( ! current_user_can( $this->settings_cap ) ) {
			// ...
		} else {
			$this->save_settings();
		}
	}

	public function get_default_value( string $option_name ) {
		$value = null;

		return $value;
	}

	public function sanitise_value( string $option_name, $value ) {
		return $value;
	}

	/**
	 * Hex colours in #RGB or #RRGGBB format.
	 */
	public function get_colour_hex( string $option_name, string $default = '' ): string {
		if ( empty( $default ) ) {
			$default = strval( $this->get_default_value( $option_name ) );
		}

		if ( ! empty( ( $value = get_option( $option_name, $default ) ) ) ) {
			$value = sanitize_hex_color( $value );
		}

		if ( strlen( $value ) != 4 && strlen( $value ) != 7 ) {
			$value = '#000000';
		}

		return $this->sanitise_value( $option_name, $value );
	}

	public function set_colour_hex( string $option_name, string $colour, $autoload = null ) {
		if ( ! empty( $colour ) ) {
			$colour = sanitize_hex_color( $colour );
		}

		if ( strlen( $colour ) != 4 && strlen( $colour ) != 7 ) {
			delete_option( $option_name );
		} else {
			update_option( $option_name, $colour, $autoload );
		}
	}

	/**
	 * Get/set array, stored as a JSON-encoded string.
	 */
	public function get_array( string $option_name, array $default = array() ): array {
		if ( empty( $default ) ) {
			$default = $this->get_default_value( $option_name );
		}

		if ( ! is_array( $default ) ) {
			$default = array();
		}

		$value = (array) get_option( $option_name, $default );

		return $this->sanitise_value( $option_name, $value );
	}

	public function set_array( string $option_name, array $value = array(), $autoload = null ) {
		if ( ! empty( $value ) ) {
			update_option( $option_name, $value, $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	/**
	 * Get/set strings.
	 */
	public function get_string( string $option_name, string $default = '' ): string {
		if ( empty( $default ) ) {
			$default = strval( $this->get_default_value( $option_name ) );
		}

		$value = strval( get_option( $option_name, $default ) );

		return $this->sanitise_value( $option_name, $value );
	}

	public function set_string( string $option_name, string $value = '', $autoload = null ) {
		if ( ! empty( $value ) ) {
			update_option( $option_name, $value, $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	public function get_bool( string $option_name, bool $default = false ): bool {
		$value = filter_var( get_option( $option_name, $default ), FILTER_VALIDATE_BOOLEAN );

		return $value;
	}

	public function set_bool( string $option_name, $value, $autoload = null ) {
		if ( $aanitised_value = filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( $option_name, 'true', $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	public function get_int( string $option_name, int $default = 0 ): int {
		if ( empty( $default ) ) {
			$default = strval( $this->get_default_value( $option_name ) );
		}

		$value = intval( get_option( $option_name, $default ) );

		return $this->sanitise_value( $option_name, $value );
	}

	public function set_int( string $option_name, int $value, $autoload = null ) {
		update_option( $option_name, $value, $autoload );
	}

	public function get_float( string $option_name, float $default = 0.0 ): float {
		if ( empty( $default ) ) {
			$default = floatval( $this->get_default_value( $option_name ) );
		}

		$value = floatval( get_option( $option_name, $default ) );

		return $this->sanitise_value( $option_name, $value );
	}

	public function set_float( string $option_name, float $value, $autoload = null ) {
		update_option( $option_name, $value, $autoload );
	}

	public function get_hook_option( string $option_name, string $default_hook, int $default_priority = 10 ) {
		$meta = null;
		if ( empty( ( $meta = get_option( $option_name ) ) ) || ! is_array( $meta ) ) {
			$meta = null;
		} elseif ( ! array_key_exists( 'hook', $meta ) ) {
			$meta = null;
		} elseif ( ! array_key_exists( 'priority', $meta ) ) {
			$meta['priority'] = 10;
		} else {
			// OK
		}

		if ( empty( $meta ) ) {
			$meta = array(
				'hook'     => $default_hook,
				'priority' => $default_priority,
			);
		}

		return $meta;
	}

	public function set_hook_option( string $option_name, string $hook_name, int $priority = 10 ) {
		if ( empty( $hook_name ) ) {
			delete_option( $option_name );
		} else {
			update_option(
				$option_name,
				array(
					'hook'     => $hook_name,
					'priority' => $priority,
				),
				true
			);
		}
	}

	public function get_datetime_string( string $option_name, string $default = '' ) {
		if ( empty( $default ) ) {
			$default = strval( $this->get_default_value( $option_name ) );
		}

		$datetime        = null;
		$datetime_string = '';

		try {
			if ( ! empty( ( $raw_value = strval( get_option( $option_name, $default ) ) ) ) ) {
				$datetime = new \DateTime( $raw_value );
			}
		} catch ( \Exception $e ) {
			$datetime = null;
		}

		try {
			if ( empty( $datetime ) && ! empty( $default ) ) {
				$datetime = new \DateTime( $default );
			}
		} catch ( \Exception $e ) {
			$datetime = null;
		}

		if ( ! empty( $datetime ) ) {
			$datetime_string = $datetime->format( 'Y-m-d H:i:s T' );
		}

		return $datetime_string;
	}

	public function set_datetime_string( string $option_name, string $option_value = '', $autoload = null ) {
		$datetime = null;

		try {
			if ( ! empty( $option_value ) ) {
				$datetime = new \DateTime( $option_value );
			}
		} catch ( \Exception $e ) {
			$datetime = null;
		}

		if ( ! empty( $datetime ) ) {
			update_option( $option_name, $datetime->format( 'Y-m-d H:i:s T' ), $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	public function get_date_string( string $option_name, string $default = '' ) {
		$value = null;

		if ( ! empty( ( $raw_date_time_string = strval( get_option( $option_name, $default ) ) ) ) ) {
			try {
				$date_time        = new \DateTime( $raw_date_time_string );
				$date_time_string = $date_time->format( 'Y-m-d H:i:s T' );
				if ( strlen( $date_time_string ) > 10 ) {
					$value = substr( $date_time_string, 0, 10 );
				}
			} catch ( \Exception $e ) {
				$value = null;
			}
		}

		return $value;
	}

	public function set_date_string( string $option_name, string $option_value = '', $autoload = null ) {
		$raw_date_time_string = $option_value;
		$option_value         = null;

		if ( ! empty( $raw_date_time_string ) ) {
			try {
				$raw_date_time_string .= ' 12:00:00';
				$date_time             = new \DateTime( $raw_date_time_string, wp_timezone() );
				$option_value          = $date_time->format( 'Y-m-d H:i:s T' );
			} catch ( \Exception $e ) {
				error_log( __FUNCTION__ . ' : ' . $e->getMessage() );
				$option_value = null;
			}
		}

		if ( ! empty( $option_value ) ) {
			update_option( $option_name, $option_value, $autoload );
		} else {
			delete_option( $option_name );
		}
	}
}

/**
 * Core Post object
 */
abstract class Post {

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}

	private $post_id;
	public function get_id(): int {
		return $this->post_id;
	}

	public function get_thumbnail_id() {
		return intval( get_post_thumbnail_id( $this->get_id() ) );
	}

	public function has_thumbnail() {
		return $this->get_thumbnail_id() > 0;
	}

	public function get_title(): string {
		return get_post_field( 'post_title', $this->post_id );
	}

	public function set_title( string $new_title ) {
		if ( ! empty( ( $this->post_id = $this->get_id() ) ) && ! empty( $new_title ) && $new_title != get_post_field( 'post_title', $this->post_id ) ) {
			$postarr = array(
				'ID'         => $this->post_id,
				'post_title' => $new_title,
			);
			wp_update_post( $postarr );
		}
	}

	public function get_slug() {
		return get_post_field( 'post_name', $this->post_id );
	}

	public function get_content() {
		$content = null;

		if ( ! empty( ( $post = get_post( $this->get_id() ) ) ) ) {
			$content = $post->post_content;
		}

		return $content;
	}

	public function get_as_array( bool $include_id = false ) {
		return array(
			'slug'  => $this->get_slug(),
			'title' => $this->get_title(),
		);
	}

	public function set_csv_column( string $column_name, array $values, string $delimiter = ',' ) {
		$values = array_unique( array_map( 'trim', array_values( array_filter( $values ) ) ) );

		if ( empty( ( $raw_data = implode( $delimiter, $values ) ) ) ) {
			delete_post_meta( $this->get_id(), $column_name );
		} else {
			update_post_meta( $this->get_id(), $column_name, $delimiter . $raw_data . $delimiter );
		}
	}

	public function get_csv_column( string $column_name, string $delimiter = ',' ): array {
		$values = null;

		if ( empty( $column_name ) ) {
			// ...
		} elseif ( empty( ( $raw_data = strval( get_post_meta( $this->get_id(), $column_name, true ) ) ) ) ) {
			// ...
		} else {
			$values = explode( $delimiter, $raw_data );
		}

		if ( ! is_array( $values ) ) {
			$values = array();
		} else {
			$values = array_unique( array_map( 'trim', array_values( array_filter( $values ) ) ) );
		}

		return $values;
	}

	public function get_bool_meta( string $key ): bool {
		return (bool) filter_var( get_post_meta( $this->get_id(), $key, true ), FILTER_VALIDATE_BOOLEAN );
	}

	public function set_bool_meta( string $key, bool $value ) {
		if ( $value ) {
			update_post_meta( $this->get_id(), $key, 1 );
		} else {
			delete_post_meta( $this->get_id(), $key );
		}
	}

	public function get_int_meta( string $key ): int {
		return intval( get_post_meta( $this->get_id(), $key, true ) );
	}

	public function set_int_meta( string $key, int $value ) {
		update_post_meta( $this->get_id(), $key, $value );
	}

	public function set_absint_meta( string $key, int $value ) {
		if ( $value > 0 ) {
			update_post_meta( $this->get_id(), $key, $value );
		} else {
			delete_post_meta( $this->get_id(), $key );
		}
	}

	public function get_string_meta( string $key ): string {
		return trim( strval( get_post_meta( $this->get_id(), $key, true ) ) );
	}

	public function set_string_meta( string $key, string $value = '' ) {
		$value = trim( $value );

		if ( ! empty( $value ) ) {
			update_post_meta( $this->get_id(), $key, $value );
		} else {
			delete_post_meta( $this->get_id(), $key );
		}
	}
}

/**
 * Core post controller.
 */
abstract class Post_Controller {

	private $post_objects;

	public function __construct( string $post_type, int $columns_priority = 20, int $max_post_object_cache_size = PP_DEFAULT_POST_CACHE_SIZE ) {
		$this->post_type = $post_type;

		$this->post_objects = array();

		$this->max_post_object_cache_size = $max_post_object_cache_size;

		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'manage_posts_columns' ), $columns_priority );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_posts_custom_column' ), $columns_priority, 2 );
	}

	/**
	 * Abstract methods.
	 */
	abstract protected function create_post_object( int $post_id );
	abstract public function manage_posts_columns( $columns );
	abstract public function manage_posts_custom_column( $column_name, $post_id );

	private $post_type;
	public function get_post_type() {
		return $this->post_type;
	}

	public function is_post_id_valid( $post = null ) {
		return ! empty( $this->get_post_id( $post ) );
	}

	protected function get_post_id( $post = null ) {
		$test_post_id = 0;
		$post_id      = 0;

		if ( is_a( $post, 'WP_Post' ) ) {
			$test_post_id = $post->ID;
		} elseif ( ! empty( $post ) ) {
			$test_post_id = intval( $post );
		} else {
			$test_post_id = get_the_ID();
		}

		if ( empty( $test_post_id ) ) {
			// ...
		} elseif ( empty( ( $post = get_post( $test_post_id ) ) ) ) {
			// ...
		} elseif ( get_post_type( $post ) != $this->post_type ) {
			// ...
		} else {
			$post_id = $test_post_id;
		}

		return $post_id;
	}

	protected function get_post_id_or_die( $post = null ) {
		$post_id = 0;

		if ( empty( ( $post_id = $this->get_post_id( $post ) ) ) ) {
			throw new \Exception( __FUNCTION__ . ' ' . $this->post_type . ' not found: ' . strval( $post ) );
		}

		return $post_id;
	}

	public function get_post_object( $post = null ) {
		$post_id = $this->get_post_id_or_die( $post );

		$post_object = null;
		if ( array_key_exists( $post_id, $this->post_objects ) ) {
			// error_log('Post from local cache');
			$post_object = $this->post_objects[ $post_id ];
		} elseif ( empty( ( $post_object = $this->create_post_object( $post_id ) ) ) ) {
			error_log( 'Failed to create ' . $this->post_type . ' post object' );
		} elseif ( $this->max_post_object_cache_size <= 0 ) {
			// error_log('Created new post object');
			// Post object cache is not enabled.
		} else {
			// error_log('Created new post object');
			if ( count( $this->post_objects ) >= $this->max_post_object_cache_size ) {
				$this->flush_post_object_cache();
			}

			$this->post_objects[ $post_id ] = $post_object;
		}

		return $post_object;
	}

	public function get_post_object_by_slug( string $slug ) {
		$post_object = null;

		if ( ! empty( ( $post = get_page_by_path( $slug, OBJECT, $this->get_post_type() ) ) ) ) {
			$post_object = $this->get_post_object( $post->ID );
		}

		return $post_object;
	}

	public function get_post_object_by_meta( string $meta_key, string $meta_value ) {
		$post_object = null;

		if ( count( $post_objects = $this->get_post_objects_by_meta( $meta_key, $meta_value, array( 'numberposts' => 1 ) ) ) === 1 ) {
			$post_object = $post_objects[0];
		}

		return $post_object;
	}

	public function get_post_objects_by_meta( string $meta_key, string $meta_value, array $wp_query_args = array() ): array {
		$post_objects = array();

		$args = wp_parse_args(
			$wp_query_args,
			array(
				'post_type'   => $this->get_post_type(),
				'post_status' => 'publish',
				'numberposts' => -1,
				'meta_key'    => $meta_key,
				'meta_value'  => $meta_value,
			)
		);

		if ( is_array( $posts = get_posts( $args ) ) ) {
			foreach ( $posts as $post ) {
				$post_objects[] = $this->get_post_object( $post->ID );
			}
		}

		return $post_objects;
	}

	public function flush_post_object_cache() {
		// error_log('Flush post object cache: ' . $this->get_post_type() . ' (' . count($this->post_objects) . ')');

		foreach ( array_keys( $this->post_objects ) as $post_id ) {
			unset( $this->post_objects[ $post_id ] );
		}
	}

	public function get_post_object_cache_size() {
		return count( $this->post_objects );
	}

	private $max_post_object_cache_size;
	public function get_max_post_object_cache_size() {
		return $this->max_post_object_cache_size;
	}

	public function set_max_post_object_cache_size( int $new_size ) {
		$this->max_post_object_cache_size = $new_size;
		$this->flush_post_object_cache();
	}

	public function create_new_post_object( array $postarr = array() ) {
		$defaults = array(
			'post_type'   => $this->get_post_type(),
			'post_status' => 'publish',
		);

		// TODO: Replace with wp_parse_args*()
		$postarr = array_merge( $postarr, $defaults );

		// error_log(print_r($postarr, true));

		$post_object = null;
		// error_log('wp_insert_post: ' . $postarr['post_type']);
		$post_id = wp_insert_post( $postarr );
		if ( is_wp_error( $wp_error = $post_id ) ) {
			error_log( $wp_error->get_error_message() );
		} else {
			$post_object = $this->create_post_object( $post_id );
		}

		return $post_object;
	}

	public function insert_post( ?array $postarr = null ) {
		error_log( __FUNCTION__ . ' Power Plugins : Deprecated : Use create_new_post_object() instead' );

		if ( ! is_array( $postarr ) ) {
			$postarr = array();
		}

		$defaults = array(
			'post_type' => $this->get_post_type(),
		);

		$postarr = wp_parse_args( $postarr, $defaults );

		$post_object = null;
		$post_id     = wp_insert_post( $postarr );
		if ( is_wp_error( $wp_error = $post_id ) ) {
			error_log( $wp_error->get_error_message() );
		} else {
			$post_object = $this->create_post_object( $post_id );
		}

		return $post_object;
	}

	public function get_all( ?array $args = null ) {
		$post_objects = array();

		if ( ! is_array( $args ) ) {
			$args = array();
		}

		$query_args = array_merge(
			$args,
			array(
				'post_type'   => $this->get_post_type(),
				'post_status' => 'publish',
			)
		);

		if ( ! array_key_exists( 'numberposts', $query_args ) ) {
			$query_args['numberposts'] = -1;
		}

		if ( is_array( $posts = get_posts( $query_args ) ) ) {
			foreach ( $posts as $post ) {
				$post_id = $post->ID;

				// $post_objects[] = $this->get_post_object($post_id);
				$post_objects[ $post_id ] = $this->get_post_object( $post_id );
			}
		}

		return $post_objects;
	}

	public function get_all_ids(): array {
		$query_args = array(
			'post_type'   => $this->get_post_type(),
			'post_status' => 'publish',
			'fields'      => 'ids',
		);

		$ids = get_posts( $query_args );

		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		return $ids;
	}
}

/**
 * Core Term object
 */
abstract class Term {

	private $wp_term;

	public function __construct( string $taxonomy, int $term_id ) {
		$this->taxonomy = $taxonomy;
		$this->term_id  = $term_id;
		$this->wp_term  = get_term_by( 'term_id', $term_id, $taxonomy );
	}

	private $taxonomy;
	public function get_taxonomy(): string {
		return $this->taxonomy;
	}

	private $term_id;
	public function get_id(): int {
		return $this->term_id;
	}

	public function get_title(): string {
		return $this->wp_term->name;
	}

	public function get_slug(): string {
		return $this->wp_term->slug;
	}

	public function has_thumbnail(): bool {
		return $this->get_thumbnail_id() > 0;
	}

	public function get_thumbnail_id(): int {
		return intval( get_term_meta( $this->get_id(), '_thumbnail_id', true ) );
	}

	public function set_thumbnail_id( int $attachment_id ) {
		if ( $attachment_id <= 0 ) {
			delete_term_meta( $this->get_id(), '_thumbnail_id' );
		} else {
			update_term_meta( $this->get_id(), '_thumbnail_id', $attachment_id );
		}
	}

	public function get_thumbnail_url(): ?string {
		$url = null;
		if ( ! empty( ( $thumbnail_id = $this->get_thumbnail_id() ) ) ) {
			$url = wp_get_attachment_url( $thumbnail_id );
		}
		return $url;
	}

	public function get_int_meta( string $key ): int {
		return intval( get_term_meta( $this->get_id(), $key, true ) );
	}

	public function set_int_meta( string $key, int $value ) {
		update_term_meta( $this->get_id(), $key, $value );
	}

	public function get_string_meta( string $key ): string {
		return trim( strval( get_term_meta( $this->get_id(), $key, true ) ) );
	}

	public function set_string_meta( string $key, string $value = '' ) {
		$value = trim( $value );

		if ( ! empty( $value ) ) {
			update_term_meta( $this->get_id(), $key, $value );
		} else {
			delete_term_meta( $this->get_id(), $key );
		}
	}

	public function get_bool_meta( string $key ): bool {
		return (bool) filter_var( get_term_meta( $this->get_id(), $key, true ), FILTER_VALIDATE_BOOLEAN );
	}

	public function set_bool_meta( string $key, bool $value ) {
		if ( $value ) {
			update_term_meta( $this->get_id(), $key, 1 );
		} else {
			delete_term_meta( $this->get_id(), $key );
		}
	}
}

/**
 * Core Term controller
 */
abstract class Term_Controller {

	private $term_objects;

	public function __construct( string $taxonomy ) {
		$this->taxonomy     = $taxonomy;
		$this->term_objects = array();

		add_filter( 'manage_edit-' . $this->taxonomy . '_columns', array( $this, 'manage_terms_columns' ), 10 );
		add_action( 'manage_' . $this->taxonomy . '_custom_column', array( $this, 'manage_terms_custom_column' ), 10, 3 );
	}

	/**
	 * Abstract methods.
	 */
	abstract protected function create_term_object( string $taxonomy, int $term_id );
	abstract public function manage_terms_columns( $columns );
	abstract public function manage_terms_custom_column( $string, $columns, $term_id );

	private $taxonomy;
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	public function get_term_object_by_slug( string $slug ) {
		$term = null;

		if ( ! empty( ( $wp_term = get_term_by( 'slug', $slug, $this->get_taxonomy() ) ) ) ) {
			$term = $this->get_term_object( $wp_term->term_id );
		}

		return $term;
	}

	public function get_term_object_by_meta( string $meta_key, string $meta_value ) {
		$term_object = null;

		if ( count( $term_objects = $this->get_term_objects_by_meta( $meta_key, $meta_value, array( 'number' => 1 ) ) ) === 1 ) {
			$term_object = $term_objects[0];
		}

		return $term_object;
	}

	public function get_term_objects_by_meta( string $meta_key, string $meta_value, array $wp_query_args = array() ): array {
		$term_objects = array();

		$args = wp_parse_args(
			$wp_query_args,
			array(
				'number'     => 0, // 0 means all terms, unlike -1 for a posts query
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			)
		);

		if ( is_array( $wp_terms = get_terms( $this->taxonomy, $args ) ) ) {
			foreach ( $wp_terms as $wp_term_id ) {
				$term_objects[] = $this->get_term_object( $wp_term_id );
			}
		}

		return $term_objects;
	}

	public function get_post_terms( int $post_id ) {
		$term_objects = array();

		if ( is_array( $wp_terms = get_the_terms( $post_id, $this->taxonomy ) ) ) {
			foreach ( $wp_terms as $wp_term ) {
				$term_objects[] = $this->get_term_object( $wp_term );
			}
		}

		return $term_objects;
	}

	public function get_terms( array $tax_query_args = array() ) {
		$terms                      = array();
		$tax_query_args['taxonomy'] = $this->get_taxonomy();
		if ( is_array( $wp_terms = get_terms( $tax_query_args ) ) ) {
			foreach ( $wp_terms as $wp_term ) {
				$terms[] = $this->get_term_object( $wp_term );
			}
		}
		return $terms;
	}

	protected function get_term_id( $term = null ) {
		$test_term_id = 0;
		$term_id      = 0;

		if ( is_a( $term, 'WP_Term' ) ) {
			$test_term_id = $term->term_id;
		} elseif ( ! empty( $term ) ) {
			$test_term_id = intval( $term );
		} else {
			// ...
		}

		if ( empty( $test_term_id ) ) {
			// ...
		} elseif ( empty( ( $wp_term = get_term( $test_term_id, $this->get_taxonomy(), OBJECT ) ) ) ) {
			// ...
		} else {
			$term_id = $test_term_id;
		}

		return $term_id;
	}

	protected function get_term_id_or_die( $term = null ) {
		$term_id = 0;

		if ( empty( ( $term_id = $this->get_term_id( $term ) ) ) ) {
			throw new \Exception( __FUNCTION__ . ' ' . $this->taxonomy . ' term not found: ' . json_encode( $term ) );
		}

		return $term_id;
	}

	public function get_term_object( $term = null ) {
		$term_id = $this->get_term_id_or_die( $term );

		$term_object = null;
		if ( array_key_exists( $term_id, $this->term_objects ) ) {
			$term_object = $this->term_objects[ $term_id ];
		} elseif ( empty( ( $term_object = $this->create_term_object( $this->taxonomy, $term_id ) ) ) ) {
			error_log( 'Failed to create ' . $this->taxonomy . ' term object' );
		} else {
			$this->term_objects[ $term_id ] = $term_object;
		}

		return $term_object;
	}

	public function create_new_term_object( string $term_name, array $args = array() ) {
		// TODO: Replace with wp_parse_args*()
		// $args = wp_parse_args($new_term_args, []);

		// error_log(print_r($postarr, true));

		$term_object = null;
		// error_log('wp_insert_post: ' . $postarr['post_type']);
		$term_meta = wp_insert_term( $term_name, $this->taxonomy, $args );
		if ( is_wp_error( $wp_error = $term_meta ) ) {
			error_log( __FUNCTION__ . ' : ' . $wp_error->get_error_message() );
		} else {
			$new_term_id = $term_meta['term_id'];
			$term_object = $this->create_term_object( $this->taxonomy, $new_term_id );
		}

		return $term_object;
	}
}

/**
 * Meta Box goodies
 */
abstract class Meta_Box {

	private $save_action;
	private $nonce_field_name;

	// public function __construct(string|array $post_types, string $save_action = '', string $nonce_field_name = '') {
	public function __construct( $post_types, string $save_action = '', string $nonce_field_name = '' ) {
		$this->save_action      = $save_action;
		$this->nonce_field_name = $nonce_field_name;

		if ( empty( $post_types ) ) {
			$this->post_types = array( 'post' );
		} elseif ( is_array( $post_types ) ) {
			$this->post_types = $post_types;
		} else {
			$this->post_types = explode( ' ', $post_types );
		}

		if ( empty( $this->save_action ) ) {
			$this->save_action = sanitize_title( get_class( $this ) ) . '_' . PP_HOST_PLUGIN_NAME . '_svembx';
		}

		if ( empty( $this->nonce_field_name ) ) {
			$this->nonce_field_name = sanitize_title( get_class( $this ) ) . '_' . PP_HOST_PLUGIN_NAME . '_nncembx';
		}
	}

	private $post_types;
	public function get_post_type() {
		error_log( PP_HOST_PLUGIN_NAME . ' : ' . __FUNCTION__ . ' : Deprecated. Use get_post_types() instead.' );

		return $this->post_types[0];
	}

	public function get_post_types() {
		return $this->post_types;
	}

	public function render_nonce_field() {
		wp_nonce_field( $this->save_action, $this->nonce_field_name );
	}

	public function is_trying_to_save_meta_box() {
		return array_key_exists( $this->nonce_field_name, $_POST ) && wp_verify_nonce( $_POST[ $this->nonce_field_name ], $this->save_action );
	}

	public function is_saving_meta_box( $post_id, $post ) {
		$is_saving = false;

		if ( ! array_key_exists( $this->nonce_field_name, $_POST ) ) {
			// ...
		} elseif ( ! wp_verify_nonce( $_POST[ $this->nonce_field_name ], $this->save_action ) ) {
			// ...
		} elseif ( ! in_array( $post->post_type, $this->post_types ) ) {
			// ...
		} elseif ( empty( ( $wp_post_type = get_post_type_object( $post->post_type ) ) ) ) {
			// ...
		} elseif ( ! current_user_can( $wp_post_type->cap->edit_post, $post_id ) ) {
			// ...
		} elseif ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			// ...
		} else {
			// error_log('Is Saving Metta Box');
			$is_saving = true;
		}

		return $is_saving;
	}
}
