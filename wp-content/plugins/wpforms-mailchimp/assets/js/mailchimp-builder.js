/* global WPForms, wpforms_builder, wpf, Choices, wpformsMailchimpBuilderVars */
'use strict';

/**
 * WPForms Providers Builder Mailchimp module.
 *
 * @since 2.0.0
 */
WPForms.Admin.Builder.Providers.Mailchimp = WPForms.Admin.Builder.Providers.Mailchimp || ( function( document, window, $ ) {

	/**
	 * Private functions and properties.
	 *
	 * @since 2.0.0
	 *
	 * @type {object}
	 */
	var __private = {

		/**
		 * Provider holder jQuery object.
		 *
		 * @since 2.0.0
		 *
		 * @type {jQuery}
		 */
		$providerHolder: null,

		/**
		 * Provider connections jQuery object.
		 *
		 * @since 2.0.0
		 *
		 * @type {jQuery}
		 */
		$providerConnections: null,

		/**
		 * Lock hidden input jQuery object.
		 *
		 * @since 2.0.0
		 *
		 * @type {jQuery}
		 */
		$lock: null,

		/**
		 * Error message jQuery object.
		 *
		 * @since 2.0.0
		 *
		 * @type {jQuery}
		 */
		$error: null,

		/**
		 * Config contains all configuration properties.
		 *
		 * @since 2.0.0
		 *
		 * @type {object.<string, *>}
		 */
		config: {

			/**
			 * List of Mailchimp templates that should be compiled.
			 *
			 * @since 2.0.0
			 *
			 * @type {string[]}
			 */
			templates: [
				'wpforms-mailchimpv3-builder-content-connection',
				'wpforms-mailchimpv3-builder-content-connection-data',
				'wpforms-mailchimpv3-builder-content-connection-subscribe',
				'wpforms-mailchimpv3-builder-content-connection-unsubscribe',
				'wpforms-mailchimpv3-builder-content-connection-archive',
				'wpforms-mailchimpv3-builder-content-connection-delete',
				'wpforms-mailchimpv3-builder-content-connection-record-event',
				'wpforms-mailchimpv3-builder-content-connection-required-fields',
				'wpforms-mailchimpv3-builder-content-connection-error',
				'wpforms-mailchimpv3-builder-content-connection-lock',
				'wpforms-mailchimpv3-builder-content-connection-conditionals',
			],
		},

		/**
		 * Sometimes in DOM we might have placeholders or temporary connection IDs.
		 * We need to replace them with actual values.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} connectionId New connection ID to replace to.
		 * @param {object} $connection  jQuery DOM connection element.
		 */
		replaceConnectionIds: function( connectionId, $connection ) {

			// Replace old temporary %connection_id% from PHP code with the new one.
			$connection
				.find( 'input, textarea, select, label, .wpforms-panel-field-select' ).each( function() {

					var $this = $( this );

					if ( $this.attr( 'name' ) ) {
						$this.attr( 'name', $this.attr( 'name' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'id' ) ) {
						$this.attr( 'id', $this.attr( 'id' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'for' ) ) {
						$this.attr( 'for', $this.attr( 'for' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'data-name' ) ) {
						$this.attr( 'data-name', $this.attr( 'data-name' ).replace( /%connection_id%/gi, connectionId ) );
					}
				} );
		},

		/**
		 * Whether we have an account ID in a list of all available accounts.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} connectionAccID Connection account ID to check.
		 * @param {object} accounts        Registered accounts.
		 *
		 * @returns {boolean} True if connection already exists.
		 */
		connectionAccountExists: function( connectionAccID, accounts ) {

			if ( _.isEmpty( accounts ) ) {
				return false;
			}

			// New connections, that have not been saved don't have the account ID yet.
			if ( _.isEmpty( connectionAccID ) ) {
				return true;
			}

			return _.has( accounts, connectionAccID );
		},
	};

	// noinspection JSUnusedGlobalSymbols
	/**
	 * Public functions and properties.
	 *
	 * @since 2.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Current provider slug.
		 *
		 * @since 2.0.0
		 *
		 * @type {string}
		 */
		provider: 'mailchimpv3',

		/**
		 * This is a shortcut to the WPForms.Admin.Builder.Providers object,
		 * that handles the parent all-providers functionality.
		 *
		 * @since 2.0.0
		 *
		 * @type {object}
		 */
		Providers: {},

		/**
		 * This is a shortcut to the WPForms.Admin.Builder.Templates object,
		 * that handles all the template management.
		 *
		 * @since 2.0.0
		 *
		 * @type {object}
		 */
		Templates: {},

		/**
		 * This is a shortcut to the WPForms.Admin.Builder.Providers.cache object,
		 * that handles all the cache management.
		 *
		 * @since 2.0.0
		 *
		 * @type {object}
		 */
		Cache: {},

		/**
		 * This is a flag for ready state.
		 *
		 * @since 2.0.0
		 *
		 * @type {boolean}
		 */
		isReady: false,

		/**
		 * Start the engine.
		 *
		 * Run initialization on providers panel only.
		 *
		 * @since 2.0.0
		 */
		init: function() {

			// We are requesting/loading the Providers panel.
			if ( 'providers' === wpf.getQueryString( 'view' ) ) {
				$( '#wpforms-panel-providers' ).on( 'WPForms.Admin.Builder.Providers.ready', app.ready );
			}

			// We have switched to Providers panel.
			$( document ).on( 'wpformsPanelSwitched', function( e, panel ) {

				if ( 'providers' === panel ) {
					app.ready();
				}
			} );
		},

		/**
		 * Initialized once the DOM and Providers are fully loaded.
		 *
		 * @since 2.0.0
		 */
		ready: function() {

			if ( app.isReady ) {
				return;
			}

			// Done by reference, so we are not doubling memory usage.
			app.Providers = WPForms.Admin.Builder.Providers;
			app.Templates = WPForms.Admin.Builder.Templates;
			app.Cache     = app.Providers.cache;

			// Save providers holder and connections for using in future.
			__private.$providerHolder      = app.Providers.getProviderHolder( app.provider );
			__private.$providerConnections = __private.$providerHolder.find( '.wpforms-builder-provider-connections' );

			// Register custom Underscore.js templates.
			app.Templates.add( __private.config.templates );

			// Register a handler for Add New Account process.
			app.Providers.ui.account.registerAddHandler( app.provider, app.processAccountAdd );

			// Events registration.
			app.bindUIActions();
			app.bindTriggers();

			app.processInitial();

			// Save a flag for ready state.
			app.isReady = true;
		},

		/**
		 * Process various events as a response to UI interactions.
		 *
		 * @since 2.0.0
		 */
		bindUIActions: function() {

			__private.$providerHolder
				.on( 'connectionCreate', app.connection.callbacks.create )
				.on( 'connectionDelete', app.connection.callbacks.delete )
				.on( 'change', '.js-wpforms-builder-mailchimp-provider-connection-account', app.ui.account.changeCallback )
				.on( 'change', '.js-wpforms-builder-mailchimp-provider-connection-lists', app.ui.list.changeCallback )
				.on( 'change', '.js-wpforms-builder-mailchimp-provider-connection-action', app.ui.action.changeCallback )
				.on( 'change', '.js-wpforms-builder-mailchimp-provider-update-profile', app.ui.updateProfile.changeCallback );
		},

		/**
		 * Fire certain events on certain actions, specific for related connections.
		 * These are not directly caused by user manipulations.
		 *
		 * @since 2.0.0
		 */
		bindTriggers: function() {

			__private.$providerConnections
				.on( 'connectionsDataLoaded', app.triggers.connectionsDataLoadedHandler )
				.on( 'connectionGenerated', app.triggers.connectionGeneratedHandler )
				.on( 'connectionGeneralSettingsRendered', app.triggers.connectionGeneralSettingsRenderedHandler )
				.on( 'connectionRendered', app.triggers.connectionRenderedHandler );

			$( '#wpforms-builder' )
				.on( 'wpformsSaved', app.requireGDPR.init )
				.on( 'wpformsFieldDelete', app.triggers.wpformsFieldDeleteHandler );
		},

		/**
		 * Compile template with data if any and display them on a page.
		 *
		 * @since 2.0.0
		 */
		processInitial() {
			__private.$providerConnections.prepend( app.tmpl.callbacks.commonsHTML() );
			__private.$lock = __private.$providerHolder.find( '.wpforms-builder-provider-connections-save-lock' );
			__private.$error = __private.$providerConnections.find( '.wpforms-builder-provider-connections-error' );

			app.connection.callbacks.dataLoad();
		},

		/**
		 * Process the account creation in the Form Builder.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} modal jQuery-Confirm modal object.
		 *
		 * @returns {boolean} False.
		 */
		processAccountAdd: function( modal ) {

			if ( modal.$$add.prop( 'disabled' ) ) {
				return false;
			}

			var $apiKeyField = modal.$content.find( 'input[name="apikey"]' ),
				$error       = modal.$content.find( '.error' ),
				apiKey       = $.trim( $apiKeyField.val() );

			modal.$$add.prop( 'disabled', true );

			if ( _.isEmpty( apiKey ) ) {
				$error.html( wpformsMailchimpBuilderVars.i18n.generalAjaxError ).show();
				$apiKeyField.addClass( 'wpforms-error' );
				modal.$$add.prop( 'disabled', false );

				return false;
			}

			$error.hide();
			modal.setType( 'blue' );
			$apiKeyField.removeClass( 'wpforms-error' );

			app.Providers.ajax
				.request( app.provider, {
					data: {
						task: 'account_save',
						apikey: apiKey,
						label: $.trim( modal.$content.find( 'input[name="label"]' ).val() ),
					},
				} )
				.done( function( response ) {

					if (
						! response.success ||
						(
							_.has( response.data, 'error' ) &&
							! _.isEmpty( response.data.error )
						)
					) {
						modal.$$add.prop( 'disabled', false );
						$error.html( response.data.error ).show();

					} else {

						// Hide `Add New Account` button.
						__private.$providerHolder
							.find( '.wpforms-builder-provider-title-add' )
							.toggleClass( 'hidden' );
						modal.close();
					}
				} );

			return false;
		},

		/**
		 * For each connection we should preselect an already saved email field.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} connectionId Current connection ID.
		 * @param {object} $connection  jQuery DOM connection element.
		 */
		mapFields: function( connectionId, $connection ) {

			var connection = app.Cache.getById( app.provider, 'connections', connectionId );

			if (
				_.isEmpty( connection ) ||
				_.isEmpty( connection.fields )
			) {
				return;
			}

			// Preselect a "Subscriber Email".
			if ( '' !== connection.fields.EMAIL ) {
				$( 'select[name="providers[' + app.provider + '][' + connectionId + '][fields][EMAIL]"]', $connection ).val( connection.fields.EMAIL );
			}
		},

		/**
		 * Load Choices.js library.
		 *
		 * @since 2.0.0
		 *
		 * @param {jQuery} $connection jQuery connection object.
		 */
		loadChoicesJS: function( $connection ) {

			// Load if function exists.
			if ( ! _.isFunction( window.Choices ) ) {
				return;
			}

			// Init if selects are exists.
			var $selects = $( '.choicesjs-select', $connection );

			if ( ! $selects.length ) {
				return;
			}

			$selects.each( function() {

				var $this = $( this ),
					args;

				// Bail if already initialized.
				if ( ! _.isUndefined( $this.data( 'choicesjs' ) ) ) {
					return;
				}

				args = {
					shouldSort: false,
					removeItemButton: true,
					callbackOnInit() {
						wpf.initMultipleSelectWithSearch( this );
						wpf.showMoreButtonForChoices( this.containerOuter.element );
					},
				};

				$this.data( 'choicesjs', new Choices( this, args ) );
			} );
		},

		/**
		 * Connection property.
		 *
		 * @since 2.0.0
		 */
		connection: {

			/**
			 * Sometimes we might need to a get a connection DOM element by its ID.
			 *
			 * @since 2.0.0
			 *
			 * @param {string} connectionId Connection ID to search for a DOM element by.
			 *
			 * @returns {jQuery} jQuery object for connection.
			 */
			getById: function( connectionId ) {

				return __private.$providerConnections.find( '.wpforms-builder-provider-connection[data-connection_id="' + connectionId + '"]' );
			},

			/**
			 * Filter connection fields meta by optional and required.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} connection  Connection data.
			 * @param {object} mergeFields Mailchimp mapped fields.
			 *
			 * @returns {object} Filtered connection data.
			 */
			filterFieldsMeta: function( connection, mergeFields ) {

				// Fields meta are empty OR already filtered.
				if (
					_.isEmpty( connection['fields_meta'] ) ||
					! _.isEmpty( connection['fields_reqs'] )
				) {
					return connection;
				}

				var filteredFieldsMeta = [],
					filteredFieldsReqs = [];

				_.each( connection['fields_meta'], function( fieldMeta ) {

					if ( ! _.has( fieldMeta, 'name' ) ) {
						return;
					}

					if ( _.has( mergeFields.required, fieldMeta.name ) ) {
						filteredFieldsReqs.push( fieldMeta );
					} else {
						filteredFieldsMeta.push( fieldMeta );
					}
				} );

				connection['fields_meta'] = filteredFieldsMeta;
				connection['fields_reqs'] = filteredFieldsReqs;

				return connection;
			},

			/**
			 * Connection methods.
			 *
			 * @since 2.0.0
			 */
			callbacks: {

				/**
				 * Create a connection using the user entered name.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} event Event object.
				 * @param {string} name  Connection name.
				 */
				create: function( event, name ) {

					var connectionId = ( new Date().getTime() ).toString( 16 ),
						connection   = {
							'isNew': true,
							'id': connectionId,
							'connection_name': name,
						};

					app.Cache.addTo( app.provider, 'connections', connectionId, connection );

					app.connection.callbacks.generate( {
						connection: connection,
					} );
				},

				/**
				 * Connection is deleted - delete a cache as well.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} event       Event object.
				 * @param {object} $connection jQuery DOM element for a connection.
				 */
				delete: function( event, $connection ) {

					if ( ! $connection.closest( __private.$providerHolder ).length ) {
						return;
					}

					var connectionId = $connection.data( 'connection_id' );

					if ( ! _.isUndefined( typeof connectionId ) ) {
						app.Cache.deleteFrom( app.provider, 'connections', connectionId );
					}
				},

				/**
				 * Get the template and data for a connection and process it.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} data Connection data.
				 */
				generate: function( data ) {

					var accounts = app.Cache.get( app.provider, 'accounts' );

					/*
					 * We may or may not receive accounts previously.
					 * If yes - render instantly, if no - request them via AJAX.
					 */
					if ( ! _.isEmpty( accounts ) ) {
						app.connection.callbacks.render( data, accounts );

					} else {

						// We need to get the live list of accounts, as nothing is in cache.
						app.Providers.ajax
							.request( app.provider, {
								data: {
									task: 'accounts_get',
								},
							} )
							.done( function( response ) {

								if (
									! response.success ||
									! _.has( response.data, 'accounts' )
								) {
									app.helpers.showErrorMsg( response.data );
									return;
								}

								// Save ACCOUNTS in "cache" as a copy.
								app.Cache.set( app.provider, 'accounts', response.data.accounts );

								app.connection.callbacks.render( data, response.data.accounts );
							} );
					}
				},

				/**
				 * Render a connection template.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} data     Connection data.
				 * @param {object} accounts Accounts.
				 */
				render: function( data, accounts ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection' );

					// Bail if we don't have account with passed ID.
					if ( ! __private.connectionAccountExists( data.connection.account_id, accounts ) ) {
						return;
					}

					__private.$providerConnections
						.prepend(
							tmpl( {
								'accounts': accounts,
								'connection': data.connection,
								'provider': app.provider,
								'form_id': app.Providers.form.data( 'id' ),
							} )
						);

					// When we are done adding a new connection with its accounts - trigger next steps.
					__private.$providerConnections.trigger( 'connectionGenerated', [ data ] );
				},

				/**
				 * Fire AJAX-request to retrieve the list of all saved connections.
				 *
				 * @since 2.0.0
				 */
				dataLoad: function() {

					app.Providers.ajax
						.request( app.provider, {
							data: {
								task: 'connections_get',
							},
						} )
						.done( function( response ) {

							if (
								! response.success ||
								! _.has( response.data, 'connections' )
							) {
								app.helpers.showErrorMsg( response.data );
								return;
							}

							// Save CONNECTIONS to "cache" as a copy.
							app.Cache.set( app.provider, 'connections', jQuery.extend( {}, response.data.connections ) );

							// Save CONDITIONALS to "cache" as a copy.
							app.Cache.set( app.provider, 'conditionals', jQuery.extend( {}, response.data.conditionals ) );

							// Save ACCOUNTS to "cache" as a copy, if we have them.
							if ( ! _.isEmpty( response.data.accounts ) ) {
								app.Cache.set( app.provider, 'accounts', jQuery.extend( {}, response.data.accounts ) );
							}

							__private.$providerConnections.trigger( 'connectionsDataLoaded', [ response.data ] );
						} );
				},
			},
		},

		/**
		 * All methods that modify UI of a page.
		 *
		 * @since 2.0.0
		 */
		ui: {

			/**
			 * Account methods.
			 *
			 * @since 2.0.0
			 */
			account: {

				/**
				 * Callback-function on change event.
				 *
				 * @since 2.0.0
				 */
				changeCallback: function() {

					var $this        = $( this ),
						accountId    = $this.val(),
						$connection  = $this.closest( '.wpforms-builder-provider-connection' ),
						connectionId = $connection.data( 'connection_id' ),
						lists        = app.Cache.get( app.provider, 'lists' );

					// Clear all connection data if account was changed.
					$( '.wpforms-builder-mailchimp-provider-connection-data', $connection ).empty();

					// If account is empty.
					if ( '' === accountId ) {
						$this.addClass( 'wpforms-error' );
						return;
					}

					$this.removeClass( 'wpforms-error' );

					if (
						_.isEmpty( lists ) ||
						! _.has( lists, accountId )
					) {
						app.ui.account.request( {
							'account_id': accountId,
							'connection_id': connectionId,
						} );
						return;
					}

					app.ui.account.render( {
						'account_id': accountId,
						'connection_id': connectionId,
					} );
				},

				/**
				 * AJAX request.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				request: function( args ) {

					// Make ajax request to get lists.
					app.Providers.ajax
						.request( app.provider, {
							data: {
								'task': 'objects_get',
								'account_id': args.account_id,
								'connection_id': args.connection_id,
								'sources': {
									'lists': true,
								},
							},
						} )
						.done( function( response ) {

							if (
								! response.success ||
								_.isEmpty( response.data )
							) {
								app.helpers.showErrorMsg( response.data );
								return;
							}

							// "Register" cache key.
							if ( _.isUndefined( app.Cache.get( app.provider, 'lists' ) ) ) {
								app.Cache.set( app.provider, 'lists', {} );
							}

							// Save lists to cache by key.
							app.Cache.addTo( app.provider, 'lists', args.account_id, response.data.lists );

							// Render HTML.
							app.ui.account.render( args );
						} );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {Object} args Arguments.
				 */
				render( args ) {
					let connection = app.Cache.getById( app.provider, 'connections', args.connection_id );
					const tmplConnection = app.Templates.get( `wpforms-${ app.provider }-builder-content-connection-data` ),
						tmplConditional = app.Templates.get( `wpforms-${ app.provider }-builder-content-connection-conditionals` ),
						$connection = app.connection.getById( args.connection_id ),
						lists = app.Cache.getById( app.provider, 'lists', args.account_id ),
						conditional = _.has( connection, 'isNew' ) && connection.isNew ? tmplConditional() : app.Cache.getById( app.provider, 'conditionals', args.connection_id );

					// If it's another account OR account has no audiences - to pass in the template empty connection data.
					if ( connection.account_id !== args.account_id || _.isEmpty( lists ) ) {
						connection = {
							id: connection.id,
						};
					}

					$connection
						.find( '.wpforms-builder-mailchimp-provider-connection-data' )
						.html(
							tmplConnection( {
								lists: app.Cache.getById( app.provider, 'lists', args.account_id ),
								connection: connection,
								conditional: conditional,
								provider: app.provider,
							} )
						);

					__private.$providerConnections.trigger( 'connectionGeneralSettingsRendered', [ app.provider, args.connection_id ] );
				},
			},

			/**
			 * List methods.
			 *
			 * @since 2.0.0
			 */
			list: {

				/**
				 * Callback-function on change event.
				 *
				 * @since 2.0.0
				 */
				changeCallback: function() {

					var $el         = $( this ),
						$connection = $el.closest( '.wpforms-builder-provider-connection' ),
						$action     = $connection.find( '.js-wpforms-builder-mailchimp-provider-connection-action' );

					if ( '' === $el.val() ) {
						$action.prop( 'selectedIndex', 0 );
					}

					$action.trigger( 'change' );
				},
			},

			/**
			 * Action methods.
			 *
			 * @since 2.0.0
			 */
			action: {

				/**
				 * Callback-function on change event.
				 *
				 * @since 2.0.0
				 */
				changeCallback: function() {

					var $el          = $( this ),
						$connection  = $el.closest( '.wpforms-builder-provider-connection' ),
						connectionId = $connection.data( 'connection_id' ),
						$account     = $connection.find( '.js-wpforms-builder-mailchimp-provider-connection-account' ),
						$list        = $connection.find( '.js-wpforms-builder-mailchimp-provider-connection-lists' ),
						action       = $el.val();

					// Clear all action data.
					$( '.wpforms-builder-mailchimp-provider-actions-data', $connection ).empty();

					if ( '' === $list.val() ) {
						$list.addClass( 'wpforms-error' );
						return;
					}

					$el.removeClass( 'wpforms-error' );
					$list.removeClass( 'wpforms-error' );

					app.actions.init( {
						'action': action,
						'target': $el,
						'list_id': $list.val(),
						'account_id': $account.val(),
						'connection_id': connectionId,
					} );
				},
			},

			/**
			 * Action methods.
			 *
			 * @since 2.1.0
			 */
			updateProfile: {

				/**
				 * Callback-function on change event.
				 *
				 * @since 2.1.0
				 */
				changeCallback: function() {

					var $el         = $( this ),
						$connection = $el.closest( '.wpforms-builder-provider-connection' ),
						$notify     = $connection.find( '.js-wpforms-builder-mailchimp-provider-notify-user' );

					$notify.closest( '.wpforms-builder-provider-connection-setting' ).toggleClass( 'wpforms-hidden', $el.prop( 'checked' ) );

					if ( $el.prop( 'checked' ) ) {
						$notify.prop( 'checked', false );
					}
				},
			},
		},

		/**
		 * Triggers.
		 *
		 * @since 2.0.0
		 */
		triggers: {

			/**
			 * Call the generation method for each connections.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} e Event object.
			 * @param {object} data Connections data.
			 */
			connectionsDataLoadedHandler: function( e, data ) {

				_.each( data.connections, function( connection, connectionId ) {

					if ( _.isEmpty( connection ) ) {
						return;
					}

					app.connection.callbacks.generate( {
						connection: connection,
						conditional: app.Cache.getById( app.provider, 'conditionals', connectionId ),
					} );
				} );
			},

			/**
			 * Trigger an account change event, if it needed.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} e Event object.
			 * @param {object} data Connection generation data.
			 */
			connectionGeneratedHandler: function( e, data ) {

				var $connection = app.connection.getById( data.connection.id ),
					$account    = $( '.js-wpforms-builder-mailchimp-provider-connection-account', $connection );

				if ( '' !== $account.val() ) {
					$account.trigger( 'change' );
				}
			},

			/**
			 * Trigger an action change event, if it needed.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} e Event object.
			 * @param {string} provider Provider slug.
			 * @param {string} connectionId Connection ID.
			 */
			connectionGeneralSettingsRenderedHandler: function( e, provider, connectionId ) {

				var $connection = app.connection.getById( connectionId ),
					$action     = $( '.js-wpforms-builder-mailchimp-provider-connection-action', $connection );

				__private.replaceConnectionIds( connectionId, $connection );

				if ( '' !== $action.val() ) {
					$action.trigger( 'change' );
				}
			},

			/**
			 * Final things and initializations, when connection was rendered.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} e Event object.
			 * @param {string} provider Provider slug.
			 * @param {string} connectionId Connection ID.
			 */
			connectionRenderedHandler: function( e, provider, connectionId ) {

				var $connection = app.connection.getById( connectionId );

				__private.replaceConnectionIds( connectionId, $connection );
				app.mapFields( connectionId, $connection );
				app.loadChoicesJS( $connection );
			},

			/**
			 * Additional preparations for the mapped selects.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object}  e            Event object.
			 * @param {jQuery}  $connections All connections.
			 * @param {Object}  fields       Form fields.
			 * @param {boolean} isCustomCall True if trigger was called manually. Otherwise - undefined.
			 */
			updatedMapSelectsHandler( e, $connections, fields, isCustomCall ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.updatedMapSelectsHandler( e, $connections, fields, isCustomCall )" has been deprecated' );

				var $mpConnections = $connections.filter( '.wpforms-builder-mailchimp-provider-connection' );

				if ( _.isEmpty( $mpConnections ) ) {
					return;
				}

				app.helpers.updateMapSelects( $mpConnections, fields );

				if ( ! isCustomCall ) {
					app.helpers.maybeSaveFormState();
				}
			},

			/**
			 * Remove a Name field in "extended" format form Custom Fields Mapping table.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} e Event object.
			 * @param {string} id Field ID.
			 * @param {string} type Field type.
			 */
			wpformsFieldDeleteHandler: function( e, id, type ) {

				// Bail if it's not Name field.
				if ( ! _.isString( type ) || 'name' !== type ) {
					return;
				}

				var $deleteOptions = $( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-field-value option[value^="' + id + '."]', __private.$providerConnections );

				if ( $deleteOptions.length ) {
					$deleteOptions.remove();
				}
			},

			/**
			 * Register a `WPForms.Admin.Builder.Providers.updatedMapSelects` core trigger when Name Format option was changed.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object} e Event object.
			 */
			registerTriggerOnNameFormatChange( e ) { // eslint-disable-line no-unused-vars
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.registerTriggerOnNameFormatChange( e )" has been deprecated' );

				const $connections = __private.$providerConnections.find( '.wpforms-builder-provider-connection' );

				if ( $connections.length ) {
					app.Providers.panelHolder.trigger( 'WPForms.Admin.Builder.Providers.updatedMapSelects', [ $connections, wpf.getFields(), true ] );
				}
			},
		},

		/**
		 * On form save notify the user about the required GDPR field.
		 *
		 * @since 2.0.0
		 *
		 * @type {object}
		 */
		requireGDPR: {

			/**
			 * True if we don't have a GDPR field, but it's require in MC audience.
			 *
			 * @since 2.0.0
			 *
			 * @type {boolean}
			 */
			hasErrors: false,

			/**
			 * We need to notify the user only once.
			 *
			 * @since 2.0.0
			 *
			 * @type {boolean}
			 */
			isNotified: false,

			/**
			 * Initialization checks.
			 *
			 * @since 2.0.0
			 */
			init: function() {

				var $connections = __private.$providerConnections.find( '.wpforms-builder-provider-connection' ),
					$gdpr        = $( '#wpforms-panel-fields' ).find( '.wpforms-field-gdpr-checkbox' );

				if ( ! $connections.length || $gdpr.length ) {
					return;
				}

				app.requireGDPR.isNotified = false;
				$connections.each( app.requireGDPR.check );
			},

			/**
			 * Do the actual check.
			 *
			 * @since 2.0.0
			 *
			 * @returns {boolean} True means that the loop should be continue, false - break the loop.
			 */
			check: function() {

				var $connection = $( this ),
					$action     = $( '.js-wpforms-builder-mailchimp-provider-connection-action', $connection ),
					$lists      = $( '.js-wpforms-builder-mailchimp-provider-connection-lists', $connection );

				if ( ! $action.length || ! $lists.length ) {
					return true;
				}

				// Check permissions for Subscribe action only.
				if ( 'subscribe' !== $action.val() ) {
					return true;
				}

				var $selectedList = $lists.find( 'option:selected' );
				if ( ! $selectedList.data( 'marketing_permissions' ) ) {
					return true;
				}

				app.requireGDPR.hasErrors = true;

				// Give an opportunity to show another modals.
				setTimeout( function() {

					// Notify user.
					app.requireGDPR.notify( {
						connectionName: $connection.find( '.wpforms-builder-provider-connection-name' ).val(),
						listName: $selectedList.text().trim(),
					} );
				}, 100 );

				return false;
			},

			/**
			 * Modal that use for user notification.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} args Arguments.
			 */
			notify: function( args ) {

				if ( _.has( window, 'jconfirm' ) && ! _.isEmpty( window.jconfirm.instances ) ) {
					return;
				}

				if ( app.requireGDPR.hasErrors && ! app.requireGDPR.isNotified ) {

					var content = wpformsMailchimpBuilderVars.i18n.gdpr;

					content = content.replace( '{audience}', args.listName );
					content = content.replace( '{connection}', args.connectionName );

					$.alert( {
						title: wpforms_builder.heads_up,
						content: content,
						icon: 'fa fa-exclamation-circle',
						type: 'orange',
						buttons: {
							confirm: {
								text: wpforms_builder.ok,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
							},
						},
					} );

					// Save that we have already showed the user.
					app.requireGDPR.isNotified = true;
				}
			},
		},

		/**
		 * Actions property.
		 *
		 * @since 2.0.0
		 */
		actions: {

			/**
			 * Actions initialization.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} args Arguments.
			 */
			init: function( args ) {

				switch ( args.action ) {

					case 'subscribe':
						app.actions.subscribe.init( args );
						break;

					case 'unsubscribe':
						app.actions.unsubscribe.init( args );
						break;

					case 'archive':
						app.actions.archive.init( args );
						break;

					case 'delete':
						app.actions.delete.init( args );
						break;

					case 'record_event':
						app.actions.recordEvent.init( args );
						break;
				}
			},

			/**
			 * Subscribe action.
			 *
			 * @since 2.0.0
			 */
			subscribe: {

				/**
				 * Subscribe initialization.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				init: function( args ) {

					var sources = [ 'tags', 'groups', 'mergeFields' ],
						self    = this,
						index   = 0,
						data;

					for ( ; index < sources.length; index++ ) {
						data = app.Cache.get( app.provider, sources[ index ] );

						if (
							_.isEmpty( data ) ||
							! _.has( data, args.list_id )
						) {
							self.request( args );
							return;
						}
					}

					this.render( args );
				},

				/**
				 * AJAX request.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				request: function( args ) {

					var self = this;

					// Make ajax request to get lists and tags.
					app.Providers.ajax
						.request( app.provider, {
							data: {
								'task': 'objects_get',
								'list_id': args.list_id,
								'account_id': args.account_id,
								'connection_id': args.connection_id,
								'sources': {
									'tags': true,
									'groups': true,
									'mergeFields': true,
								},
							},
						} )
						.done( function( response ) {

							if (
								! response.success ||
								_.isEmpty( response.data )
							) {
								app.helpers.showErrorMsg( response.data );
								return;
							}

							// Cache response data.
							self.cache( response.data, args );

							// Render template.
							self.render( args );
						} );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {Object} args Arguments.
				 */
				render( args ) {
					const formFields = wpf.getFields(),
						mergeFields = app.Cache.getById( app.provider, 'mergeFields', args.list_id ),
						tmplSubscribe = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-subscribe' ),
						$connection = app.connection.getById( args.connection_id );
					let maybeHideMapTable = false,
						connection = app.Cache.getById( app.provider, 'connections', args.connection_id );

					// If it's another account - to pass in the template empty connection data.
					if ( connection.account_id !== args.account_id ) {
						connection = {
							id: connection.id,
						};
					} else {
						connection = app.connection.filterFieldsMeta( connection, mergeFields );
					}

					// Display compiled template.
					$connection
						.find( '.wpforms-builder-mailchimp-provider-actions-data' )
						.html(
							tmplSubscribe( {
								connection: connection,
								tags: app.Cache.getById( app.provider, 'tags', args.list_id ),
								groups: app.Cache.getById( app.provider, 'groups', args.list_id ),
								provider: app.provider,
							} ) + app.tmpl.callbacks.optionalFieldsHTML( args, formFields, mergeFields )
						);

					// Clean a Custom Fields table, if an Audience hasn't optional fields.
					if ( _.isObject( mergeFields ) && _.isEmpty( mergeFields.optional ) ) {
						maybeHideMapTable = true;
						$connection
							.find( '.wpforms-builder-provider-connection-fields-table tbody' ).html( '' );
					}

					// Add to Custom Fields table the required fields.
					var htmlRequiredFields = app.tmpl.callbacks.requiredFieldsHTML( connection, formFields, mergeFields );
					if ( ! _.isEmpty( htmlRequiredFields ) ) {
						$connection
							.find( '.wpforms-builder-provider-connection-fields-table tbody' )
							.prepend( htmlRequiredFields );

					// Or remove a whole Custom Fields table, if an Audience hasn't any fields.
					} else if ( maybeHideMapTable ) {
						$connection.find( '.wpforms-builder-provider-connection-fields' ).remove();
					}

					__private.$providerConnections.trigger( 'connectionRendered', [ app.provider, args.connection_id ] );
				},

				/**
				 * Cache response data.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} data Response data.
				 * @param {object} args Arguments.
				 */
				cache: function( data, args ) {

					var sources = [ 'tags', 'groups', 'mergeFields' ];

					_.each( sources, function( key ) {

						// "Register" cache keys.
						if ( _.isUndefined( app.Cache.get( app.provider, key ) ) ) {
							app.Cache.set( app.provider, key, {} );
						}

						// Save data to cache by keys.
						if ( _.has( data, key ) ) {
							app.Cache.addTo( app.provider, key, args.list_id, data[ key ] );
						}
					} );
				},
			},

			/**
			 * Unsubscribe action.
			 *
			 * @since 2.0.0
			 */
			unsubscribe: {

				/**
				 * Unsubscribe initialization.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				init: function( args ) {

					this.render( args );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				render: function( args ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-unsubscribe' );

					app.connection.getById( args.connection_id )
						.find( '.wpforms-builder-mailchimp-provider-actions-data' )
						.html( tmpl() );

					__private.$providerConnections.trigger( 'connectionRendered', [ app.provider, args.connection_id ] );
				},
			},

			/**
			 * Archive action.
			 *
			 * @since 2.0.0
			 */
			archive: {

				/**
				 * Archive initialization.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				init: function( args ) {

					this.render( args );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				render: function( args ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-archive' );

					app.connection.getById( args.connection_id )
						.find( '.wpforms-builder-mailchimp-provider-actions-data' )
						.html( tmpl() );

					__private.$providerConnections.trigger( 'connectionRendered', [ app.provider, args.connection_id ] );
				},
			},

			/**
			 * Delete action.
			 *
			 * @since 2.0.0
			 */
			delete: {

				/**
				 * Delete initialization.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				init: function( args ) {

					this.render( args );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				render: function( args ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-delete' );

					app.connection.getById( args.connection_id )
						.find( '.wpforms-builder-mailchimp-provider-actions-data' )
						.html( tmpl() );

					__private.$providerConnections.trigger( 'connectionRendered', [ app.provider, args.connection_id ] );
				},
			},

			/**
			 * Record event action.
			 *
			 * @since 2.0.0
			 */
			recordEvent: {

				/**
				 * Record event initialization.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				init: function( args ) {

					this.render( args );
				},

				/**
				 * Render HTML.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} args Arguments.
				 */
				render: function( args ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-record-event' );

					app.connection.getById( args.connection_id )
						.find( '.wpforms-builder-mailchimp-provider-actions-data' )
						.html(
							tmpl( {
								connection: app.Cache.getById( app.provider, 'connections', args.connection_id ),
								provider: app.provider,
							} )
						);

					__private.$providerConnections.trigger( 'connectionRendered', [ app.provider, args.connection_id ] );
				},
			},
		},

		/**
		 * All methods for *.tmpl files.
		 *
		 * @since 2.0.0
		 */
		tmpl: {

			/**
			 * Wrap functions for quickly compile *.tmpl files and receive their HTML.
			 *
			 * @since 2.0.0
			 */
			callbacks: {

				/**
				 * Compile and retrieve a HTML for common elements.
				 *
				 * @since 2.0.0
				 *
				 * @todo Since WPForms v1.9.5, this method will be redundant and can be deprecated once addon requirements are updated.
				 *
				 * @return {string} Compiled HTML.
				 */
				commonsHTML() {
					const tmplError = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-error' );

					return tmplError();
				},

				/**
				 * Compile and retrieve a HTML for optional fields.
				 *
				 * @since 2.0.0
				 *
				 * @param {Object} args        Arguments.
				 * @param {Object} formFields  Form Fields.
				 * @param {Object} mergeFields Mailchimp Merge Fields.
				 *
				 * @return {string} Compiled HTML.
				 */
				optionalFieldsHTML( args, formFields, mergeFields ) {
					if ( ! _.isObject( mergeFields ) || ! _.has( mergeFields, 'optional' ) ) {
						return '';
					}

					const tmpl = app.Templates.get( 'wpforms-providers-builder-content-connection-fields' );
					let connection = app.Cache.getById( app.provider, 'connections', args.connection_id );

					// If it's another account - to pass in the template empty connection data.
					if ( connection.account_id !== args.account_id ) {
						connection = {
							id: connection.id,
						};
					}

					return tmpl( {
						connection,
						fields: formFields,
						isSupportSubfields: true,
						provider: {
							slug: app.provider,
							placeholder: wpformsMailchimpBuilderVars.i18n.providerPlaceholder,
							fields: mergeFields.optional,
						},
					} );
				},

				/**
				 * Compile and retrieve a HTML for required fields.
				 *
				 * @since 2.0.0
				 *
				 * @param {object} connection Connection data.
				 * @param {object} formFields Form Fields.
				 * @param {object} mergeFields Mailchimp Merge Fields.
				 *
				 * @returns {string} Compiled HTML.
				 */
				requiredFieldsHTML: function( connection, formFields, mergeFields ) {

					var tmpl = app.Templates.get( 'wpforms-' + app.provider + '-builder-content-connection-required-fields' );

					// If an audience hasn't required fields.
					if (
						! _.isObject( mergeFields ) ||
						! _.has( mergeFields, 'required' ) ||
						_.isEmpty( mergeFields.required )
					) {
						return '';
					}

					return tmpl( {
						connection: connection,
						fields: formFields,
						isSupportSubfields: true,
						provider: {
							slug: app.provider,
							fields: mergeFields.required,
						},
					} );
				},
			},
		},

		/**
		 * Helper functions.
		 *
		 * @since 2.0.0
		 */
		helpers: {

			/**
			 * Prepare and retrieve fields for mapping.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object} fields Form fields.
			 *
			 * @return {Object} Form fields.
			 */
			getFieldsForMapping( fields ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.getFieldsForMapping( fields )" has been deprecated' );

				_.each( fields, function( field, key ) {

					if ( _.isEmpty( field ) || ! _.has( field, 'id' ) ) {
						return;
					}

					field.id = field.id.toString();

					if ( ! app.helpers.isNameField( field ) ) {
						return;
					}

					var fieldLabel = app.helpers.getFieldLabel( field ),
						newId;

					// Remove data for a Name field.
					delete fields[ key ];

					// Add data for Name field in "extended" format (Full, First, Middle and Last).
					_.each( wpformsMailchimpBuilderVars.i18n.nameFieldFormats, function( valueLabel, valueSlug ) {

						if (
							( _.has( field, 'format' ) && ( -1 !== field.format.indexOf( valueSlug ) ) ) ||
							( 'full' === valueSlug )
						) {
							newId = field.id + '.' + valueSlug;
							fields[ newId ] = {
								id: newId,
								label: fieldLabel + ' (' + valueLabel + ')',
								format: field.format,
							};
						}
					} );
				} );

				return fields;
			},

			/**
			 * Get field label with fallback.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object} field Field data.
			 *
			 * @return {string} Field label.
			 */
			getFieldLabel( field ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.getFieldLabel( field )" has been deprecated' );

				let fieldLabel = '';

				if ( ! _.has( field, 'id' ) || ! _.has( field, 'label' ) ) {
					return fieldLabel;
				}

				if ( field.label.toString().trim() !== '' ) {
					fieldLabel = wpf.sanitizeHTML( field.label.toString().trim() );
				} else {
					fieldLabel = wpforms_builder.field + ' #' + field.id;
				}

				return fieldLabel;
			},

			/**
			 * Retrieve Name field formats.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object} field Field data.
			 *
			 * @return {Array} Formats for Name field.
			 */
			getNameFormats( field ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.getNameFormats( field )" has been deprecated' );

				let formats = [];

				if ( ! _.has( field, 'format' ) || _.isEmpty( field.format ) ) {
					return formats;
				}

				formats = field.format.split( '-' );

				return _.isArray( formats ) ? formats : [];
			},

			/**
			 * Determine if it's a Name field.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {Object} field Field data.
			 *
			 * @return {boolean} True if it's a Name field, otherwise - false.
			 */
			isNameField( field ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.isNameField( field )" has been deprecated' );

				return ! _.isEmpty( field ) && _.has( field, 'type' ) && 'name' === field.type;
			},

			/**
			 * Update mapped selects.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {jQuery} $connections Selector with active connections.
			 * @param {Object} fields       Form fields.
			 */
			updateMapSelects( $connections, fields ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.updateMapSelects( $connections, fields )" has been deprecated' );

				_.each( fields, function( field ) {

					if ( ! app.helpers.isNameField( field ) ) {
						return;
					}

					var formats       = app.helpers.getNameFormats( field ),
						formatsAmount = formats.length;

					if ( formats.length > 1 ) {
						formatsAmount++;
					}

					var $optionsFormat = $( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-fields-table-row:first option[value^="' + field.id + '."]', $connections.first() );

					// Update <option>'s label, if a `Format` setting wasn't changed.
					if ( formatsAmount && formatsAmount === $optionsFormat.length ) {
						app.helpers.updateOptionsLabel(
							$( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-field-value option[value^="' + field.id + '."]', $connections ),
							app.helpers.getFieldLabel( field )
						);
						return;
					}

					if ( ! $optionsFormat.length ) {
						$optionsFormat = $( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-fields-table-row:first .wpforms-builder-provider-connection-field-value option', $connections.first() );
					}

					app.helpers.updateNameFieldOptions( $connections, $optionsFormat, field );
				} );
			},

			/**
			 * Update mapped options - do some specific things for options related with Name fields.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {jQuery} $connections Selector with active connections.
			 * @param {jQuery} $options     Selector with options.
			 * @param {Object} field        Form field.
			 */
			updateNameFieldOptions( $connections, $options, field ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.updateNameFieldOptions( $connections, $options, field )" has been deprecated' );

				var fieldLabel   = app.helpers.getFieldLabel( field ),
					pointerValue = field.id;

				if ( ! $options.filter( '[value="' + pointerValue + '"]' ).length ) {
					pointerValue = $options.last().val();
				}

				// Watching, that a Name field in "extended" format (Full, First, Middle and Last).
				_.each( wpformsMailchimpBuilderVars.i18n.nameFieldFormats, function( valueLabel, valueSlug ) {

					var optionValue       = field.id + '.' + valueSlug, // e.g., 1.first
						$optionFormatItem = $options.filter( '[value="' + optionValue + '"]' );

					if ( ( -1 === field.format.indexOf( valueSlug ) ) && ( 'full' !== valueSlug ) ) {

						// Remove <option>'s with format, which a Name field doesn't support.
						if ( $optionFormatItem.length ) {
							$( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-field-value option[value="' + optionValue + '"]', $connections ).remove();
						}
						return;
					}

					// Update <option> label with format, if it's already exists.
					if ( $optionFormatItem.length ) {
						pointerValue = optionValue;
						app.helpers.updateOptionsLabel( $optionFormatItem, fieldLabel );
						return;
					}

					// Add a new <option>.
					$( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-field-value option[value="' + pointerValue + '"]', $connections ).after(
						$( '<option>', {
							text: wpf.sanitizeHTML( fieldLabel + ' (' + valueLabel + ')' ),
							value: optionValue,
						} )
					);
					pointerValue = optionValue;
				} );

				// Remove <option>'s for Name fields without format.
				$( '.wpforms-builder-provider-connection-fields-table .wpforms-builder-provider-connection-field-value option[value="' + field.id + '"]', $connections ).remove();
			},

			/**
			 * Update label/text for mapped select options.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 *
			 * @param {jQuery} $options   Selector with options.
			 * @param {string} fieldLabel Field label.
			 */
			updateOptionsLabel( $options, fieldLabel ) {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.updateOptionsLabel( $options, fieldLabel )" has been deprecated' );

				_.each( $options, function( option ) {

					var $option  = $( option ),
						value    = $option.val(),
						label    = $option.text(),
						data     = value.split( '.' ),
						newLabel = fieldLabel,
						valueSlug;

					if ( ! _.isArray( data ) || data.length < 2 ) {
						return;
					}

					valueSlug = data[1];

					if ( _.has( wpformsMailchimpBuilderVars.i18n.nameFieldFormats, valueSlug ) ) {
						newLabel += ' (' + wpformsMailchimpBuilderVars.i18n.nameFieldFormats[ valueSlug ] + ')';
					}

					if ( label !== newLabel ) {
						$option.text( newLabel );
					}
				} );
			},

			/**
			 * Save the form state.
			 *
			 * @since 2.0.0
			 * @deprecated 2.5.0
			 */
			maybeSaveFormState() {
				// eslint-disable-next-line no-console
				console.warn( 'WARNING! Function "WPForms.Admin.Builder.Providers.Mailchimp.maybeSaveFormState()" has been deprecated' );

				const currentState = wpf.getFormState( '#wpforms-builder-form' );

				// If selects for mapping was changed, that whole form state was changed as well.
				// That's why we need to re-save it.
				if ( wpf.savedState !== currentState ) {
					wpf.savedState = currentState;
				}
			},

			/**
			 * Show the response error message.
			 *
			 * @since 2.0.0
			 *
			 * @param {object} data Response data.
			 */
			showErrorMsg: function( data ) {

				var errorMessage = _.isEmpty( data.error ) ? wpformsMailchimpBuilderVars.i18n.generalAjaxError : data.error;

				__private.$lock.val( 1 );
				__private.$error
					.html( wpf.sanitizeHTML( errorMessage ) )
					.show();
			},
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPForms.Admin.Builder.Providers.Mailchimp.init();
