<?php
/*
Plugin Name: Extra Feeds
Plugin URI: https://github.com/dshanske/extra-feeds/
Description: Adds extra feed links.
Version: 0.0.1
Author: David Shanske
Text Domain: extra-feeds
License: GPL2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

defined( 'ABSPATH' ) || die( "WordPress plugin can't be loaded directly." );

if ( ! defined( 'EXTRA_LINKS_FEED' ) ) {
	define( 'EXTRA_LINKS_FEED', 'rss' );
}

/**
 * Display the links to the extra feeds such as category feeds.
 *
 *
 * @param array $args Optional arguments.
 */
function extra_feed_links_extra( $args = array() ) {
	$defaults = array(
		/* translators: Separator between blog name and feed type in feed links */
		'separator'   => _x( '&raquo;', 'feed link', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: post title */
		'singletitle' => __( '%1$s %2$s %3$s Comments Feed', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: category name */
		'cattitle'    => __( '%1$s %2$s %3$s Category Feed', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: tag name */
		'tagtitle'    => __( '%1$s %2$s %3$s Tag Feed', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: term name, 4: taxonomy singular name */
		'taxtitle'    => __( '%1$s %2$s %3$s %4$s Feed', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: author name  */
		'authortitle' => __( '%1$s %2$s Posts by %3$s Feed', 'extra-feeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: search phrase */
		'searchtitle' => __( '%1$s %2$s Search Results for &#8220;%3$s&#8221; Feed', 'extra-feeds' ),
	);
	$args     = wp_parse_args( $args, $defaults );
	$feeds    = array();
	if ( is_single() ) {
		$id         = 0;
		$post       = get_post( $id );
		$categories = get_the_category( $post->ID );
		foreach ( $categories as $category ) {
			$feeds[] = array(
				'title' => sprintf( $args['cattitle'], get_bloginfo( 'name' ), $args['separator'], $category->name ),
				'href'  => get_category_feed_link( $category->term_id, EXTRA_LINKS_FEED ),
			);
		}
		$tags = get_the_tags( $post->ID );
		foreach ( $tags as $tag ) {
			$feeds[] = array(
				'title' => sprintf( $args['tagtitle'], get_bloginfo( 'name' ), $args['separator'], $tag->name ),
				'href'  => get_tag_feed_link( $tag->term_id, EXTRA_LINKS_FEED ),
			);
		}
		$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
		foreach ( $taxonomies as $taxonomy_slug => $tax ) {
			$terms = get_the_terms( $post->ID, $taxonomy_slug );
			$terms = is_array( $terms ) ? $terms : array();
			foreach ( $terms as $term ) {
				$feeds[] = array(
					'title' => sprintf( $args['taxtitle'], get_bloginfo( 'name' ), $args['separator'], $term->name, $tax->labels->singular_name ),
					'href'  => get_term_feed_link( $term->term_id, $term->taxonomy, EXTRA_LINKS_FEED ),
				);
			}
		}
		$author_id = $post->post_author;
		$feeds[]   = array(
			'title' => sprintf( $args['authortitle'], get_bloginfo( 'name' ), $args['separator'], get_the_author_meta( 'display_name', $author_id ) ),
			'href'  => get_author_feed_link( $author_id, EXTRA_LINKS_FEED ),
		);
		foreach ( $feeds as $feed ) {
			if ( array_key_exists( 'href', $feed ) && array_key_exists( 'title', $feed ) ) {
				printf( '<link rel="alternate" type="%s" title="%s" href="%s" />', esc_attr( feed_content_type( EXTRA_LINKS_FEED ) ), esc_attr( $feed['title'] ), esc_url( $feed['href'] ) );
				echo PHP_EOL;
			}
		}
	}
}
add_filter( 'wp_head', 'extra_feed_links_extra' );
