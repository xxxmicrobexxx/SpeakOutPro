<?php

// register shortcode to display signatures list
add_shortcode( 'signaturelist', 'dk_speakout_signatures_shortcode' );

function dk_speakout_signatures_shortcode( $attr ) {
global  $dk_speakout_version;

    include_once( 'class.petition.php' );
    $petition = new dk_speakout_Petition();
    
	include_once( 'class.signaturelist.php' );
	$options = get_option( 'dk_speakout_options' );

	$id             = 1;
	$rows           = $options['signaturelist_rows'];
	$firstbuttontext= '&lt;&lt;';
	$nextbuttontext = '&gt;';
	$prevbuttontext = '&lt;';
	$lastbuttontext= '&gt;&gt;';
	$dateformat     = 'M d, Y'; 
    $hideUnconfirmed = $petition->requires_confirmation == 1 ? true : false ;

	if ( isset( $attr['id'] ) && is_numeric( $attr['id'] ) ) {
		$id = $attr['id'];
	}
	
    $petition_exists = $petition->retrieve( $id );
    
    
    
	//over-ride if shortcode used
	if ( isset( $attr['rows'] ) && is_numeric( $attr['rows'] ) ) {
		$rows = absint( $attr['rows'] );
	}
	if ( isset( $attr['firstbuttontext'] ) ) {
		$firstbuttontext = $attr['firstbuttontext'];
	}
	if ( isset( $attr['nextbuttontext'] ) ) {
		$nextbuttontext = $attr['nextbuttontext'];
	}
	if ( isset( $attr['prevbuttontext'] ) ) {
		$prevbuttontext = $attr['prevbuttontext'];
	}
	if ( isset( $attr['lastbuttontext'] ) ) {
		$lastbuttontext = $attr['lastbuttontext'];
	}
	if ( isset( $attr['dateformat'] ) ) {
		$dateformat = $attr['dateformat'];
	}
    
    $hideUnconfirmed = $petition->requires_confirmation == 1 ? true : false ;


	// make sure ajax callback url works on both https and http
	$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
	$params   = array(
		'ajaxurl'    => admin_url( 'admin-ajax.php', $protocol ),
		'dateformat' => $dateformat
	);
	wp_enqueue_script( 'dk_speakout_signaturelist_js', plugins_url( 'speakout/js/signaturelist.js' ), array( 'jquery' ),  $dk_speakout_version );
	wp_localize_script( 'dk_speakout_signaturelist_js', 'dk_speakout_signaturelist_js', $params );

	$table_html = dk_speakout_signaturelist::table( $id, 0, $rows, 'shortcode', $dateformat, $firstbuttontext, $nextbuttontext, $prevbuttontext, $lastbuttontext, $hideUnconfirmed );
	return $table_html;
}

// load CSS on pages/posts that contain the [signaturelist] shortcode
add_filter( 'the_posts', 'dk_speakout_signaturelist_css' );
function dk_speakout_signaturelist_css( $posts ) {
global  $dk_speakout_version;
	// ignore if there are no posts
	if ( empty( $posts ) ) return $posts;

	$options = get_option( 'dk_speakout_options' );

	// set flag to determine if post contains shortcode
	$shortcode_found = false;
	foreach ( $posts as $post ) {
		// if post content contains the shortcode
		if ( strstr( $post->post_content, '[signaturelist' ) ) {
			// update flag
			$shortcode_found = true;
			break;
		}
	}

	// if flag is now true, load the CSS
	if ( $shortcode_found ) {
		$theme = $options['signaturelist_theme'];

		 // load default theme
		if ( $theme === 'default' ) {
			wp_enqueue_style( 'dk_speakout_signaturelist_css', plugins_url( 'speakout/css/signaturelist.css' ) , array(), $dk_speakout_version);
		}
		// attempt to load cusom theme (petition-signaturelist.css)
		else {
			$parent_dir       = get_template_directory_uri();
			$parent_theme_url = $parent_dir . '/petition-signaturelist.css';

			// if a child theme is in use
			// try to load style from child theme folder
			if ( is_child_theme() ) {
				$child_dir        = get_stylesheet_directory_uri();
				$child_theme_url  = $child_dir . '/petition-signaturelist.css';
				$child_theme_path = STYLESHEETPATH . '/petition-signaturelist.css';

				// use child theme if it exists
				if ( file_exists( $child_theme_path ) ) {
					wp_enqueue_style( 'dk_speakout_signaturelist_css', $child_theme_url, array(), $dk_speakout_version );
				}
				// else try to load style from parent theme folder
				else {
					wp_enqueue_style( 'dk_speakout_signaturelist_css', $parent_theme_url, array(), $dk_speakout_version );
				}
			}
			// if not using a child theme, just try to load style from active theme folder
			else {
				wp_enqueue_style( 'dk_speakout_signaturelist_css', $parent_theme_url, array(), $dk_speakout_version );
			}
		}
	}

	return $posts;
}

?>