<?php


namespace ProDevign\BlockMeister;

use WP_Error;

/**
 * Class File_Uploader
 */
class JSON_File_Uploader {

	private $target_url;

	/**
	 * File_Uploader constructor.
	 *
	 * @param string $target_url The target URL to which the form will be submitted.
	 */
	public function __construct( $target_url ) {
		$this->target_url = $target_url;
	}


    public function init( $enqueue_direct = false ) {
	    add_action( 'admin_footer', [ $this, 'add_style' ] );
	    add_action( 'admin_footer', [ $this, 'add_script' ] );
    }


	public function get_html() {
        $action_url = esc_url( $this->target_url );
		$drop_instruction = esc_html__('Drop files to upload','blockmeister' );
		$select_instruction = esc_html__( 'Select Files', 'blockmeister' );
		$close_text = esc_html__( 'Close uploader', 'blockmeister' );
		$action = 'import';
		$nonce_field = wp_nonce_field( $action, 'bm_nonce' );

		$import_disclaimer = '<strong>'  . esc_html__( 'Keep your site safe!', 'blockmeister' ) . '</strong><br />' .
		                     esc_html__( 'Only import patterns you downloaded from trusted sources or manually validate the pattern code before importing.', 'blockmeister' );

		return $html = <<<FORM
			<form method="POST" enctype="multipart/form-data" action="{$action_url}" class="imports">
			<p><sup></sup>{$import_disclaimer}</p>
			    <div id="spinner-wrapper">
                    <img src="/wp-includes/images/spinner.gif" class="loading">		       
			    </div>
			    <div id="upload-ui-wrapper">
			    <button id="close-form" class="dashicons dashicons-no"><span class="screen-reader-text">{$close_text}</span></button>	
			    <div class="upload-ui">
					<h2 class="upload-instructions drop-instructions">{$drop_instruction}</h2>
					<p class="upload-instructions drop-instructions">or</p>
					<button type="button" class="browser button button-hero">{$select_instruction}</button>
				</div>
			    <input id="files" name="file[]" type="file" accept="application/json" multiple />
			    <input name="action" type="hidden" value="{$action}">
			    {$nonce_field}
			    </div>
			</form>   
FORM;

	}

	/**
	 * Does the same checks as is done by _wp_handle_upload, but doesn't move (save) the file on to the server.
	 * Note: the return decoded json object/array needs yet to be sanitized for your particular application
	 *
	 * @param string[] $file Reference to a single element of `$_FILES`.
	 *                       Call the function once for each uploaded file.
	 * @param bool $assoc When TRUE, JSON objects will be returned as associative arrays;
     *                    When FALSE, JSON objects will be returned as objects;*
     *                    Default TRUE
     *
	 *
	 * @return mixed|WP_Error
	 */
	public static function decode_json_file( $file, $assoc = true ) {

	    // test if upload failed $file['error'] will be set
		if ( isset( $file['error'] ) && $file['error'] > 0 ) {
		    return new WP_Error( 'upload-failed', esc_html( $file['error'] ) );
		}

		// test if file was uploaded via HTTP_POST (otherwise assume malicious intent)
        if ( !is_uploaded_file( $file['tmp_name']) ) {
            wp_die('😞','',400);
        }

		// test file is not empty
		$test_file_size = filesize( $file['tmp_name'] );
        if ( $test_file_size === 0 ) {
	        return new  WP_Error( 'file-empty', esc_html__( 'Uploaded file is empty.', 'blockmeister' ) );
        }

        // test json extension
		$wp_filetype     = wp_check_filetype( $file['name'], ['json'=>'application/json'] );
		$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
		$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];

		if ( $wp_filetype['ext'] !== 'json' ) {
			return new WP_Error( 'no-json-file-extension', esc_html__( 'Imported file has invalid file extension.','blockmeister' ) );
		}

		// test if file contains a json string
		$file_content = file_get_contents( $file['tmp_name'] );
		$json_decoded = json_decode( $file_content, $assoc );
		if ( json_last_error() !== JSON_ERROR_NONE && is_object( $json_decoded ) ) {
			return new WP_Error( 'invalid-json', json_last_error_msg() );
		}

		// all checks passed, return (not-sanitized) decoded JSON (object/array)
		return $json_decoded;

    }

	public function add_style() {

		?>
		<style id="blockmeister-json-file-uploader-style">
			form.imports {
				position: relative;
				width: calc(100%-12px);
				display: none;
                border: 4px dashed #b4b9be;
				text-align: center;
				margin: 12px 2px;
			}

            form.imports > #spinner-wrapper {
                position: absolute;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100%;
                width: 100%;
                opacity: 0;
            }

            form.imports > #spinner-wrapper > img.loading {
                opacity: 0.7;
                width: auto;
                transition: opacity 400ms ease-in-out;
                position: absolute;
                z-index: 1;
                padding: 4px;
            }

            form.imports > #upload-ui-wrapper {
                transition: opacity 200ms ease-in-out;
            }

			form.imports button#close-form {
                background-color: transparent;
				font-size: 2em;
				color: grey;
                border: 0;
                cursor: pointer;
                height: 48px;
                outline: 0;
                padding: 0;
                position: absolute;
                right: 2px;
                text-align: center;
                top: 2px;
                width: 48px;
                z-index: 10;
                transition: transform .2s ease-in-out;
			}

            form.imports button#close-form:hover {
                transform:rotate(90deg);
            }

            form.imports .upload-ui {
	            margin: 2em 0;
            }

            form.imports h2 {
                font-size: 20px;
                line-height: 1.4;
                font-weight: 400;
                margin: 0;
            }

            form.imports button.button-hero {
                pointer-events: none;
                display: inline-block;
	            position: relative;
	            z-index: 1;
            }

            form.imports input[type=file] {
                position: absolute;
	            left: 0;
	            top: 0;
                width: 100%;
                height: 100%;
	            outline: 0;
                opacity: 0;
            }
		</style>
		<?php

	}

	public function add_script() {
        ?>
        <script id="blockmeister-json-file-uploader-script">
            jQuery(document).ready(function ($) {

                $('button#close-form').on('click', function (e) {
                    $('form.imports').slideToggle(200);
                    e.preventDefault();
                });

                $('form.imports input#files').on('change', function (e){
                    $('form.imports > #spinner-wrapper').css('opacity',1);
                    $('form.imports > #upload-ui-wrapper').css('opacity',0);
                    $('form.imports').submit();
                });

            });
        </script>
        <?php
	}

}