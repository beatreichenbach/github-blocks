<?php

/**
 * Plugin Name: GitHub
 * Plugin URI: https://github.com/beatreichenbach
 * Description: A collection of blocks to integrate GitHub into WordPress.
 * Version: 1.0.0
 * Author: Beat Reichenbach
 *
 * @package github
 */

defined( 'ABSPATH' ) || exit;

function github_list_repos_render_block( $block_attributes, $content ) {

	// get repos
	$username = $block_attributes['username'];
	$profileButton = $block_attributes['profileButton'];

	if ( false == $username )
		return null;




	$url = 'https://api.github.com/users/' . $username . '/repos';
	$profile_url = 'https://github.com/' . $username;

	$context = stream_context_create(array(
		'http' => array(
			'method' => 'GET',
			'header' => array('User-Agent: PHP')
			)
		));
	$content = file_get_contents($url, false, $context);

	if ( false === $content )
		return null;
	$repos = json_decode($content);

	// get color data for repo languages
	$colordata = file_get_contents(plugins_url( 'colors.json', __FILE__ ) );

	if ( false !== $colordata )
		$colors = json_decode( $colordata );

	// create html
	$blockProps = get_block_wrapper_attributes();

	$star_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
			<g><path d="M12,17.27L18.18,21l-1.64-7.03L22,9.24l-7.19-0.61L12,2L9.19,8.63L2,9.24l5.46,4.73L5.82,21L12,17.27z"/></g>
			</svg>';

	$html = '<ol class="github-list">';
	foreach ($repos as $repo) {
		$date = new DateTime($repo->updated_at);
		$date_title = $date->format('M d, Y, h:i A');
		$date_text = $date->format('M d, Y');
		$stargaze_url = $repo->html_url . '/stargazers';
		if( isset( $colors ) )
			$color = $colors->{$repo->language}->color;
		else
			$color = 'white';

		$html .= '
			<li class="github-list-item">
				<div class="github-repo">
					<div class="github-repo-title">
						<a href="' . $repo->html_url . '" class="">' . $repo->name . '</a>
					</div>
					<p class="github-repo-description">' . $repo->description . '</p>
					<p class="github-repo-tags">
						<span class="github-repo-language github-repo-tag">
							<span class="github-repo-language-color" style="background-color: ' . $color . '"></span>
							<span class="github-repo-language-text">' . $repo->language . '</span>
						</span>
						<a href="' . $stargaze_url . '" class="github-repo-stargazers github-repo-tag">
							' . $star_svg . $repo->stargazers_count . '
						</a>
						Updated <relative-time datetime="' . $repo->updated_at . '" class="no-wrap" title="' . $date_title . '">on ' . $date_text . '</relative-time>
					</p>
				</div>
			</li>';
	}

	$html .= '</ol>';


	if( $profileButton ) {
		$html .= '<div class="wp-block-button"><a href="' . $profile_url . '" class="github-profile">GitHub Profile</a></div>';
	}

	$html = sprintf( '<div %s>%s</div>', $blockProps, $html );
	return $html;
}

function github_list_repos_register_block() {

	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// automatically load dependencies and version
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

	wp_register_script(
		'github-list-repos',
		plugins_url( '/build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version']
	);

	wp_register_style(
		'github-list-repos',
		plugins_url( 'style.css', __FILE__ ),
		array( ),
		filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
	);

	register_block_type( 'github/list-repos', array(
		'api_version' => 2,
		'style' => 'github-list-repos',
		'editor_script' => 'github-list-repos',
		'render_callback' => 'github_list_repos_render_block',
		'attributes' => array(
			'username' => array(
				'type' => 'string',
				'default' => ''
				),
			'profileButton' => array(
				'type' => 'boolean',
				'default' => false
				),
			)
		)
	);

}
add_action( 'init', 'github_list_repos_register_block' );
