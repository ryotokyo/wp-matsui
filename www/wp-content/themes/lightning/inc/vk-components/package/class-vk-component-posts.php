<?php
/*
The original of this file is located at:
https://github.com/vektor-inc/vektor-wp-libraries
If you want to change this file, please change the original file.
*/

if ( ! class_exists( 'VK_Component_Posts' ) ) {

	class VK_Component_Posts {

		/*
		 Basic method
		 Common Parts
		 Layout patterns
		 UI Helper method
		/*-------------------------------------------*/

		/*
		 Basic method
		/*-------------------------------------------*/
		public static function get_loop_post_view_options( $options ) {
			$default = array(
				'layout'                     => 'card',
				'display_image'              => true,
				'display_image_overlay_term' => true,
				'display_excerpt'            => false,
				'display_author'             => false,
				'display_date'               => true,
				'display_new'                => true,
				'display_taxonomies'         => false,
				'display_btn'                => false,
				'image_default_url'          => false,
				'overlay'                    => false,
				'btn_text'                   => __( 'Read more', 'lightning' ),
				'btn_align'                  => 'text-right',
				'new_text'                   => __( 'New!!', 'lightning' ),
				'new_date'                   => 7,
				'textlink'                   => true,
				'class_outer'                => '',
				'class_title'                => '',
				'body_prepend'               => '',
				'body_append'                => '',
			);
			$return  = apply_filters( 'vk_post_options', wp_parse_args( $options, $default ) );
			return $return;
		}

		/**
		 * [public description]
		 *
		 * @var [type]
		 */
		public static function get_view( $post, $options ) {

			$options = self::get_loop_post_view_options( $options );

			if ( $options['layout'] == 'card-horizontal' ) {
				$html = self::get_view_type_card_horizontal( $post, $options );
			} elseif ( $options['layout'] == 'media' ) {
				$html = self::get_view_type_media( $post, $options );
			} elseif ( $options['layout'] == 'postListText' ) {
				$html = self::get_view_type_text( $post, $options );
			} else {
				$html = self::get_view_type_card( $post, $options );
			}
			return $html;
		}

		public static function the_view( $post, $options ) {
			 echo wp_kses_post( self::get_view( $post, $options ) );
		}

		/**
		 * [public description]
		 *
		 * @var [type]
		 */
		public static function get_loop( $wp_query, $options, $options_loop = array() ) {

			// Outer Post Type classes
			$patterns                    = self::get_patterns();
			$loop_outer_class_post_types = array();
			if ( ! isset( $wp_query->query['post_type'] ) ) {
				$loop_outer_class_post_types[] = 'vk_posts-postType-post';
			} else {
				if ( is_array( $wp_query->query['post_type'] ) ) {
					foreach ( $wp_query->query['post_type'] as $key => $value ) {
						$loop_outer_class_post_types[] = 'vk_posts-postType-' . $value;
					}
				} else {
					$loop_outer_class_post_types[] = 'vk_posts-postType-' . $wp_query->query['post_type'];
				}
			}

			$loop_outer_class_post_types[] = 'vk_posts-layout-' . $options['layout'];

			// Additional loop option
			$loop_outer_class = implode( ' ', $loop_outer_class_post_types );

			if ( ! empty( $options_loop['class_loop_outer'] ) ) {
				$loop_outer_class .= ' ' . $options_loop['class_loop_outer'];
			}

			// Set post item outer col class
			if ( $options['layout'] !== 'postListText' ) {
				// If get info of column that deploy col to class annd add
				if ( empty( $options['class_outer'] ) ) {
					$options['class_outer'] = self::get_col_size_classes( $options );
				} else {
					$options['class_outer'] .= ' ' . self::get_col_size_classes( $options );
				}
			}

			// Set hidden class
			$hidden_class = array();
			if ( ! empty( $options['vkb_hidden'] ) ) {
				array_push( $hidden_class, 'vk_hidden' );
			} elseif ( ! empty( $options['vkb_hidden_xxl'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xxl' );
			} elseif ( ! empty( $options['vkb_hidden_xl'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xl' );
			} elseif ( ! empty( $options['vkb_hidden_lg'] ) ) {
				array_push( $hidden_class, 'vk_hidden-lg' );
			} elseif ( ! empty( $options['vkb_hidden_md'] ) ) {
				array_push( $hidden_class, 'vk_hidden-md' );
			} elseif ( ! empty( $options['vkb_hidden_sm'] ) ) {
				array_push( $hidden_class, 'vk_hidden-sm' );
			} elseif ( ! empty( $options['vkb_hidden_xs'] ) ) {
				array_push( $hidden_class, 'vk_hidden-xs' );
			}

			$loop = '';
			if ( $wp_query->have_posts() ) :

				$loop .= '<div class="vk_posts ' . esc_attr( $loop_outer_class ) . ' ' . esc_attr( implode( ' ', $hidden_class ) ) . '">';

				global $vk_posts_loop_item_count;
				$vk_posts_loop_item_count = 0;

				while ( $wp_query->have_posts() ) {

					$vk_posts_loop_item_count++;

					$wp_query->the_post();
					global $post;
					$loop .= self::get_view( $post, $options );

					$loop .= apply_filters( 'vk_posts_loop_item_after', '', $options );

				} // while ( have_posts() ) {

				$loop .= '</div>';

			endif;

			wp_reset_postdata();
			return $loop;
		}

		/**
		 * [public description]
		 *
		 * @var [type]
		 */
		public static function the_loop( $wp_query, $options, $options_loop = array() ) {
			echo self::get_loop( $wp_query, $options, $options_loop );
		}


		/*
		 Common Parts
		/*-------------------------------------------*/

		/**
		 * Common Part _ first DIV
		 *
		 * @var [type]
		 */
		public static function get_view_first_div( $post, $options ) {

			// Add layout Class
			if ( $options['layout'] == 'card-horizontal' ) {
				$class_outer = 'card card-post card-horizontal';
			} elseif ( $options['layout'] == 'card-noborder' ) {
				$class_outer = 'card card-noborder';
			} elseif ( $options['layout'] == 'card-intext' ) {
				$class_outer = 'card card-intext';
			} elseif ( $options['layout'] == 'media' ) {
				$class_outer = 'media';
			} elseif ( $options['layout'] == 'postListText' ) {
				$class_outer = 'postListText';
			} else {
				$class_outer = 'card card-post';
			}

			// Add Outer class
			if ( ! empty( $options['class_outer'] ) ) {
				$class_outer .= ' ' . esc_attr( $options['class_outer'] );
			}

			// Add btn class
			if ( $options['display_btn'] && $options['layout'] !== 'postListText' ) {
				$class_outer .= ' vk_post-btn-display';
			}
			global $post;
			$html = '<div id="post-' . esc_attr( $post->ID ) . '" class="vk_post vk_post-postType-' . esc_attr( $post->post_type ) . ' ' . join( ' ', get_post_class( $class_outer ) ) . '">';
			return $html;
		}

		/**
		 * Common Part _ post thumbnail
		 *
		 * @param  [type] $post    [description]
		 * @param  [type] $options [description]
		 * @param  string $class   [description]
		 * @return [type]          [description]
		 */
		public static function get_thumbnail_image( $post, $options, $attr = array() ) {

			$default = array(
				'class_outer' => '',
				'class_image' => '',
			);
			$classes = wp_parse_args( $attr, $default );

			$html = '';
			if ( $options['display_image'] ) {
				if ( $classes['class_outer'] ) {
					$classes['class_outer'] = ' ' . $classes['class_outer'];
				}

				$image_src = get_the_post_thumbnail_url( $post->ID, 'large' );
				if ( ! $image_src && $options['image_default_url'] ) {
					$image_src = esc_url( $options['image_default_url'] );
				}
				$style = ' style="background-image:url(' . $image_src . ')"';

				$html .= '<div class="vk_post_imgOuter' . $classes['class_outer'] . '"' . $style . '>';

				if ( $options['layout'] != 'card-intext' ){
					$html .= '<a href="' . get_the_permalink( $post->ID ) . '">';
				}

				if ( $options['overlay'] ) {
					$html .= '<div class="card-img-overlay">';
					$html .= $options['overlay'];
					$html .= '</div>';
				}

				if ( $options['display_image_overlay_term'] ) {

					$html     .= '<div class="card-img-overlay">';
					$term_args = array(
						'class' => 'vk_post_imgOuter_singleTermLabel',
					);
					if ( method_exists( 'Vk_term_color', 'get_single_term_with_color' ) ) {
						$html .= Vk_term_color::get_single_term_with_color( $post, $term_args );
					}
					$html .= '</div>';

				}
				if ( $classes['class_image'] ) {
					$image_class = 'vk_post_imgOuter_img ' . $classes['class_image'];
				} else {
					$image_class = 'vk_post_imgOuter_img';
				}

				$image_attr = array( 'class' => $image_class );
				$img        = get_the_post_thumbnail( $post->ID, 'medium', $image_attr );
				if ( $img ) {
					$html .= $img;
				} elseif ( $options['image_default_url'] ) {
					$html .= '<img src="' . esc_url( $options['image_default_url'] ) . '" alt="" class="' . $image_class . '" loading="lazy" />';
				}

				if ( $options['layout'] != 'card-intext' ){
					$html .= '</a>';
				}

				$html .= '</div><!-- [ /.vk_post_imgOuter ] -->';
			} // if ( $options['display_image'] ) {

			return $html;
		}

		/**
		 * Common Part _ post body
		 *
		 * @var [type]
		 */
		public static function get_view_body( $post, $options ) {
			// $default = array(
			// 'textlink' => false,
			// );
			// $attr = wp_parse_args( $attr, $default );

			$layout_type = $options['layout'];
			if ( $layout_type == 'card-horizontal' || $layout_type == 'card-noborder' || $layout_type == 'card-intext' ) {
				$layout_type = 'card';
			}

			$html = '';

			$html .= '<div class="vk_post_body ' . $layout_type . '-body">';

			if ( ! empty( $options['body_prepend'] ) ) {
				$html .= $options['body_prepend'];
			}

			$html .= '<h5 class="vk_post_title ' . $layout_type . '-title">';

			/*
			?????????????????????????????????????????????????????????????????????????????????????????????DOM??????????????????????????????
			????????????????????????????????????????????????
			*/
			if ( $options['layout'] == 'card-intext' ){
				$options['textlink'] = false;
			}

			if ( $options['textlink'] ) {
				$html .= '<a href="' . get_the_permalink( $post->ID ) . '">';
			}

			$html .= get_the_title( $post->ID );

			if ( $options['display_new'] ) {
				$today = date_i18n( 'U' );
				$entry = get_the_time( 'U', $post );
				$kiji  = date( 'U', ( $today - $entry ) ) / 86400;
				if ( $options['new_date'] > $kiji ) {
					$html .= '<span class="vk_post_title_new">' . $options['new_text'] . '</span>';
				}
			}

			if ( $options['textlink'] ) {
				$html .= '</a>';
			}

			$html .= '</h5>';

			if ( $options['display_date'] ) {
				$html .= '<div class="vk_post_date ' . $layout_type . '-date published">';
				$html .= esc_html( get_the_date( '', $post->ID ) );
				$html .= '</div>';
			}

			if ( $options['display_excerpt'] ) {
				$html .= '<p class="vk_post_excerpt ' . $layout_type . '-text">';
				$html .= wp_kses_post( get_the_excerpt( $post->ID ) );
				$html .= '</p>';
			}

			if ( $options['display_author'] ) {
				$author = get_the_author();
				if ( $author ) {
					$html .= '<p class="vcard vk_post_author" itemprop="author">';

					// VK Post Author Display ??????????????????
					$profile_image_id = get_the_author_meta( 'user_profile_image' );
					$html .= '<span class="vk_post_author_image">';
					if ( $profile_image_id ) {
						$profile_image_src = wp_get_attachment_image_src( $profile_image_id, 'thumbnail' );
						$html      .= '<img class="vk_post_author_image" src="' . $profile_image_src[0] . '" alt="' . esc_attr( $author ) . '" />';
					} else {
						$html .= get_avatar( get_the_author_meta( 'email' ), 100 );
					}
					$html .= '</span>';

					$html .= '<span class="fn vk_post_author_name" itemprop="name">';
					$html .= esc_html( $author );
					$html .= '</span></p>';
				} // if author
			}

			if ( $options['display_taxonomies'] ) {
				$args          = array(
					'template'      => '<dt class="vk_post_taxonomy_title"><span class="vk_post_taxonomy_title_inner">%s</span></dt><dd class="vk_post_taxonomy_terms">%l</dd>',
					'term_template' => '<a href="%1$s">%2$s</a>',
				);
				$taxonomies	= get_the_taxonomies( $post->ID, $args );
				$exclusion	= array( 'product_type' );
				// ????????????????????????????????????????????????????????????????????????
				$exclusion	= apply_filters( 'vk_get_display_taxonomies_exclusion', $exclusion );

				if ( is_array( $exclusion ) ){
					foreach ( $exclusion as $key => $value ){
						unset( $taxonomies[$value] );
					}
				}
				if ( $taxonomies ) {
					$html .= '<div class="vk_post_taxonomies">';
					foreach ( $taxonomies as $key => $value ) {
						$html .= '<dl class="vk_post_taxonomy vk_post_taxonomy-' . $key . '">' . $value . '</dl>';
					} // foreach
					$html .= '</div>';
				} // if ($taxonomies)
			}

			if ( $options['textlink'] ) {

				if ( $options['display_btn'] ) {
					$button_options = array(
						'outer_id'       => '',
						'outer_class'    => '',
						'btn_text'       => $options['btn_text'],
						'btn_url'        => get_the_permalink( $post->ID ),
						'btn_class'      => 'btn btn-sm btn-primary vk_post_btn',
						'btn_target'     => '',
						'btn_ghost'      => false,
						'btn_color_text' => '',
						'btn_color_bg'   => '',
						'shadow_use'     => false,
						'shadow_color'   => '',
					);

					// $text_align = '';
					// if ( $options['btn_align'] == 'right' ) {
					// $text_align = ' text-right';
					// }
					$html .= '<div class="vk_post_btnOuter ' . $options['btn_align'] . '">';
					$html .= VK_Component_Button::get_view( $button_options );
					$html .= '</div>';
				}

			}

			if ( ! empty( $options['body_append'] ) ) {
				$html .= $options['body_append'];
			}

			$html .= '</div><!-- [ /.' . $layout_type . '-body ] -->';

			return $html;
		}


		/*
		 Layout patterns
		/*-------------------------------------------*/

		public static function get_patterns() {

			$patterns = array(
				'card'            => array(
					'label'             => __( 'Card', 'lightning' ),
					'class_posts_outer' => '',
				),
				'card-noborder'            => array(
					'label'             => __( 'Card Noborder', 'lightning' ),
					'class_posts_outer' => '',
				),
				'card-horizontal' => array(
					'label'             => __( 'Card Horizontal', 'lightning' ),
					'class_posts_outer' => '',
				),
				'media'           => array(
					'label'             => __( 'Media', 'lightning' ),
					'class_posts_outer' => 'media-outer',
				),
				'postListText'    => array(
					'label'             => _x( 'Text 1 Column', 'post list type', 'lightning' ),
					'class_posts_outer' => 'postListText-outer',
				),
			);
			return $patterns;
		}

		/**
		 * Card
		 *
		 * @var [type]
		 */
		public static function get_view_type_card( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );

			$attr = array(
				'class_outer' => '',
				'class_image' => 'card-img-top',
			);

			$html_body = '';
			$html_body .= self::get_thumbnail_image( $post, $options, $attr );
			$html_body .= self::get_view_body( $post, $options );

			if ( $options['layout'] == 'card-intext' ){

				$html .= '<a href="' . esc_url( get_the_permalink( $post->ID ) ) . '" class="card-intext-inner">';

				// a????????????a??????????????????Chrome??????????????????????????????a?????????????????????????????????????????????????????????a?????????span???????????????
				$html_body = str_replace( "<a", "<span", $html_body );
				$html_body = str_replace( "href=", "data-url=", $html_body );
				$html_body = str_replace( "a>", "span>", $html_body );

				$html .= $html_body;

				$html .= '</a>';

			} else {
				$html .= $html_body;
			}

			$html .= '</div><!-- [ /.card ] -->';
			return $html;
		}

		/**
		 * Card horizontal
		 *
		 * @var [type]
		 */
		public static function get_view_type_card_horizontal( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );
			// $html .= '<a href="' . get_the_permalink( $post->ID ) . '" class="card-horizontal-inner">';
			$html .= '<div class="row no-gutters card-horizontal-inner-row">';

			// $image_src = '';
			if ( $options['display_image'] ) {
				$html .= '<div class="col-5 card-img-outer">';
				$attr  = array(
					'class_outer' => '',
					'class_image' => 'card-img card-img-use-bg',
				);
				$html .= self::get_thumbnail_image( $post, $options, $attr );
				$html .= '</div><!-- /.col -->';
				$html .= '<div class="col-7">';
			}

			$html .= self::get_view_body( $post, $options );

			if ( $options['display_image'] ) {
				$html .= '</div><!-- /.col -->';
			}

			$html .= '</div><!-- [ /.row ] -->';
			// $html .= '</a>';
			$html .= '</div><!-- [ /.card ] -->';
			return $html;
		}

		/**
		 * Media
		 *
		 * @var [type]
		 */
		public static function get_view_type_media( $post, $options ) {
			$html  = '';
			$html .= self::get_view_first_div( $post, $options );
			if ( $options['display_image'] ) {
				// $html .= '<a href="' . get_the_permalink() . '" class="media-img">';
				$attr  = array(
					'class_outer' => 'media-img',
					'class_image' => '',
				);
				$html .= self::get_thumbnail_image( $post, $options, $attr );
				// $html .= '</a>';
			}

			// $attr  = array(
			// 'textlink' => true,
			// );
			$html .= self::get_view_body( $post, $options );

			$html .= '</div><!-- [ /.media ] -->';
			return $html;
		}

		/**
		 * Text
		 *
		 * @var [type]
		 */
		public static function get_view_type_text( $post, $options ) {

			$layout_type = $options['layout'];

			$html  = '';
			$html .= self::get_view_first_div( $post, $options );

			if ( $options['display_date'] ) {
				$html .= '<span class="postListText_date published">';
				$html .= esc_html( get_the_date( '', $post->ID ) );
				$html .= '</span>';
			}

			if ( $options['display_image_overlay_term'] ) {
				$html     .= '<span class="postListText_singleTermLabel">';
				$term_args = array(
					'class' => 'postListText_singleTermLabel_inner',
					'link'  => true,
				);
				if ( method_exists( 'Vk_term_color', 'get_single_term_with_color' ) ) {
					$html .= Vk_term_color::get_single_term_with_color( $post, $term_args );
				}
				$html .= '</span>';
			}

			$html .= '<p class="postListText_title"><a href="' . get_the_permalink( $post->ID ) . '">';
			$html .= get_the_title( $post->ID );
			$html .= '</a>';

			if ( $options['display_new'] ) {
				$today = date_i18n( 'U' );
				$entry = get_the_time( 'U' );
				$kiji  = date( 'U', ( $today - $entry ) ) / 86400;
				if ( $options['new_date'] > $kiji ) {
					$html .= '<span class="vk_post_title_new">' . $options['new_text'] . '</span>';
				}
			}

			$html .= '</p>';

			$html .= '</div>';
			return $html;
		}

		/*
		 UI Helper method
		/*-------------------------------------------*/

		/**
		 * Convert col-count from inputed column count.
		 *
		 * @param  integer $input_col [description]
		 * @return [type]             [description]
		 */
		public static function get_col_converted_size( $input_col = 4 ) {
			if ( $input_col == 1 ) {
				$col = 12;
			} elseif ( $input_col == 2 ) {
				$col = 6;
			} elseif ( $input_col == 3 ) {
				$col = 4;
			} elseif ( $input_col == 4 ) {
				$col = 3;
			} elseif ( $input_col == 6 ) {
				$col = 2;
			} else {
                $col = 4;
            }
			return strval( $col );
		}

		/**
		 * Get all size col classes
		 *
		 * @param  [type] $attributes inputed col numbers array
		 * @return [type]             [description]
		 */
		public static function get_col_size_classes( $attributes ) {
			$col_class_array = array();
			$sizes           = array( 'xs', 'sm', 'md', 'lg', 'xl', 'xxl' );
			foreach ( $sizes as $key => $size ) {
				if ( ! empty( $attributes[ 'col_' . $size ] ) ) {
					$col_class_array[] = 'vk_post-col-' . $size . '-' . self::get_col_converted_size( $attributes[ 'col_' . $size ] );
				}
			}
			$col_class = implode( ' ', $col_class_array );
			return $col_class;
		}

	}
}
