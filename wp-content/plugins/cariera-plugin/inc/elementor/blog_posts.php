<?php
/**
 * ELEMENTOR WIDGET - BLOG POSTS
 *
 * @since    1.4.5
 * @version  1.9.6
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Blog_Posts extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'blog_posts';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Blog Posts', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Register the controls for the widget
	 */
	protected function register_controls() {

		// POST LAYOUT SECTION.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Post Layout', 'cariera-core' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'post_layout',
			[
				'label'   => esc_html__( 'Blog Post Layout', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'layout1' => esc_html__( 'Layout 1', 'cariera-core' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera-core' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera-core' ),
				],
				'default' => 'layout1',
			]
		);
		$this->add_responsive_control(
			'columns_grid',
			[
				'label'          => esc_html__( 'Columns', 'cariera-core' ),
				'type'           => \Elementor\Controls_Manager::SLIDER,
				'size_units'     => [ 'px' ],
				'range'          => [
					'px' => [
						'min'  => 1,
						'max'  => 6,
						'step' => 1,
					],
				],
				'default'        => [
					'size' => 3,
					'unit' => 'px'
				],
				'tablet_default' => [
					'size' => 2,
					'unit' => 'px'
				],
				'mobile_default' => [
					'size' => 1,
					'unit' => 'px'
				],
				'selectors'      => [
					'{{WRAPPER}} .blog-posts-wrapper' => 'grid-template-columns: repeat({{SIZE}}, 1fr);',
				],
			]
		);
		$this->add_control(
			'show_thumb',
			[
				'label'        => esc_html__( 'Show Post Thumbnail', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'post_layout' => 'layout1',
				],
			]
		);
		$this->add_control(
			'show_avatar',
			[
				'label'        => esc_html__( 'Show Author Avatar', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'post_layout' => 'layout1',
					'show_thumb'  => 'show',
				],
			]
		);
		$this->add_control(
			'show_date',
			[
				'label'        => esc_html__( 'Show Post Date', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
			]
		);
		$this->add_control(
			'show_cats',
			[
				'label'        => esc_html__( 'Show Categories', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'yes',
				'condition'    => [
					'post_layout' => 'layout3',
				],
			]
		);
		$this->add_control(
			'button',
			[
				'label'       => esc_html__( 'Button Name', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Read More',
				'label_block' => true,
				'description' => esc_html__( 'Delete the text of the field if you want to hide the "read more" button', 'cariera-core' ),
			]
		);
		$this->add_control(
			'read_more_link',
			[
				'label'         => esc_html__( 'Read All URL', 'cariera-core' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'default'       => [
					'url'         => 'http://',
					'is_external' => '',
					'nofollow'    => '',
				],
				'show_external' => true, // Show the 'open in new tab' button.
			]
		);

		$this->end_controls_section();

		// POST QUERY SECTION.
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Post Query', 'cariera-core' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'cat_ids',
			[
				'label'       => esc_html__( 'Post Category IDs to include', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter post category ids to include, separated by a comma. Leave empty to get posts from all categories.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'ids',
			[
				'label'       => esc_html__( 'Enter Post IDs', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter Post ids to show, separated by a comma. Leave empty to show all.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'ids_not',
			[
				'label'       => esc_html__( 'Or Post IDs to Exclude', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter post ids to exclude, separated by a comma (,). Use if the field above is empty.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'order_by',
			[
				'label'       => esc_html__( 'Order by', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'date'          => esc_html__( 'Date', 'cariera-core' ),
					'ID'            => esc_html__( 'ID', 'cariera-core' ),
					'author'        => esc_html__( 'Author', 'cariera-core' ),
					'title'         => esc_html__( 'Title', 'cariera-core' ),
					'modified'      => esc_html__( 'Modified', 'cariera-core' ),
					'rand'          => esc_html__( 'Random', 'cariera-core' ),
					'comment_count' => esc_html__( 'Comment Count', 'cariera-core' ),
					'menu_order'    => esc_html__( 'Menu Order', 'cariera-core' ),
					'post__in'      => esc_html__( 'ID order given (post__in)', 'cariera-core' ),
				],
				'default'     => 'date',
				'separator'   => 'before',
				'description' => esc_html__( 'Select how to sort retrieved posts. More at ', 'cariera-core' ) . '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex</a>.',
			]
		);
		$this->add_control(
			'order',
			[
				'label'       => esc_html__( 'Sort Order', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'ASC'  => esc_html__( 'Ascending', 'cariera-core' ),
					'DESC' => esc_html__( 'Descending', 'cariera-core' ),
				],
				'default'     => 'DESC',
				'separator'   => 'before',
				'description' => esc_html__( 'Select Ascending or Descending order. More at', 'cariera-core' ) . '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex</a>.',
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Posts to show', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '3',
				'description' => esc_html__( 'Number of posts to show (-1 for all).', 'cariera-core' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-blog-element' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-blog-element' );

		$settings = $this->get_settings();

		// Pagination handling.
		$paged = 1;
		if ( get_query_var( 'paged' ) ) {
			$paged = (int) get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = (int) get_query_var( 'page' );
		}

		// Base post args.
		$post_args = [
			'post_type'           => 'post',
			'paged'               => $paged,
			'posts_per_page'      => ! empty( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 6,
			'orderby'             => ! empty( $settings['order_by'] ) ? sanitize_key( $settings['order_by'] ) : 'date',
			'order'               => ! empty( $settings['order'] ) ? $settings['order'] : 'DESC',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
		];

		// Handle inclusions/exclusions.
		if ( ! empty( $settings['ids'] ) ) {
			$post_args['post__in'] = array_map( 'absint', explode( ',', $settings['ids'] ) );
		} elseif ( ! empty( $settings['ids_not'] ) ) {
			$post_args['post__not_in'] = array_map( 'absint', explode( ',', $settings['ids_not'] ) );
		}

		// Handle category filtering.
		if ( ! empty( $settings['cat_ids'] ) ) {
			$post_args['cat'] = absint( $settings['cat_ids'] );
		}

		$query = new \WP_Query( $post_args );
		?>

		<div class="blog-posts-wrapper">
			<?php
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$post_id        = get_the_ID();
					$blog_thumbnail = get_the_post_thumbnail_url( $post_id );

					if ( empty( $blog_thumbnail ) ) {
						$blog_thumbnail = get_template_directory_uri() . '/assets/images/default-thumbnail.png';
					}

					if ( 'layout1' === $settings['post_layout'] ) {
						$this->render_layout1( $settings, $blog_thumbnail, $post_id );
					} elseif ( 'layout2' === $settings['post_layout'] ) {
						$this->render_layout2( $settings, $blog_thumbnail, $post_id );
					} elseif ( 'layout3' === $settings['post_layout'] ) {
						$this->render_layout3( $settings, $post_id );
					}
				}
				wp_reset_postdata();
			}
			?>
		</div>

		<?php
		// Render "read more" button.
		if ( ! empty( $settings['button'] ) && ! empty( $settings['read_more_link']['url'] ) ) {
			$url = esc_url( $settings['read_more_link']['url'] );

			$target = '';
			if ( ! empty( $settings['read_more_link']['is_external'] ) ) {
				$target = ' target="_blank"';
			}

			$follow = '';
			if ( ! empty( $settings['read_more_link']['nofollow'] ) ) {
				$follow = ' rel="nofollow"';
			}

			printf(
				'<div class="read-more-wrapper"><a href="%1$s"%2$s%3$s class="btn btn-main btn-effect">%4$s</a></div>',
				$url,
				$target,
				$follow,
				esc_html( $settings['button'] )
			);
		}

		wp_reset_postdata();
	}

	/**
	 * Render layout 1
	 *
	 * @param array  $settings
	 * @param string $thumbnail
	 * @param int    $post_id
	 */
	protected function render_layout1( $settings, $thumbnail, $post_id ) {
		?>
		<div class="blog-post-layout" id="post-<?php echo esc_attr( $post_id ); ?>">
			<?php if ( 'show' === $settings['show_thumb'] ) { ?>
				<a href="<?php the_permalink(); ?>" class="bloglist-thumb-link" aria-label="<?php the_title_attribute(); ?>">
					<div class="bloglist-post-thumbnail" style="background-image:url('<?php echo esc_url( $thumbnail ); ?>')"></div>
				</a>
			<?php } ?>

			<div class="bloglist-text-wrapper">
				<?php if ( 'show' === $settings['show_thumb'] && 'show' === $settings['show_avatar'] ) { ?>
					<span class="bloglist-avatar"><?php echo get_avatar( get_the_author_meta( 'user_email' ), 50 ); ?></span>
				<?php } ?>

				<h4 class="bloglist-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h4>

				<?php if ( 'show' === $settings['show_date'] ) { ?>
					<div class="bloglist-meta">
						<i class="las la-calendar"></i> <?php echo esc_html( get_the_time( get_option( 'date_format' ) ) ); ?>
					</div>
				<?php } ?>

				<div class="bloglist-excerpt">
					<p><?php echo esc_html( cariera_string_limit_words( get_the_excerpt(), 23 ) ); ?>...</p>
					<a href="<?php the_permalink(); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'read more', 'cariera-core' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render layout 2
	 *
	 * @param array  $settings
	 * @param string $thumbnail
	 * @param int    $post_id
	 */
	protected function render_layout2( $settings, $thumbnail, $post_id ) {
		$post_cat = get_the_category();
		$category = ! empty( $post_cat ) ? $post_cat[0] : null;
		?>
		<div class="blog-post-layout2" id="post-<?php echo esc_attr( $post_id ); ?>">
			<div class="bloglist-post-thumbnail" style="background-image:url('<?php echo esc_url( $thumbnail ); ?>')"></div>

			<div class="bloglist-text-wrapper">
				<?php if ( $category ) { ?>
					<span class="post-category">
						<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
					</span>
				<?php } ?>

				<h4 class="bloglist-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h4>

				<?php if ( 'show' === $settings['show_date'] ) { ?>
					<div class="bloglist-meta">
						<i class="las la-calendar"></i> <?php echo esc_html( get_the_time( get_option( 'date_format' ) ) ); ?>
					</div>
				<?php } ?>

				<div class="bloglist-excerpt">
					<p><?php echo esc_html( cariera_string_limit_words( get_the_excerpt(), 23 ) ); ?>...</p>
					<a href="<?php the_permalink(); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'read more', 'cariera-core' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render layout 3
	 *
	 * @param array $settings
	 * @param int   $post_id
	 */
	protected function render_layout3( $settings, $post_id ) {
		?>
		<a href="<?php the_permalink(); ?>" class="blog-post-layout3" id="post-<?php echo esc_attr( $post_id ); ?>">
			<div class="blog-grid-item">
				<?php if ( has_post_thumbnail() && ! post_password_required() ) { ?>
					<?php the_post_thumbnail(); ?>
				<?php } ?>

				<?php if ( 'yes' === $settings['show_cats'] && has_category() ) { ?>
					<span class="item-cat"><?php echo esc_html( get_the_category()[0]->name ); ?></span>
				<?php } ?>

				<div class="blog-grid-item-content">
					<?php if ( 'show' === $settings['show_date'] && get_the_date() ) { ?>
						<ul class="post-meta">
							<li class="published"><?php echo esc_html( get_the_date() ); ?></li>
						</ul>
					<?php } ?>

					<h3 class="title"><?php the_title(); ?></h3>
				</div>
			</div>
		</a>
		<?php
	}
}
