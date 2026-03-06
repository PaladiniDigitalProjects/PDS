<?php

namespace ProDevign\BlockMeister\Pattern_Builder\Admin;

use ProDevign\BlockMeister\BlockMeister;
use ProDevign\BlockMeister\Context;
use ProDevign\BlockMeister\JSON_File_Uploader;
use ProDevign\BlockMeister\Pattern_Builder\BlockMeister_Pattern_Category_Taxonomy;
use ProDevign\BlockMeister\Pattern_Builder\Pattern_Builder;
use ProDevign\BlockMeister\Utils;
use WP_Error;
use function ProDevign\BlockMeister\blockmeister_license;
class BlockMeister_Pattern_List_Table {
    /**
     * @const float
     */
    const PREVIEW_SCALE_FACTOR = 0.32;

    /**
     * Holds regular registered patterns and post based patterns with *any* status
     * (see Custom_Block_Pattern_Registry::register_custom_block_patterns() where post status is set to any for list table use)
     *
     * @var array
     */
    private $registered_block_patterns = [];

    public function __construct() {
    }

    public function init() {
        if ( !Context::is_blockmeister_pattern_list_table() ) {
            return;
        }
        if ( isset( $_REQUEST['action'] ) ) {
            if ( $is_not_bulk_action = !isset( $_REQUEST['post'] ) ) {
                add_filter( 'upload_mimes', function ( $allowed_mime_types ) {
                    $allowed_mime_types['json'] = "application/json";
                    return $allowed_mime_types;
                } );
                add_action( 'admin_init', [$this, 'handle_action_request'], 1100 );
            }
        }
        if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] === Pattern_Builder::POST_TYPE ) {
            add_action( 'admin_notices', [$this, 'show_admin_notice_for_action_request_result'] );
            add_action( 'current_screen', function () {
                $this->registered_block_patterns = \WP_Block_Patterns_Registry::get_instance()->get_all_registered();
            }, 15 );
            // needs a lower priority then used in Block_Pattern_Registry::init (=10)
            add_action( 'restrict_manage_posts', [$this, 'category_filter'] );
            add_filter( 'manage_blockmeister_pattern_posts_columns', [$this, 'customize_blockmeister_pattern_posts_columns'] );
            add_filter(
                'posts_results',
                [$this, 'filter_table_list_rows_on_category'],
                10,
                2
            );
            add_action(
                'manage_blockmeister_pattern_posts_custom_column',
                [$this, 'render_custom_columns'],
                10,
                3
            );
            add_filter(
                'post_date_column_status',
                [$this, 'override_status_in_date_column'],
                10,
                2
            );
            add_filter(
                'post_row_actions',
                [$this, 'add_and_remove_row_actions'],
                10,
                2
            );
            add_filter(
                'post_class',
                [$this, 'assign_status_and_virtual_post_classes'],
                10,
                3
            );
            add_action( 'admin_head-edit.php', [$this, 'add_style'] );
            add_action( 'admin_head-edit.php', [$this, 'add_script'] );
            add_action( 'admin_init', [$this, 'filter_out_negative_post_ids_on_bulk_trash_request'] );
            add_filter( 'bulk_actions-edit-blockmeister_pattern', [$this, 'add_and_remove_bulk_actions'] );
            add_filter(
                'handle_bulk_actions-edit-blockmeister_pattern',
                [$this, 'handle_bulk_actions'],
                10,
                3
            );
            add_filter(
                'bulk_post_updated_messages',
                [$this, 'filter_bulk_action_result_messages'],
                10,
                2
            );
            add_filter( 'disable_months_dropdown', '__return_true' );
            add_filter( 'edit_blockmeister_pattern_per_page', function () {
                return 998;
            } );
        }
    }

    /**
     * Adds the block pattern category filter to the table nav area
     */
    public function category_filter() {
        global $typenow;
        if ( $typenow === 'blockmeister_pattern' ) {
            $bpc_taxonomy = get_taxonomy( BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME );
            wp_dropdown_categories( [
                'show_option_all' => $bpc_taxonomy->labels->all_items,
                'taxonomy'        => BlockMeister_Pattern_Category_Taxonomy::TAXONOMY_NAME,
                'name'            => 'category',
                'orderby'         => 'name',
                'value_field'     => 'slug',
                'selected'        => ( isset( $_GET['category'] ) ? $_GET['category'] : 0 ),
                'show_count'      => false,
                'hide_empty'      => false,
            ] );
        }
    }

    /**
     * Remove non-custom block patterns on bulk trash requests
     */
    public function filter_out_negative_post_ids_on_bulk_trash_request() {
        $is_bulk_trash_request = isset( $_GET['action'] ) && $_GET['action'] === 'trash' && isset( $_GET['post_type'] ) && $_GET['post_type'] === Pattern_Builder::POST_TYPE && isset( $_GET['post'] );
        if ( $is_bulk_trash_request ) {
            foreach ( $_GET['post'] as $index => $post_id ) {
                if ( $post_id <= 0 ) {
                    $x = 1;
                    //array_splice($_GET['post'], $index, 1);
                    unset($_GET['post'][$index]);
                }
            }
            $_REQUEST['post'] = $_GET['post'];
        }
    }

    private function get_count_all() {
        return sizeof( $this->registered_block_patterns );
    }

    private function get_count_inactive() {
        $inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );
        $count = 0;
        foreach ( $this->registered_block_patterns as $registered_block_pattern ) {
            if ( $is_pattern_inactive = in_array( $registered_block_pattern['name'], $inactive_patterns, true ) ) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Helper to create links to edit.php with params.
     *
     * @param string[] $args Associative array of URL parameters for the link.
     * @param string $label Link text.
     * @param string $class Optional. Class attribute. Default empty string.
     *
     * @return string The formatted link string.
     *
     */
    private function get_edit_link( $args, $label, $class = '' ) {
        $url = add_query_arg( $args, 'edit.php' );
        $class_html = '';
        $aria_current = '';
        if ( !empty( $class ) ) {
            $class_html = sprintf( ' class="%s"', esc_attr( $class ) );
            if ( 'current' === $class ) {
                $aria_current = ' aria-current="page"';
            }
        }
        return sprintf(
            '<a href="%s"%s%s>%s</a>',
            esc_url( $url ),
            $class_html,
            $aria_current,
            $label
        );
    }

    /**
     * Filters the list of available list table views.
     * By default WP generates filters (and counts), like: All (9) | Published (6) | Trashed (1) | ...
     *
     * @param string[] $views An array of available list table views.
     *
     * @return string[]
     */
    public function customize_table_view_links_filtered_on_category( array $views ) {
        $count_filtered_category = $this->get_count_inactive();
        $views['test'] = 'test';
        //		$extra_views = [
        //
        //			'active' => $this->get_edit_link(
        //				[ 'post_type' => 'blockmeister_pattern', 'filter' => 'active' ],
        //				esc_html__( 'Active', 'blockmeister' ) . " <span class='count'>($count_active)</span>",
        //				isset( $_GET['filter'] ) && $_GET['filter'] === 'active' ? 'current' : ''
        //			),
        //
        //			'inactive' => $this->get_edit_link(
        //				[ 'post_type' => 'blockmeister_pattern', 'filter' => 'inactive' ],
        //				esc_html__( 'Inactive', 'blockmeister' ) . " <span class='count'>($count_inactive)</span>",
        //				isset( $_GET['filter'] ) && $_GET['filter'] === 'inactive' ? 'current' : ''
        //			),
        //
        //		];
        //
        //		$views_part_a = array_splice( $views, 0, 1 );
        //		$views        = array_merge( $views_part_a, $extra_views, $views );
        return $views;
    }

    /**
     * Filters the status text of the post.
     * If this is a pseudo post (registered pattern) then override the status.
     *
     * @param string $status The status text.
     * @param \WP_Post $post Post object.
     *
     * @return string
     */
    public function override_status_in_date_column( $status, $post ) {
        if ( $post->ID <= 0 ) {
            return esc_html__( 'Registered', 'blockmeister' );
        }
        return $status;
    }

    /**
     * Shows notices related to row actions.
     *
     * Note: bulk actions are handled by core functions in edit.php (and this::filter_bulk_action_result_messages)
     */
    public function show_admin_notice_for_action_request_result() {
        if ( !isset( $_GET['status'] ) || !isset( $_GET['notice'] ) ) {
            return;
        }
        echo "<div class='notice notice-" . sanitize_html_class( $_GET['status'], 'success' ) . " is-dismissible'>";
        echo "  <p>" . esc_html( wp_unslash( $_GET['notice'] ) ) . "</p>";
        echo "</div>";
    }

    public function add_script() {
        if ( $inject_import_elements = current_user_can( 'publish_blockmeister_patterns' ) && current_user_can( 'import' ) ) {
            $submit_to = add_query_arg( [
                'post_status' => ( isset( $_GET['post_status'] ) ? $_GET['post_status'] : false ),
                'filter'      => ( isset( $_GET['filter'] ) ? $_GET['filter'] : false ),
            ], self_admin_url( "edit.php?post_type=blockmeister_pattern" ) );
            // url will be escaped in JSON_File_Uploader
            $file_uploader = new JSON_File_Uploader($submit_to);
            $file_uploader->init( true );
            $file_uploader_html = $file_uploader->get_html();
            $import_label = esc_html__( 'Import', 'blockmeister' );
        }
        ?>
        <script id="blockmeister-pattern-list-table-script">

            jQuery(document).ready(function ($) {

				<?php 
        if ( $inject_import_elements ) {
            ?>

                // inject import button
                var importButton = $('.wp-heading-inline + .page-title-action').after(`
                        <a href="#" id="import-button" class="page-title-action"><?php 
            echo esc_html( $import_label );
            ?></a>
                    `);

                // inject upload form
                var uploadForm = $('#import-button').after(`
                        <?php 
            echo $file_uploader_html;
            ?>
                    `);

                $('#import-button').on('click', function (e) {
                    $('form.imports').slideToggle(200);
                    e.preventDefault();
                });

				<?php 
        }
        ?>

                // remove links from title of registered patterns (so no actual post)
                $('table.wp-list-table tr.virtual-post a.row-title').contents().unwrap();

            });
        </script>
		<?php 
    }

    public function add_style() {
        global $post_type;
        if ( 'blockmeister_pattern' !== $post_type ) {
            return;
        }
        ?>
        <style id="blockmeister-pattern-list-table-style">
            .search-box, .tablenav-pages, .screen-options { /* <-- screen-options = pagination section !!! */
                display: none;
            }

            table.wp-list-table {
                table-layout: auto;
            }

        </style>
		<?php 
    }

    /**
     * Filters the list of CSS class names for the current pattern and adds either
     * active or inactive class AND virtual-post class if applicable
     *
     * @param string[] $classes An array of post class names.
     * @param string[] $class An array of additional class names added to the post.
     * @param int $post_id The post ID.
     *
     * @return string[] The update $classes array
     */
    public function assign_status_and_virtual_post_classes( array $classes, array $class, $post_id ) {
        // status class:
        $pattern_name = $this->get_pattern_name( $post_id );
        // check if pattern_name is active or not and add respective class
        $inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );
        $is_pattern_inactive = in_array( $pattern_name, $inactive_patterns, true );
        $classes[] = ( $is_pattern_inactive ? 'inactive' : 'active' );
        // virtual post class
        if ( $post_id < 0 ) {
            $classes[] = 'virtual-post';
        }
        return $classes;
    }

    /**
     * Looks up and returns the pattern name associated with the given post.
     * Note: drafts are by default unnamed, so we generate a name for those.
     *
     * @param int|object $post If the id <= 0 then this is pointing to a pseudo post, i.e. a registered pattern added as a post.
     *
     * @return mixed|string
     */
    private function get_pattern_name( $post ) {
        $is_post_object = is_object( $post );
        $post_id = ( $is_post_object ? $post->ID : (int) $post );
        $is_registered_block_pattern = $post_id <= 0;
        if ( $is_registered_block_pattern ) {
            return $this->registered_block_patterns[-$post_id]['name'];
        }
        if ( !$is_post_object ) {
            $post = get_post( $post_id );
        }
        // for drafts, we need to generate a name (wp leaves names empty while posts are in draft state)
        $post_name = ( $post->post_status === 'draft' ? 'draft-' . $post_id : $post->post_name );
        $pattern_name = BlockMeister::get_default_block_namespace() . '/' . $post_name;
        return $pattern_name;
    }

    /**
     * By default, WordPress will place the Categories column after the Author column.
     * With this filter we reverse that.
     *
     * @param $columns
     *
     * @return array The reordered columns
     */
    public function customize_blockmeister_pattern_posts_columns( $columns ) {
        $author_column_label = $columns['author'];
        // reuse later, so no need to translate
        unset($columns['taxonomy-pattern_category']);
        unset($columns['taxonomy-pattern_keyword']);
        unset($columns['author']);
        $columns_ordered = [];
        foreach ( $columns as $key => $title ) {
            if ( $key == 'date' ) {
                // Put the custom columns before the Author column
                //$columns_ordered['pattern_enabled']    = esc_html__( 'Enabled', 'blockmeister' );
                $columns_ordered['pattern_categories'] = esc_html__( 'Categories', 'blockmeister' );
                $columns_ordered['pattern_keywords'] = esc_html__( 'Keywords', 'blockmeister' );
                $columns_ordered['pattern_author'] = $author_column_label;
            }
            $columns_ordered[$key] = $title;
        }
        return $columns_ordered;
    }

    /**
     * Echoes the content of the custom column per given post_id.
     * Fires for each custom column of list table.
     *
     * @param string $column_name The name of the column to display.
     * @param int $post_id The current post ID.
     *
     */
    public function render_custom_columns( $column_name, $post_id ) {
        $registered_block_pattern = null;
        if ( $is_registered_block_pattern = $post_id <= 0 ) {
            $registered_block_pattern = $this->registered_block_patterns[-$post_id];
        }
        // don't render a hidden column:
        $hidden_columns = get_hidden_columns( 'edit-blockmeister_pattern' );
        if ( in_array( $column_name, $hidden_columns ) ) {
            return;
        }
        switch ( $column_name ) {
            case "pattern_categories":
                $this->render_pattern_categories_column( $post_id, $is_registered_block_pattern, $registered_block_pattern );
                break;
            case "pattern_keywords":
                $this->render_pattern_keywords_column( $post_id, $is_registered_block_pattern, $registered_block_pattern );
                break;
            case "pattern_author":
                $this->render_pattern_author_column( $post_id, $is_registered_block_pattern, $registered_block_pattern );
                break;
            default:
                $this->render_unknown_column( $post_id, $is_registered_block_pattern, $column_name );
        }
    }

    private function render_pattern_categories_column( $post_id, $is_registered_block_pattern, $registered_block_pattern ) {
        $categories = '';
        $base_list_table_url = get_admin_url() . 'edit.php?post_type=blockmeister_pattern&filter_action=Filter&category=';
        if ( $is_registered_block_pattern ) {
            // get data from registered block pattern
            $category_registry = \WP_Block_Pattern_Categories_Registry::get_instance();
            $pattern_categories = ( isset( $registered_block_pattern['categories'] ) ? $registered_block_pattern['categories'] : [] );
            foreach ( $pattern_categories as $category_name ) {
                if ( $registered_categories = $category_registry->get_registered( $category_name ) ) {
                    $category_list_table_url = $base_list_table_url . esc_html( $registered_categories['name'] );
                    $category_link = '<a href="' . $category_list_table_url . '">' . esc_html( $registered_categories['label'] ) . '</a>';
                    $categories .= (( $categories !== '' ? ', ' : '' )) . $category_link;
                }
            }
        } else {
            // custom block pattern
            $object_terms = wp_get_object_terms( $post_id, 'pattern_category' );
            foreach ( $object_terms as $object_term ) {
                $category_list_table_url = $base_list_table_url . esc_html( $object_term->slug );
                $category_link = '<a href="' . $category_list_table_url . '">' . esc_html( $object_term->name ) . '</a>';
                $categories .= (( $categories !== '' ? ', ' : '' )) . $category_link;
            }
        }
        $categories = ( $categories !== '' ? $categories : '—' );
        echo $categories;
    }

    private function render_pattern_keywords_column( $post_id, $is_registered_block_pattern, $registered_block_pattern ) {
        $keywords = '';
        if ( $is_registered_block_pattern ) {
            $pattern_keywords = ( isset( $registered_block_pattern['keywords'] ) ? $registered_block_pattern['keywords'] : [] );
            foreach ( $pattern_keywords as $keyword_name ) {
                //$registered_keywords = $category_registry->get_registered( $keyword_name );
                $keywords .= (( $keywords !== '' ? ', ' : '' )) . $keyword_name;
            }
        } else {
            $object_terms = wp_get_object_terms( $post_id, 'pattern_keyword' );
            foreach ( $object_terms as $object_term ) {
                $keywords .= (( $keywords !== '' ? ', ' : '' )) . $object_term->name;
            }
        }
        $keywords = ( $keywords !== '' ? $keywords : '—' );
        echo esc_html( $keywords );
    }

    private function render_pattern_author_column( $post_id, $is_registered_block_pattern, $registered_block_pattern ) {
        if ( $is_registered_block_pattern ) {
            $name_parts = explode( '/', $registered_block_pattern['name'] );
            // remote loaded core patterns are not namespaced, so we give those a default of 'pattern directory'!
            $namespace = ( sizeof( $name_parts ) > 1 ? $name_parts[0] : 'pattern directory' );
            echo ucfirst( esc_html( $namespace ) );
        } else {
            $post = get_post( $post_id );
            $author_id = $post->post_author;
            $author = get_the_author_meta( 'display_name', $author_id );
            echo esc_html( $author );
        }
    }

    /**
     * We only allow rendering of custom column content for custom patterns.
     * This allows e.g. translation plugins to add a translations column.
     *
     * @param $post_id
     * @param $is_registered_block_pattern
     * @param $column_name
     */
    private function render_unknown_column( $post_id, $is_registered_block_pattern, $column_name ) {
        if ( $is_registered_block_pattern ) {
            echo "<span class='not-applicable'>—</span><style>tr#post-{$post_id} > td.column-{$column_name} *:not(.not-applicable) { display: none}</style>";
        }
    }

    /**
     * Filters the action links displayed for each term in the blockmeister_pattern list table.
     *
     * @param string[] $actions An array of row action links.
     * @param \WP_Post $post The post object.
     *
     * @return array|string[]
     */
    public function add_and_remove_row_actions( $actions, $post ) {
        $is_registered_block_pattern = $post->ID <= 0;
        // Remove default row actions for pseudo (registered) posts
        if ( $is_registered_block_pattern ) {
            $actions = [];
        } else {
            unset($actions['view']);
            //unset( $actions ['preview'] );
        }
        // Remove 'quick edit' - because of bug which hides preview on opening quick edit
        unset($actions['inline hide-if-no-js']);
        // Add Activate or Deactivate row action
        if ( current_user_can( 'publish_blockmeister_patterns' ) ) {
            // add activate/deactivate row action:
            //$pattern_name = $is_registered_block_pattern ? $post->post_name : BlockMeister::get_default_block_namespace() . '/' . $post->post_name;
            $pattern_name = $this->get_pattern_name( $post );
            if ( Utils::is_pattern_active( $pattern_name ) ) {
                $action = 'deactivate';
                /* translators: %s: Pattern name. */
                $aria_label = sprintf( esc_html_x( 'Deactivate %s', 'pattern' ), $pattern_name );
                $label = __( 'Deactivate', 'blockmeister' );
            } else {
                // pattern is inactive
                $action = 'activate';
                /* translators: %s: Pattern name. */
                $aria_label = sprintf( esc_html_x( 'Activate %s', 'pattern' ), $pattern_name );
                $label = __( 'Activate', 'blockmeister' );
            }
            $action_url = add_query_arg( [
                'pattern' => urlencode( $pattern_name ),
                'action'  => $action,
            ] );
            $action_url = remove_query_arg( ['notice', 'status'], $action_url );
            $actions[$action] = sprintf(
                '<a href="%s" id="%s-%s" aria-label="%s">%s</a>',
                wp_nonce_url( $action_url, $action, 'bm_nonce' ),
                $action,
                esc_attr( $pattern_name ),
                esc_attr( $aria_label ),
                esc_html( $label )
            );
        }
        return $actions;
    }

    /**
     * Creates and returns a json string, containing all pattern data AND
     * keywords and categories. All terms will contain their definition data
     * in order to create the term on the import site when the term is missing.
     */
    private function get_registered_pattern_for_export_json( $pattern_name ) {
        $patterns_registry = \WP_Block_Patterns_Registry::get_instance();
        $registered_pattern = $patterns_registry->get_registered( $pattern_name );
        $registered_pattern['__file'] = "blockmeister_pattern";
        if ( isset( $registered_pattern['categories'] ) ) {
            // add category registration data
            $category_registry = \WP_Block_Pattern_Categories_Registry::get_instance();
            foreach ( $registered_pattern['categories'] as $index => $category ) {
                $category_term = $category_registry->get_registered( $category );
                $registered_pattern['categories'][$index] = $category_term;
            }
        }
        return wp_json_encode( $registered_pattern );
    }

    /**
     * Filters the pattern list table bulk actions drop-down.
     *
     * The dynamic portion of the hook name, `$this->screen->id`, refers
     * to the ID of the current screen, usually a string.
     *
     * @param string[] $actions An array of the available bulk actions.
     *
     * @return string[]
     */
    public function add_and_remove_bulk_actions( $actions ) {
        $filter = ( isset( $_GET['filter'] ) ? $_GET['filter'] : false );
        if ( !$filter || 'active' !== $filter ) {
            $actions['activate'] = esc_html__( 'Activate', 'blockmeister' );
        }
        if ( !$filter || 'inactive' !== $filter ) {
            $actions['deactivate'] = esc_html__( 'Deactivate', 'blockmeister' );
        }
        unset($actions['edit']);
        return $actions;
    }

    /**
     * Handles row actions and form upload actions
     *
     * Security:
     * - Checks user intend (admin referer)
     * - action handlers it calls will check required capbilites
     */
    public function handle_action_request() {
        global $pagenow;
        $handled_actions = [
            'activate',
            'deactivate',
            'clone',
            'trash',
            'import'
        ];
        // other actions will be handled by WP
        $action = ( isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : false );
        if ( 'edit.php' !== $pagenow || !isset( $_GET['post_type'] ) || 'blockmeister_pattern' !== $_GET['post_type'] || $action === false || !in_array( $action, $handled_actions ) ) {
            return;
        }
        $num = false;
        if ( check_admin_referer( $action, 'bm_nonce' ) ) {
            $post_status_query_arg = ( isset( $_GET['post_status'] ) ? $_GET['post_status'] : false );
            $filter_query_arg = ( isset( $_GET['filter'] ) ? $_GET['filter'] : false );
            $filter_action_query_arg = ( isset( $_GET['filter_action'] ) ? $_GET['filter_action'] : false );
            $filter_category_query_arg = ( isset( $_GET['category'] ) ? $_GET['category'] : false );
            $pattern = ( isset( $_GET['pattern'] ) ? $_GET['pattern'] : false );
            switch ( $action ) {
                case 'activate':
                    $notice = $this->activate_pattern( $pattern );
                    break;
                case 'deactivate':
                    $notice = $this->deactivate_pattern( $pattern );
                    break;
                case 'clone':
                    $notice = $this->clone_pattern__premium_only( $pattern );
                    break;
                case 'import':
                    $num = count( $_FILES['file'] );
                    $notice = $this->import_patterns();
                    break;
                default:
                    $notice = new WP_Error('unknown-action', __( 'Unknown action', 'blockmeister' ));
            }
            $query_args = [
                'notice'        => urlencode( ( is_wp_error( $notice ) ? $notice->get_error_message() : $notice ) ),
                'status'        => ( is_wp_error( $notice ) ? 'error' : 'success' ),
                'post_status'   => $post_status_query_arg,
                'filter'        => $filter_query_arg,
                'filter_action' => $filter_action_query_arg,
                'category'      => $filter_category_query_arg,
            ];
            if ( $pattern ) {
                $query_args['pattern'] = $pattern;
            }
            if ( $num ) {
                $query_args['num'] = $num;
            }
            $_SERVER['REQUEST_URI'] = remove_query_arg( array(
                'locked',
                'skipped',
                'updated',
                'deleted',
                'trashed',
                'untrashed',
                'activated',
                'deactivated'
            ), $_SERVER['REQUEST_URI'] );
            $redirect_to = add_query_arg( $query_args, self_admin_url( "edit.php?post_type=blockmeister_pattern" ) );
            wp_redirect( $redirect_to );
            // will be sanitized by wp_sanitize_redirect
            exit;
        }
    }

    public function activate_pattern( $pattern_name ) {
        if ( current_user_can( 'publish_blockmeister_patterns' ) && $pattern_name !== false ) {
            $inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );
            if ( in_array( $pattern_name, $inactive_patterns, true ) ) {
                $index = array_search( $pattern_name, $inactive_patterns, true );
                unset($inactive_patterns[$index]);
            }
            $pattern = $this->get_registered_pattern( $pattern_name );
            /* translators: placeholder will be replaced by the name of the pattern that was activated */
            $notice = wp_sprintf( __( "Pattern '%s' has been activated.", 'blockmeister' ), $pattern['title'] );
            sort( $inactive_patterns );
            update_option( 'blockmeister_inactive_patterns', $inactive_patterns );
        } else {
            $notice = new WP_Error('activate-not-allowed', __( 'You have insufficient rights to activate patterns.', 'blockmeister' ));
        }
        return $notice;
    }

    /**
     * @param string $pattern_name Pattern name including namespace
     *
     * @return array|null
     */
    private function get_registered_pattern( $pattern_name ) {
        $patterns_registry = \WP_Block_Patterns_Registry::get_instance();
        return $registered_pattern = $patterns_registry->get_registered( $pattern_name );
    }

    public function deactivate_pattern( $pattern_name ) {
        if ( current_user_can( 'publish_blockmeister_patterns' ) && $pattern_name !== false ) {
            $inactive_patterns = get_option( 'blockmeister_inactive_patterns', [] );
            if ( !in_array( $pattern_name, $inactive_patterns, true ) ) {
                $inactive_patterns[] = $pattern_name;
            }
            $pattern = $this->get_registered_pattern( $pattern_name );
            /* translators: placeholder will be replaced by the name of the pattern that was deactivated */
            $notice = wp_sprintf( __( "Pattern '%s' has been deactivated.", 'blockmeister' ), $pattern['title'] );
            sort( $inactive_patterns );
            update_option( 'blockmeister_inactive_patterns', $inactive_patterns );
        } else {
            $notice = new WP_Error('deactivate-not-allowed', __( 'You have insufficient rights to deactivate patterns.', 'blockmeister' ));
        }
        return $notice;
    }

    /**
     * If current user is allowed to and the uploaded file is 'sane'
     * it will be inserted as a blockmeister_pattern post.
     *
     * @return array|string|WP_Error
     */
    private function import_patterns() {
        if ( current_user_can( 'publish_blockmeister_patterns' ) && current_user_can( 'import' ) ) {
            if ( !isset( $_FILES['file'] ) ) {
                return new WP_Error('no-upload', esc_html__( 'There where no files uploaded to import.', 'blockmeister' ));
            }
            // rearrange $_FILES['file'] (for easier use)
            $files = [];
            foreach ( $_FILES['file'] as $key => $all ) {
                foreach ( $all as $i => $val ) {
                    $files[$i][$key] = $val;
                }
            }
            // decode, check format, save imported file(s) data:
            foreach ( $files as $file ) {
                $decoded = JSON_File_Uploader::decode_json_file( $file );
                if ( is_wp_error( $decoded ) ) {
                    return $decoded;
                    // = WP_Error
                } else {
                    $pattern_import_data = $this->check_pattern_import_data_format( $decoded );
                    if ( is_wp_error( $pattern_import_data ) ) {
                        return $pattern_import_data;
                        // = WP_Error
                    } else {
                        $this->insert_imported_pattern_as_post( $pattern_import_data );
                    }
                }
            }
            if ( count( $files ) === 1 ) {
                /* translators: placeholder will be replaced by the name of the pattern */
                $notice = wp_sprintf( __( "Pattern '%s' has been imported.", 'blockmeister' ), $pattern_import_data['title'] );
            } else {
                /* translators: placeholder will be replaced by the number of the patterns that were imported */
                $notice = wp_sprintf( __( "%d patterns imported.", 'blockmeister' ), count( $files ) );
            }
        } else {
            $notice = new WP_Error('no-permission', __( 'You have insufficient rights to import patterns.', 'blockmeister' ));
        }
        return $notice;
    }

    /**
     * Checks data format
     * Users can import blockmeister_pattern data or core reusable (exported) data.
     *
     * @param $pattern_import_data
     *
     * @return WP_Error|array The import data (as associative array) or WP_Error when data format is invalid. The data is safe to store in the DB.
     */
    private function check_pattern_import_data_format( $pattern_import_data ) {
        $required_keys = ['__file', 'title', 'content'];
        $invalid_json_error = new WP_Error('invalid-json-format', __( 'Invalid data format.', 'blockmeister' ));
        // check if required keys exist (else return WP Error: invalid format):
        foreach ( $required_keys as $required_key ) {
            if ( !isset( $pattern_import_data[$required_key] ) ) {
                return $invalid_json_error;
            }
        }
        // check supported file formats (currently blockmeister pattern export files and core reusable block export files)
        $supported_file_formats = ['blockmeister_pattern', 'wp_block'];
        if ( !in_array( $pattern_import_data['__file'], $supported_file_formats ) ) {
            return $invalid_json_error;
        }
        return $pattern_import_data;
        // = not sanitized at this point!
    }

    /**
     * Inserts the imported data as blockmeister_pattern post.
     * Missing pattern_category terms will be inserted to.
     *
     * Security notes:
     * - uses wp_insert_post and wp_insert_term, which take care of data sanitization.
     * - caller should take care of checking user intend and capability check before calling this method
     *   and take care of any action specific checks/sanitization (e.g. of custom post meta data), if applicable.
     *
     * @param $pattern_import_data
     *
     * @return int|WP_Error
     */
    private function insert_imported_pattern_as_post( $pattern_import_data ) {
        $insert_post_args = [
            'post_title'     => $pattern_import_data['title'] . ' (' . esc_html__( 'import', 'blockmeister' ) . ')',
            'post_status'    => 'draft',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_content'   => wp_slash( $pattern_import_data['content'] ),
            'post_excerpt'   => ( isset( $pattern_import_data['description'] ) ? wp_slash( $pattern_import_data['description'] ) : '' ),
            'post_type'      => 'blockmeister_pattern',
        ];
        // add the clone post
        $new_post_id = wp_insert_post( $insert_post_args );
        // newly cloned post will automatically get the default category assigned, we need to undo that:
        wp_remove_object_terms( $new_post_id, 'default', 'pattern_category' );
        // set categories (note: is a hierarchical taxonomy)
        if ( isset( $pattern_import_data['categories'] ) ) {
            foreach ( $pattern_import_data['categories'] as $category_term ) {
                // if category doesn't exist, add it:
                $ids = term_exists( $category_term['name'], 'pattern_category' );
                if ( !$ids ) {
                    $ids = wp_insert_term( $category_term['label'], 'pattern_category', [
                        'slug' => $category_term['name'],
                    ] );
                }
                if ( $ids ) {
                    $affected_term_taxonomy_ids = wp_set_post_terms(
                        $new_post_id,
                        [$ids['term_id']],
                        'pattern_category',
                        true
                    );
                }
            }
        }
        // set keywords
        if ( isset( $pattern_import_data['keywords'] ) ) {
            $affected_term_taxonomy_ids = wp_set_post_terms(
                $new_post_id,
                $pattern_import_data['keywords'],
                'pattern_keyword',
                true
            );
        }
        // set meta
        if ( isset( $pattern_import_data['viewportWidth'] ) ) {
            update_post_meta( $new_post_id, '_bm_viewport_width', (int) $pattern_import_data['viewportWidth'] );
        }
        return $new_post_id;
    }

    /**
     * Fires when a custom bulk action should be handled.
     *
     * The redirect link should be modified with success or failure feedback
     * from the action to be used to display feedback to the user.
     *
     * Note: this is a callback called by edit.php for custom bulk actions.
     *       Before it is called edit.php checks user intend via check_admin_referer(bulk-posts)
     *
     * @param string $redirectURL The redirect URL.
     * @param string $doaction The action being taken.
     * @param array $pattern_post_ids The (pseudo) post ids of patterns to take the action on.
     *
     * @return string The redirect URL.
     */
    public function handle_bulk_actions( $redirectURL, $doaction, $pattern_post_ids ) {
        $num_successful = 0;
        foreach ( $pattern_post_ids as $pattern_post_id ) {
            $pattern_name = $this->get_pattern_name( $pattern_post_id );
            $action_method = "{$doaction}_pattern";
            switch ( $doaction ) {
                case "activate":
                    $success_status = __( 'activated' );
                    // use core translation
                    break;
                case "deactivate":
                    $success_status = __( 'deactivated' );
                    // use core translation
                    break;
                case 'trash_custom':
                    $success_status = __( 'trashed' );
                    // use core translation
                    break;
            }
            // do action + add to count how many where successful
            $notice = $this->{$action_method}( $pattern_name );
            $num_successful += ( !is_wp_error( $notice ) ? 1 : 0 );
        }
        // note: unfortunately it seems impossible to filter bulk_counts array, therefore we re-use the core status 'updated'
        $redirectURL = add_query_arg( [
            'updated'       => $num_successful,
            $success_status => $num_successful,
        ], $redirectURL );
        //$redirect_to            = add_query_arg( $query_args, $redirectURL );
        return $redirectURL;
    }

    /**
     * Filters the action updated messages.
     * By default, custom post types use the messages for the 'post' post type.
     * With this callback we change that to 'pattern'.
     *
     * @param array[] $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
     *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
     * @param int[] $bulk_counts Array of item counts for each message, used to build internationalized strings.
     *
     * @return array[] The update messages.
     */
    public function filter_bulk_action_result_messages( $bulk_messages, $bulk_counts ) {
        if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'blockmeister_pattern' ) {
            $bulk_counts['activated'] = ( isset( $_REQUEST['activated'] ) ? absint( $_REQUEST['activated'] ) : 0 );
            $bulk_counts['deactivated'] = ( isset( $_REQUEST['deactivated'] ) ? absint( $_REQUEST['deactivated'] ) : 0 );
            $bulk_messages['post'] = [
                'updated'   => _n( '%s pattern updated.', '%s patterns updated.', $bulk_counts['updated'] ),
                'locked'    => ( 1 === $bulk_counts['locked'] ? __( '1 pattern not updated, somebody is editing it.' ) : _n( '%s pattern not updated, somebody is editing it.', '%s patterns not updated, somebody is editing them.', $bulk_counts['locked'] ) ),
                'deleted'   => _n( '%s pattern permanently deleted.', '%s patterns permanently deleted.', $bulk_counts['deleted'] ),
                'trashed'   => _n( '%s pattern moved to the Trash.', '%s patterns moved to the Trash.', $bulk_counts['trashed'] ),
                'untrashed' => _n( '%s pattern restored from the Trash.', '%s patterns restored from the Trash.', $bulk_counts['untrashed'] ),
            ];
            // note: edit.php has no filter to add custom keys to its $block_counts,
            //       therefore we re-use 'updated' key which we override if applicable:
            if ( isset( $_REQUEST['activated'] ) ) {
                /* translators: %s: Number of patterns. */
                $bulk_messages['post']['updated'] = _n( '%s pattern activated.', '%s patterns activated.', $bulk_counts['activated'] );
            } elseif ( isset( $_REQUEST['deactivated'] ) ) {
                /* translators: %s: Number of patterns. */
                $bulk_messages['post']['updated'] = _n( '%s pattern deactivated.', '%s patterns deactivated.', $bulk_counts['deactivated'] );
            }
        }
        return $bulk_messages;
    }

    /**
     * Filters on category on the raw post results array, prior to status checks.
     * If there is a filter arg then we remove post based on filtered state them from the post results.
     * This is to make sure the table list only shows patterns that apply to the custom filter.
     * Handles only our custom filters: active, inactive,
     *
     * @param \WP_Post[] $posts Array of post objects.
     * @param \WP_Query $wp_query The WP_Query instance (passed by reference).
     *
     * @return \WP_Post[]
     *
     */
    public function filter_table_list_rows_on_category( $posts, $wp_query ) {
        // if no applicable filter then do nothing but return the posts as is
        if ( !isset( $wp_query->query['post_type'] ) || $wp_query->query['post_type'] !== 'blockmeister_pattern' || !isset( $_GET['filter_action'] ) || !isset( $_GET['category'] ) || $_GET['category'] === '0' ) {
            return $posts;
        }
        $category_filter = $_GET['category'];
        $filtered_posts = [];
        foreach ( $posts as $post ) {
            $pattern_name = $this->get_pattern_name( $post );
            $registered_block_pattern = $this->get_registered_pattern( $pattern_name );
            $pattern_categories = ( isset( $registered_block_pattern['categories'] ) ? $registered_block_pattern['categories'] : [] );
            if ( in_array( $category_filter, $pattern_categories, true ) ) {
                $filtered_posts[] = $post;
            }
        }
        return $filtered_posts;
    }

    private function sort_posts( $posts, $order, $orderByColumn ) {
        switch ( $orderByColumn ) {
            case 'date':
                $orderBy = [
                    'post_date'  => $order,
                    'post_title' => 'ASC',
                ];
                break;
            case 'title':
            default:
                $orderBy = 'post_title';
                break;
        }
        $posts = wp_list_sort( $posts, $orderBy, $order );
        return $posts;
    }

}
