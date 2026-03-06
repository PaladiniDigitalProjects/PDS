<?php

namespace ProDevign\BlockMeister\Pattern_Builder;

use ProDevign\BlockMeister\BlockMeister;
use ProDevign\BlockMeister\Context;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use function ProDevign\BlockMeister\blockmeister_license;
class Block_Pattern_Registry {
    /**
     * Post_Editor constructor.
     *
     */
    public function __construct() {
    }

    /**
     * Initialize
     */
    public function init() {
        add_action( 'init', function () {
        }, 5 );
        // needs to be a higher priority then which is used by core for pattern registration (=10)
        //disabled because currently handled in block-scope-css-editor:
        //add_filter( 'block_type_metadata_settings', [ $this, 'register_custom_attributes'],10,2);
        add_action( 'init', [$this, 'init_pattern_registration'], 1020 );
        // priority needs to be lower than init action of BlockMeister_Pattern_Category_Taxonomy (=1010)
        add_action( 'init', function () {
        }, 1020 );
        // needs to be a lower priority then which is used by core for pattern registration (=10)
        add_filter(
            'rest_request_after_callbacks',
            [$this, 'filter_inactive_patterns_from_patterns_rest_response'],
            10,
            3
        );
    }

    // handled in block-scope-css-editor:
    //	/**
    //	 * Add custom attributes to all block types
    //	 *
    //	 * @param $settings
    //	 * @param $metadata
    //	 *
    //	 * @return array
    //	 *
    //	 * @since 3.8.1
    //	 */
    //	public function register_custom_attributes( $settings, $metadata ) {
    //		if ( blockmeister_license()->is_plan_or_trial__premium_only( 'pro' ) ) {
    //			$settings['attributes']['bmCSS'] = array(
    //				'type'    => 'string',
    //				'default' => ''
    //			);
    //		}
    //		return $settings;
    //	}
    /**
     * Initialize pattern registration related things.
     */
    public function init_pattern_registration() {
        $this->register_custom_block_patterns();
    }

    /**
     * Server side custom pattern registration.
     *
     * Security notes:
     * - Escaping:      None of the registration args will be directly output to the screen.
     *                  Therefore, escaping isn't necessary at this point and will be done by WP when
     *                  the patterns are rendered for preview or as rendered in a post/page.
     * - Sanitization:  The registration args are sanitized to prevent unexpected results though.
     */
    private function register_custom_block_patterns() {
        $current_post_id = ( (int) isset( $_GET['post'] ) ? $_GET['post'] : -1 );
        // for use in the pattern list table we need to get posts of any status
        $post_status = ( Context::is_blockmeister_pattern_list_table() ? 'any' : 'publish' );
        $args = [
            'numberposts'      => -1,
            'post_type'        => Pattern_Builder::POST_TYPE,
            'post_status'      => $post_status,
            'suppress_filters' => Context::is_blockmeister_pattern_list_table(),
        ];
        $blockmeister_pattern_posts = get_posts( $args );
        foreach ( $blockmeister_pattern_posts as $block_pattern ) {
            if ( $block_pattern->ID === $current_post_id ) {
                continue;
                // skip currently being edited pattern to prevent self nesting
            }
            $category_slugs = wp_get_post_terms( $block_pattern->ID, 'pattern_category', [
                'fields' => 'id=>slug',
            ] );
            $pattern_categories = ( sizeof( $category_slugs ) > 0 ? explode( ",", implode( ",", $category_slugs ) ) : [] );
            // explode/implode re-indexes array
            $keyword_names = wp_get_post_terms( $block_pattern->ID, 'pattern_keyword', [
                'fields' => 'names',
            ] );
            $pattern_keywords = ( sizeof( $keyword_names ) > 0 ? explode( ",", implode( ",", $keyword_names ) ) : [] );
            // explode/implode re-indexes array
            $filtered_post_content = wp_kses_post( $block_pattern->post_content );
            if ( $filtered_post_content !== $block_pattern->post_content ) {
                // some content was stripped out by kses method
                if ( current_user_can( 'unfiltered_html' ) ) {
                    $filtered_post_content = $block_pattern->post_content;
                    // potential unsafe content is the responsibility of user
                }
            }
            $pattern_props = [
                'title'         => strip_tags( $block_pattern->post_title ),
                'description'   => strip_tags( $block_pattern->post_excerpt ),
                'content'       => $filtered_post_content,
                'categories'    => array_map( 'strip_tags', $pattern_categories ),
                'keywords'      => array_map( 'strip_tags', $pattern_keywords ),
                'viewportWidth' => (int) get_post_meta( $block_pattern->ID, "_bm_viewport_width", true ),
            ];
            $block_namespace = BlockMeister::get_default_block_namespace();
            $block_pattern_name = ( $block_pattern->post_status === 'draft' ? 'draft-' . $block_pattern->ID : sanitize_key( $block_pattern->post_name ) );
            $pattern_name = "{$block_namespace}/{$block_pattern_name}";
            register_block_pattern( $pattern_name, $pattern_props );
        }
    }

    /**
     * Filters the response immediately after executing any REST API callbacks.
     * Filters inactive patterns, unless pattern list table.
     *
     * @since 3.1.4
     *
     * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send to the client.
     *                                                                   Usually a WP_REST_Response or WP_Error.
     * @param array                                            $handler  Route handler used for the request.
     * @param WP_REST_Request                                  $request  Request used to generate the response.
     */
    public function filter_inactive_patterns_from_patterns_rest_response( $response, $handler, $request ) {
        $route = $request->get_route();
        $is_patterns_rest_request = $route === '/wp/v2/block-patterns/patterns';
        $is_pattern_directory_rest_request = $route === '/wp/v2/pattern-directory/patterns';
        $is_rest_request_needs_filtering = $is_patterns_rest_request || $is_pattern_directory_rest_request;
        if ( is_wp_error( $response ) || !$is_rest_request_needs_filtering || Context::is_blockmeister_pattern_list_table() ) {
            return $response;
        }
        $response = rest_ensure_response( $response );
        $received_patterns = $response->get_data();
        $inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );
        foreach ( $received_patterns as $index => $directory_pattern ) {
            $is_core_directory_pattern = $is_pattern_directory_rest_request && $request->has_param( 'keyword' ) && $request->get_param( 'keyword' ) === 11;
            // 11 = 'core' keyword
            $is_featured_directory_pattern = $is_pattern_directory_rest_request && $request->has_param( 'category' ) && $request->get_param( 'category' ) === 26;
            // 26 = 'featured' category
            if ( $is_pattern_directory_rest_request ) {
                $namespace = ( $is_core_directory_pattern ? 'core/' : '' );
                // non core directory patterns won't have a namespace/prefix
                $pattern_name_ns = $namespace . sanitize_title( $directory_pattern['title'] );
            } else {
                // local patterns are already namespaced
                $pattern_name_ns = $directory_pattern['name'];
            }
            if ( in_array( $pattern_name_ns, $inactive_patterns ) ) {
                unset($received_patterns[$index]);
            }
            // Note: In _load_remote_featured_patterns WP filters out the registration of featured directory patterns
            //       that are also registered as core directory pattern. Now if a users deactivated that core pattern
            //       the featured pattern will be registered. The user is now confused why the pattern he deactivated
            //       still shows.
            //       Hence, if the core pattern is deactivated we also need to filter out the featured copy:
            if ( $is_featured_directory_pattern ) {
                // if same core pattern inactive then also filter out the featured copy:
                $core_pattern_name_ns = 'core/' . sanitize_title( $directory_pattern['title'] );
                if ( in_array( $core_pattern_name_ns, $inactive_patterns ) ) {
                    unset($received_patterns[$index]);
                }
            }
        }
        $response->set_data( array_values( $received_patterns ) );
        return $response;
    }

}
