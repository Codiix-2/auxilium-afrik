<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main menu fallback function when no menu exists
 *
 * @since   1.0.0
 * @version 1.8.1
 */
function menu_fallback() {
	if ( current_user_can( 'manage_options' ) ) {
		echo( '
        <ul id="menu-main-menu" class="main-menu main-nav">
        <li class="menu-item"><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Add a menu', 'cariera' ) . '</a></li>
        </ul>' );
	} else {
		echo( '
        <ul id="menu-main-menu" class="main-menu main-nav">
        <li class="menu-item"></li>
        </ul>' );
	}
}

/**
 * Post Thumbnail
 *
 * @since   1.3.3
 * @version 1.9.6
 *
 * @param array $args
 *
 * TODO: Check for fixes.
 */
function post_thumbnail( $args = [] ) {
	global $post;

	$defaults = [
		'size'  => 'large',
		'class' => 'post-image',
	];

	$args        = wp_parse_args( $args, $defaults );
	$post_format = get_post_format();

	// Standard or Image Post.
	if ( false === $post_format || 'standard' === $post_format || 'image' === $post_format ) {
		if ( has_post_thumbnail() ) {
			?>
			<div class="blog-thumbnail">
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<?php the_post_thumbnail(); ?>
				</a>
			</div>
			<?php
		}
	}

	// Gallery Post.
	if ( 'gallery' === $post_format ) {
		$images = get_post_meta( $post->ID, 'cariera_blog_gallery', true );

		if ( ! empty( $images ) ) {
			?>
			<div class="gallery-post-wrapper">
				<div class="gallery-post">
					<?php
					foreach ( $images as $image ) {
						echo '<div class="item"><img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Blog post gallery image', 'cariera' ) . '"/></div>';
					}
					?>
				</div>
			</div>
			<?php
		}
	}

	// Video Post.
	if ( 'video' === $post_format ) {
		$video = get_post_meta( $post->ID, 'cariera_blog_video_embed', true );
		if ( ! empty( $video ) ) {
			?>
			<div class="embed-responsive embed-responsive-16by9">
				<?php
				if ( wp_oembed_get( $video ) ) {
					echo wp_oembed_get( $video );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $video, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}

	// Audio Post.
	if ( 'audio' === $post_format ) {
		$audio = get_post_meta( $post->ID, 'cariera_blog_audio', true );
		if ( ! empty( $audio ) ) {
			?>
			<div class="audio-wrapper">
				<?php
				if ( wp_oembed_get( $audio ) ) {
					echo wp_oembed_get( $audio );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $audio, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}
}

/**
 * Single Post Thumbnail
 *
 * @since   1.3.3
 * @version 1.9.6
 *
 * TODO: Check for fixes.
 */
function single_post_thumbnail() {
	global $post;

	$post_format = get_post_format();

	// Standard or Image Post.
	if ( false === $post_format || 'image' === $post_format ) {
		if ( has_post_thumbnail() ) {
			?>
			<div class="blog-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div>
			<?php
		}
	}

	// Gallery Post.
	if ( 'gallery' === $post_format ) {
		$images = get_post_meta( $post->ID, 'cariera_blog_gallery', true );

		if ( ! empty( $images ) ) {
			?>
			<div class="gallery-post">
				<?php
				foreach ( $images as $image ) {
					echo '<div class="item"><img src="' . esc_url( $image ) . '"/></div>';
				}
				?>
			</div>
			<?php
		}
	}

	// Quote Post.
	if ( 'quote' === $post_format ) {
		$quote_content = get_post_meta( $post->ID, 'cariera_blog_quote_content', true );
		$quote_author  = get_post_meta( $post->ID, 'cariera_blog_quote_author', true );
		$quote_source  = get_post_meta( $post->ID, 'cariera_blog_quote_source', true );
		$allowed_tags  = wp_kses_allowed_html( 'post' );

		if ( ! empty( $quote_content ) && ! empty( $quote_author ) ) {
			?>
			<figure class="post-quote">
				<span class="icon"></span>
				<blockquote>
					<h2 class="quote"><?php echo esc_html( $quote_content ); ?></h2>

					<?php if ( ! empty( $quote_source ) ) { ?>
						<a href="<?php echo esc_url( $quote_source ); ?>">
					<?php } ?>
							<h3 class="author">
							<?php
							echo esc_html( '- ' );
							echo wp_kses( $quote_author, $allowed_tags );
							?>
							</h3>
					<?php if ( ! empty( $quote_source ) ) { ?>
						</a> 
					<?php } ?>
				</blockquote>
			</figure>
			<?php
		}
	}

	// Audio Post.
	if ( 'audio' === $post_format ) {
		$audio = get_post_meta( $post->ID, 'cariera_blog_audio', true );
		if ( ! empty( $audio ) ) {
			?>
			<div class="audio-wrapper">
				<?php
				if ( wp_oembed_get( $audio ) ) {
					echo wp_oembed_get( $audio );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $audio, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}

	// Video Post.
	if ( 'video' === $post_format ) {
		$video_embed = get_post_meta( $post->ID, 'cariera_blog_video_embed', true );
		if ( ! empty( $video_embed ) ) {
			?>
			<div class="embed-responsive embed-responsive-16by9">
				<?php
				if ( wp_oembed_get( $video_embed ) ) {
					echo wp_oembed_get( $video_embed );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $video_embed, $allowed_tags );
				}
				?>
			</div>

			<?php
		}
	}
}

/**
 * Navigation function for pagination.
 *
 * @since   1.0.0
 * @version 1.9.6
 */
function pagination_nav() {
	echo '<div class="archive-pagination">';
	the_posts_pagination(
		[
			'mid_size'           => 2,
			'prev_text'          => esc_html__( '« Previous', 'cariera' ),
			'next_text'          => esc_html__( 'Next »', 'cariera' ),
			'screen_reader_text' => esc_html__( 'Posts navigation', 'cariera' ),
		]
	);
	echo '</div>';
}

/**
 * Display breadcrumbs for posts, pages, archive page with the microdata that search engines understand
 *
 * @since   1.2.7
 * @version 1.7.5
 *
 * @param array $args
 */
function breadcrumbs( $args = '' ) {
	if ( ! cariera_get_option( 'cariera_breadcrumbs' ) ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		[
			'separator'         => '',
			'home_class'        => 'home',
			'before'            => '<ul class="breadcrumb">',
			'after'             => '</ul>',
			'before_item'       => '<li>',
			'after_item'        => '</li>',
			'taxonomy'          => 'category',
			'display_last_item' => true,
			'show_on_front'     => true,
			'labels'            => [
				'home'      => esc_html__( 'Home', 'cariera' ),
				'archive'   => esc_html__( 'Archives', 'cariera' ),
				'blog'      => esc_html__( 'Blog', 'cariera' ),
				'search'    => esc_html__( 'Search results for', 'cariera' ),
				'not_found' => esc_html__( 'Not Found', 'cariera' ),
				'author'    => esc_html__( 'Author:', 'cariera' ),
				'day'       => esc_html__( 'Daily:', 'cariera' ),
				'month'     => esc_html__( 'Monthly:', 'cariera' ),
				'year'      => esc_html__( 'Yearly:', 'cariera' ),
			],
		]
	);

	$args = apply_filters( 'cariera_breadcrumbs_args', $args );

	if ( is_front_page() && ! $args['show_on_front'] ) {
		return;
	}

	$items = [];

	// HTML template for each item.
	$item_tpl      = $args['before_item'] . '<span><a href="%s">%s</a></span>' . $args['after_item'];
	$item_text_tpl = $args['before_item'] . '<span>%s</span>' . $args['after_item'];

	// Home.
	if ( ! $args['home_class'] ) {
		$items[] = sprintf( $item_tpl, get_home_url(), $args['labels']['home'] );
	} else {
		$items[] = sprintf(
			'%s<span>
				<a class="%s" href="%s"><span>%s</span></a>
			</span>%s',
			$args['before_item'],
			$args['home_class'],
			apply_filters( 'cariera_breadcrumbs_home_url', get_home_url() ),
			$args['labels']['home'],
			$args['after_item']
		);
	}

	// Front page.
	if ( is_front_page() ) {
		$items   = [];
		$items[] = sprintf( $item_text_tpl, $args['labels']['home'] );
	} // Blog
	elseif ( is_home() && ! is_front_page() ) {
		$items[] = sprintf(
			$item_text_tpl,
			\get_the_title( get_option( 'page_for_posts' ) )
		);
	}

	// Single.
	elseif ( is_single() ) {
		// Terms.
		$taxonomy = $args['taxonomy'];

		$terms = \get_the_terms( get_the_ID(), $taxonomy );
		if ( $terms ) {
			$term    = end( $terms );
			$terms   = \Cariera\get_term_parents( $term->term_id, $taxonomy );
			$terms[] = $term->term_id;

			foreach ( $terms as $term_id ) {
				$term    = get_term( $term_id, $taxonomy );
				$items[] = sprintf( $item_tpl, \get_term_link( $term, $taxonomy ), $term->name );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, \get_the_title() );
		}
	}

	// Page.
	elseif ( is_page() ) {
		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			$page_id = get_option( 'woocommerce_shop_page_id' );
			if ( $page_id ) {
				$items[] = sprintf( $item_tpl, esc_url( get_permalink( $page_id ) ), \get_the_title( $page_id ) );
			}
		} else {
			$pages = \Cariera\get_post_parents( get_queried_object_id() );
			foreach ( $pages as $page ) {
				$items[] = sprintf( $item_tpl, esc_url( get_permalink( $page ) ), \get_the_title( $page ) );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, \get_the_title() );
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$title = \get_the_title( get_option( 'woocommerce_shop_page_id' ) );
		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, $title );
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$current_term = get_queried_object();
		$terms        = \Cariera\get_term_parents( get_queried_object_id(), $current_term->taxonomy );

		if ( $terms ) {
			foreach ( $terms as $term_id ) {
				$term    = get_term( $term_id, $current_term->taxonomy );
				$items[] = sprintf( $item_tpl, \get_term_link( $term, $current_term->taxonomy ), $term->name );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, $current_term->name );
		}
	} // Search.
	elseif ( is_search() ) {
		$items[] = sprintf( $item_text_tpl, $args['labels']['search'] . ' &quot;' . \get_search_query() . '&quot;' );

	} // 404 Page.
	elseif ( is_404() ) {
		$items[] = sprintf( $item_text_tpl, $args['labels']['not_found'] );

	} // Author archive.
	elseif ( is_author() ) {
		// Queue the first post, that way we know what author we're dealing with (if that is the case).
		the_post();
		$items[] = sprintf(
			$item_text_tpl,
			$args['labels']['author'] . ' <span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . \get_the_author() . '</a></span>'
		);
		rewind_posts();

	} // Day archive.
	elseif ( is_day() ) {
		$items[] = sprintf(
			$item_text_tpl,
			// translators: %1$s is the day label, %2$s is the date.
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['day'], \get_the_date() )
		);

	} // Month archive.
	elseif ( is_month() ) {
		$items[] = sprintf(
			$item_text_tpl,
			// translators: %1$s is the month label, %2$s is the date.
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['month'], \get_the_date( 'F Y' ) )
		);

	} // Year archive.
	elseif ( is_year() ) {
		$items[] = sprintf(
			$item_text_tpl,
			// translators: %1$s is the month label, %2$s is the date.
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['year'], \get_the_date( 'Y' ) )
		);

	} // Archive.
	else {
		$items[] = sprintf(
			$item_text_tpl,
			$args['labels']['archive']
		);
	}

	return $args['before'] . implode( $args['separator'], $items ) . $args['after'];
}

/**
 * Get Currency Symbol
 *
 * @since 1.0.0
 *
 * @param string $currency
 */
function currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_option( 'cariera_currency_setting' );
	}

	switch ( $currency ) {
		case 'BHD':
			$currency_symbol = esc_html( '.د.ب' );
			break;
		case 'AED':
			$currency_symbol = esc_html( 'د.إ' );
			break;
		case 'AUD':
		case 'ARS':
		case 'CAD':
		case 'CLP':
		case 'COP':
		case 'HKD':
		case 'MXN':
		case 'NZD':
		case 'SGD':
		case 'USD':
			$currency_symbol = esc_html( '&#36;' );
			break;
		case 'BAM':
			$currency_symbol = esc_html( 'KM' );
			break;
		case 'BDT':
			$currency_symbol = esc_html( '&#2547;&nbsp;' );
			break;
		case 'LKR':
			$currency_symbol = esc_html( '&#3515;&#3540;&nbsp;' );
			break;
		case 'BGN':
			$currency_symbol = esc_html( '&#1083;&#1074;.' );
			break;
		case 'BRL':
			$currency_symbol = esc_html( '&#82;&#36;' );
			break;
		case 'CHF':
			$currency_symbol = esc_html( '&#67;&#72;&#70;' );
			break;
		case 'CNY':
		case 'JPY':
		case 'RMB':
			$currency_symbol = esc_html( '&yen;' );
			break;
		case 'CZK':
			$currency_symbol = esc_html( '&#75;&#269;' );
			break;
		case 'DKK':
			$currency_symbol = esc_html( 'DKK' );
			break;
		case 'DOP':
			$currency_symbol = esc_html( 'RD&#36;' );
			break;
		case 'EGP':
			$currency_symbol = esc_html( 'EGP' );
			break;
		case 'EUR':
			$currency_symbol = esc_html( '&euro;' );
			break;
		case 'GBP':
			$currency_symbol = esc_html( '&pound;' );
			break;
		case 'HRK':
			$currency_symbol = esc_html( 'Kn' );
			break;
		case 'HUF':
			$currency_symbol = esc_html( '&#70;&#116;' );
			break;
		case 'IDR':
			$currency_symbol = esc_html( 'Rp' );
			break;
		case 'ILS':
			$currency_symbol = esc_html( '&#8362;' );
			break;
		case 'INR':
			$currency_symbol = esc_html( 'Rs.' );
			break;
		case 'ISK':
			$currency_symbol = esc_html( 'Kr.' );
			break;
		case 'KIP':
			$currency_symbol = esc_html( '&#8365;' );
			break;
		case 'KRW':
			$currency_symbol = esc_html( '&#8361;' );
			break;
		case 'MYR':
			$currency_symbol = esc_html( '&#82;&#77;' );
			break;
		case 'NGN':
			$currency_symbol = esc_html( '&#8358;' );
			break;
		case 'NOK':
			$currency_symbol = esc_html( '&#107;&#114;' );
			break;
		case 'NPR':
			$currency_symbol = esc_html( 'Rs.' );
			break;
		case 'PHP':
			$currency_symbol = esc_html( '&#8369;' );
			break;
		case 'PLN':
			$currency_symbol = esc_html( '&#122;&#322;' );
			break;
		case 'PYG':
			$currency_symbol = esc_html( '&#8370;' );
			break;
		case 'RON':
			$currency_symbol = esc_html( 'lei' );
			break;
		case 'RUB':
			$currency_symbol = esc_html( '&#1088;&#1091;&#1073;.' );
			break;
		case 'SEK':
			$currency_symbol = esc_html( '&#107;&#114;' );
			break;
		case 'THB':
			$currency_symbol = esc_html( '&#3647;' );
			break;
		case 'TRY':
			$currency_symbol = esc_html( '&#8378;' );
			break;
		case 'TWD':
			$currency_symbol = esc_html( '&#78;&#84;&#36;' );
			break;
		case 'UAH':
			$currency_symbol = esc_html( '&#8372;' );
			break;
		case 'VND':
			$currency_symbol = esc_html( '&#8363;' );
			break;
		case 'ZAR':
			$currency_symbol = esc_html( '&#82;' );
			break;
		case 'ZMK':
			$currency_symbol = esc_html( 'ZK' );
			break;
		default:
			$currency_symbol = esc_html( '' );
			break;
	}

	return apply_filters( 'cariera_currency_symbol', $currency_symbol, $currency );
}
