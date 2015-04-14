<?php
/*
Plugin Name: Autocomplete Wizard
Version: 2.0
Plugin URI: http://getbutterfly.com/wordpress-plugins/autocomplete-wizard/
Description: <strong>Autocomplete Wizard</strong> plugin helps your users find what they are looking for better and faster. No more searching in the dark, no more 404 errors! Autocomplete your content and redirect your users.
Author: Ciprian Popescu
Author URI: http://getbutterfly.com/

Copyright 2013, 2014, 2015 Ciprian Popescu (email: getbutterfly@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('ACW_VERSION', '2.0');

function acw_scripts() {
	// purecss.io // 0.6.0 // load as 'pure'
	wp_enqueue_style('pure', plugins_url('css/pure.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'acw_scripts');

add_action('admin_menu', 'acw_plugin_menu');

function acw_activate() {
	add_option('acw_search_label', 'search');
	add_option('acw_select_placeholder', 'Search...');
}
register_activation_hook(__FILE__, 'acw_activate');

function acw_plugin_menu() {
	add_options_page(__('Autocomplete Wizard', 'acw'), __('Autocomplete Wizard', 'acw'), 'manage_options', 'acw', 'acw_plugin_options');
}

function acw_plugin_options() {
	if(isset($_POST['acw_submit'])) {
		update_option('acw_search_label', $_POST['acw_search_label']);
		update_option('acw_select_placeholder', $_POST['acw_select_placeholder']);
		update_option('acw_highlight_colour', $_POST['acw_highlight_colour']);

		echo '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
	}
	?>
	<div class="wrap">
		<h2>Autocomplete Wizard</h2>
		<div id="poststuff">
			<div class="postbox">
				<h3><?php _e('General Settings', 'acw'); ?></h3>
				<div class="inside">
					<p>You are currently using <b>Autocomplete Wizard</b> version <b><?php echo ACW_VERSION; ?></b> with <b><?php bloginfo('charset'); ?></b> charset.</p>
					<form name="form1" method="post" action="">
						<p>
							<input type="text" class="regular-text" name="acw_search_label" id="acw_search_label" value="<?php echo get_option('acw_search_label'); ?>"> 
							<label for="acw_search_label">Search button label</label>
						</p>
						<p>
							<input type="text" class="regular-text" name="acw_select_placeholder" id="acw_select_placeholder" value="<?php echo get_option('acw_select_placeholder'); ?>"> 
							<label for="acw_select_placeholder">Select field placeholder</label>
						</p>
						<p class="submit">
							<input type="submit" name="acw_submit" class="button-primary" value="Save Changes">
						</p>
					</form>

					<h4>Plugin Usage: Shortcodes</h4>
					<p>
						Add the <code>[ac-meta name="email"]</code> shortcode to display all posts with the same meta value (e.g. &quot;email&quot;).<br>
						Add the <code>[ac-posts comments="yes"]</code> shortcode to display all posts and show number of comments.<br>
						Add the <code>[ac-posts comments="no"]</code> shortcode to display all posts and hide number of comments.<br>
						Add the <code>[ac-posts category="Adventures"]</code> shortcode to display all posts in a specific category, and all child categories. (e.g. &quot;Adventures&quot;).<br>
						Add the <code>[ac-pages comments="yes"]</code> shortcode to display all pages and show number of comments.<br>
						Add the <code>[ac-pages comments="no"]</code> shortcode to display all pages and hide number of comments.<br>
						Add the <code>[ac-all comments="yes"]</code> shortcode to display all posts and pages and show number of comments.<br>
						Add the <code>[ac-all comments="no"]</code> shortcode to display all posts and pages and hide number of comments.<br>
						Add the <code>[ac-categories]</code> shortcode to display all categories.<br>
						Add the <code>[ac-custom type="testimonials"]</code> shortcode to display all custom post types (e.g. &quot;testimonials&quot;).<br>
						Add the <code>[ac-taxonomy name="testimonials_category"]</code> shortcode to display all custom taxonomies (e.g. &quot;testimonials_category&quot;).<br>
						Add the <code>[ac-tax-posts taxonomyname="testimonials_category" name="business" type="testimonials"]</code> shortcode to display all custom posts in a specific taxonomy.<br>
						Add the <code>[ac-tags]</code> shortcode to display all tags.<br>
						Add the <code>[ac-tagged-posts tag="blog"]</code> shortcode to display all posts with a specific tag.
					</p>

					<h3>Plugin Support</h3>
					<p>For support, feature requests and bug reporting, please visit the <a href="//getbutterfly.com/wordpress-plugins/autocomplete-wizard/" rel="external">official website</a>.</p>
				</div>
			</div>
		</div>
	</div>
<?php
}


function ac_meta_value($atts, $content = null) { // autocomplete :: meta value
	extract(shortcode_atts(array(
		'name' => '',
	), $atts));

	global $wpdb;
	$u = uniqid();
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');

	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			$metakey = $name;
			$acs = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = %s ORDER BY meta_value ASC", $metakey) );
			if($acs) {
				foreach($acs as $ac) {
					$display .= '<option value="' . $ac . '">' . $ac . '</option>';
				}
			}
			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';

	return $display;
}

function ac_posts($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'comments' => 'yes',
		'category' => '',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			$autocomplete_args = array(
				'post_type' => array('post'),
				'showposts' => -1,
				'offset' => 0,
				'category_name' => $category,
			);
			$ac = new WP_Query($autocomplete_args);
			if($ac->have_posts()) : while($ac->have_posts()) : $ac->the_post();
				if($comments == 'yes')
					$ac_comments = ' (' . get_comments_number(get_the_ID()) . ' comments)';
				$display .= '<option value="' . str_replace('-', ' ', sanitize_title(get_the_title())) . '">' . get_the_title() . $ac_comments . '<option>';
			endwhile; endif;

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_pages($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'comments' => 'yes',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			$autocomplete_args = array(
				'post_type' => array('page'),
				'showposts' => -1,
				'offset' => 0,
			);
			$ac = new WP_Query($autocomplete_args);
			if($ac->have_posts()) : while($ac->have_posts()) : $ac->the_post();
				if($comments == 'yes')
					$ac_comments = ' (' . get_comments_number(get_the_ID()) . ' comments)';
				$display .= '<option value="' . str_replace('-', ' ', sanitize_title(get_the_title())) . '">' . get_the_title() . $ac_comments . '<option>';
			endwhile; endif;

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_all($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'comments' => 'yes',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
			<datalist id="' . $u . '">';
			$autocomplete_args = array(
				'post_type' => array('post','page'),
				'showposts' => -1,
				'offset' => 0,
			);
			$ac = new WP_Query($autocomplete_args);
			if($ac->have_posts()) : while($ac->have_posts()) : $ac->the_post();
				if($comments == 'yes')
					$ac_comments = ' (' . get_comments_number(get_the_ID()) . ' comments)';
				$display .= '<option value="' . str_replace('-', ' ', sanitize_title(get_the_title())) . '">' . get_the_title() . $ac_comments . '<option>';
			endwhile; endif;

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_categories($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'comments' => 'yes',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">';
		$display .= wp_dropdown_categories(array(
			'show_option_all' => 'All categories',
			'class' => 'pure-input-1-2',
			'echo' => 0,
			'show_count' => 1,
		));

		$display .= ' <input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';

	return $display;

	wp_reset_query();
}

function ac_custom($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'type' => '',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			$autocomplete_args = array(
				'post_type' => array($type),
				'showposts' => -1,
				'offset' => 0,
			);
			$ac = new WP_Query($autocomplete_args);
			if($ac->have_posts()) : while($ac->have_posts()) : $ac->the_post();
				$display .= '<option value="' . str_replace('-', ' ', sanitize_title(get_the_title())) . '">' . get_the_title() . $ac_comments . '<option>';
			endwhile; endif;

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_taxonomy($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'name' => '',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';

	$myterms = get_terms($name, 'orderby=none&hide_empty');    

	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			foreach($myterms as $term) {
				$display .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
			}

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_tax_posts($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'taxonomyname' => '',
		'name' => '',
		'type' => '',
	), $atts));

	$u = uniqid();
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';

	$autocomplete_args = array(
		"$taxonomyname" => "$name",
		'post_type' => $type,
	);
	$ac = new WP_Query($autocomplete_args);
	if($ac->have_posts()) {
		$display .= '
		<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
			<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
			<datalist id="' . $u . '">';
				while($ac->have_posts()) : $ac->the_post();
					$display .= '<option>'.str_replace('-', ' ', sanitize_title(get_the_title())).'</option>';
				endwhile; wp_reset_query();
			$display .= '</datalist>
			<input type="submit" value="' . $acw_search_label . '" class="pure-button">
		</form>';
	}

	return $display;

	wp_reset_query();
}

function ac_tags($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'name' => '',
		'type' => '',
	), $atts));

	$u = uniqid();
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$tags = get_tags();

	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			foreach($tags as $tag) {
				$display .= '<option value="'.$tag->slug.'">'.$tag->name.'</option>';
			}

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}

function ac_tagged_posts($atts, $content = null) { // autocomplete :: posts
	extract(shortcode_atts(array(
		'tag' => '',
		'comments' => 'no',
	), $atts));

	$u = uniqid();
	$ac_comments = '';
	$acw_search_label = get_option('acw_search_label');
	$acw_select_placeholder = get_option('acw_select_placeholder');
	$display = '';
	$display .= '
	<form name="search" action="' . $_SERVER['PHP_SELF'] . '" method="get" class="pure-form">
		<input name="s" list="' . $u . '" placeholder="' . $acw_select_placeholder . '" type="text" class="pure-input-1-2">
		<datalist id="' . $u . '">';
			$autocomplete_args = array(
				'post_type' => array('post'),
				'showposts' => -1,
				'offset' => 0,
				'tag' => $tag, // tag_slug__in
			);
			$ac = new WP_Query($autocomplete_args);
			if($ac->have_posts()) : while($ac->have_posts()) : $ac->the_post();
				if($comments == 'yes')
					$ac_comments = ' (' . get_comments_number(get_the_ID()) . ' comments)';
				$display .= '<option value="' . str_replace('-', ' ', sanitize_title(get_the_title())) . '">' . get_the_title() . $ac_comments . '<option>';
			endwhile; endif;

			$display .= '</datalist>
		<input type="submit" value="' . $acw_search_label . '" class="pure-button">
	</form>';
	return $display;

	wp_reset_query();
}


add_shortcode('ac-meta', 'ac_meta_value'); // shortcode, function
add_shortcode('ac-posts', 'ac_posts'); // shortcode, function
add_shortcode('ac-pages', 'ac_pages'); // shortcode, function
add_shortcode('ac-all', 'ac_all'); // shortcode, function
add_shortcode('ac-categories', 'ac_categories'); // shortcode, function
add_shortcode('ac-custom', 'ac_custom'); // shortcode, function
add_shortcode('ac-taxonomy', 'ac_taxonomy'); // shortcode, function
add_shortcode('ac-tax-posts', 'ac_tax_posts'); // shortcode, function
add_shortcode('ac-tags', 'ac_tags'); // shortcode, function
add_shortcode('ac-tagged-posts', 'ac_tagged_posts'); // shortcode, function
?>
