<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

enqueue_frontend_assets();

// printf(
// 	'<p>%s</p>',
// 	sprintf(
// 		__('It looks like you\'ve not accepted the <strong>%s</strong> Privacy Policy yet for GDPR compliance.', 'mini-wp-gdpr'),
// 		esc_html(get_bloginfo('name'))
// 	)
// );

echo '<p class="mini-gdpr-form">';
echo get_accept_gdpr_checkbox_outer_html();
echo '</p>';
