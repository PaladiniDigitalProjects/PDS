/**
 * PDS Web Talk — conversación integrada (estilo inmersivo).
 * Sin dependencias. La conversación se acumula: cada turno (pregunta + respuesta)
 * se apila y la nueva pregunta sube a la parte alta de la vista, con la respuesta
 * escribiéndose debajo (efecto máquina de escribir). El historial se conserva en
 * localStorage para el contexto conversacional que se envía al backend.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'pdswtChatHistory';
	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) { fn(); }
		else { document.addEventListener( 'DOMContentLoaded', fn ); }
	}

	function loadHistory() {
		try { return JSON.parse( localStorage.getItem( STORAGE_KEY ) ) || []; }
		catch ( e ) { return []; }
	}
	function saveHistory( h ) {
		try { localStorage.setItem( STORAGE_KEY, JSON.stringify( h.slice( -20 ) ) ); } catch ( e ) {}
	}

	// Separa el cuerpo de la respuesta de la pregunta de seguimiento final
	// (última línea no vacía que termina en «?»), para poder estilarlas distinto.
	function splitFollowup( text ) {
		var lines = String( text ).split( '\n' );
		var i = lines.length - 1;
		while ( i >= 0 && '' === lines[ i ].trim() ) { i--; }
		if ( i >= 0 && /\?\s*$/.test( lines[ i ].trim() ) ) {
			return {
				body: lines.slice( 0, i ).join( '\n' ).replace( /\s+$/, '' ),
				followup: lines[ i ].trim()
			};
		}
		return { body: text, followup: '' };
	}

	function initWidget( root ) {
		var stage   = root.querySelector( '.pdswt-chat__stage' );
		var form    = root.querySelector( '.pdswt-chat__form' );
		var input   = root.querySelector( '.pdswt-chat__input' );
		var sendBtn = root.querySelector( '.pdswt-chat__send' );
		var clearBtn= root.querySelector( '.pdswt-chat__clear' );
		var closeBtn= root.querySelector( '.pdswt-chat__close' );
		var i18n    = ( window.pdswtChat && pdswtChat.i18n ) || {};
		var history = loadHistory();
		var busy    = false;
		var typer   = null; // timeout del efecto máquina de escribir en curso

		// Crea un turno (pregunta opcional + respuesta + fuentes) y lo añade al stage.
		function addTurn( ask ) {
			var turn = document.createElement( 'div' );
			turn.className = 'pdswt-chat__turn';

			if ( ask ) {
				var q = document.createElement( 'p' );
				q.className = 'pdswt-chat__ask';
				q.textContent = ask;
				turn.appendChild( q );
			}
			var reply = document.createElement( 'div' );
			reply.className = 'pdswt-chat__reply';
			turn.appendChild( reply );

			var followup = document.createElement( 'p' );
			followup.className = 'pdswt-chat__followup';
			followup.hidden = true;
			turn.appendChild( followup );

			var src = document.createElement( 'p' );
			src.className = 'pdswt-chat__sources';
			src.hidden = true;
			turn.appendChild( src );

			var pieces = document.createElement( 'div' );
			pieces.className = 'pdswt-chat__pieces';
			pieces.hidden = true;
			turn.appendChild( pieces );

			stage.appendChild( turn );
			return { turn: turn, reply: reply, followup: followup, src: src, pieces: pieces };
		}

		// Muestra la pregunta de seguimiento (si la hay) en su elemento propio.
		function setFollowup( el, text ) {
			if ( text ) { el.textContent = text; el.hidden = false; }
			else { el.hidden = true; el.textContent = ''; }
		}

		// Pinta las piezas visuales (tarjetas de proyecto/partner) bajo la respuesta.
		function renderPieces( el, pieces ) {
			el.innerHTML = '';
			if ( ! pieces || ! pieces.length ) { el.hidden = true; return; }

			// Antetítulo según la categoría dominante de las piezas mostradas.
			var labels = i18n.pieceLabels || {};
			var cat    = pieces[ 0 ] && pieces[ 0 ].category;
			var label  = document.createElement( 'p' );
			label.className = 'pdswt-chat__pieces-label';
			label.textContent = ( cat && labels[ cat ] ) ? labels[ cat ] : ( i18n.example || 'examples:' );
			el.appendChild( label );

			pieces.forEach( function ( pz ) {
				var a = document.createElement( 'a' );
				a.className = 'pdswt-piece-card solution zoom10';
				a.href = pz.link; a.target = '_blank'; a.rel = 'noopener';

				var media = document.createElement( 'div' );
				media.className = 'pdswt-piece-card__media';
				if ( pz.image ) {
					media.style.backgroundImage = 'url("' + pz.image + '")';
				} else {
					// Sin imagen (p. ej. una página de PDS): fondo azul de marca.
					a.classList.add( 'pdswt-piece-card--flat' );
				}

				if ( pz.logo ) {
					var logo = document.createElement( 'img' );
					logo.className = 'pdswt-piece-card__logo';
					logo.src = pz.logo; logo.alt = '';
					media.appendChild( logo );
				}
				var title = document.createElement( 'div' );
				title.className = 'pdswt-piece-card__title';
				title.textContent = pz.title || '';
				media.appendChild( title );

				a.appendChild( media );
				el.appendChild( a );
			} );
			el.hidden = false;
		}

		// ── Oferta de envío por email (tras N preguntas) ──────────────────
		function emailDone() {
			try { return '1' === localStorage.getItem( 'pdswtEmailDone' ); } catch ( e ) { return false; }
		}
		function setEmailDone() {
			try { localStorage.setItem( 'pdswtEmailDone', '1' ); } catch ( e ) {}
		}

		// Tras la respuesta: si ya hay suficientes preguntas y no se ofreció, propone.
		function maybeOfferEmail() {
			if ( emailDone() || stage.querySelector( '.pdswt-chat__email' ) ) { return; }
			var asks = 0;
			for ( var k = 0; k < history.length; k++ ) {
				if ( 'user' === history[ k ].role ) { asks++; }
			}
			var threshold = ( window.pdswtChat && pdswtChat.emailAfter ) || 6;
			if ( asks >= threshold ) { offerEmail(); }
		}

		function offerEmail() {
			var box = document.createElement( 'div' );
			box.className = 'pdswt-chat__email';

			var prompt = document.createElement( 'p' );
			prompt.className = 'pdswt-chat__email-prompt';
			prompt.textContent = i18n.emailPrompt || 'Want a copy of this conversation by email?';
			box.appendChild( prompt );

			var form = document.createElement( 'form' );
			form.className = 'pdswt-chat__email-form';

			var mail = document.createElement( 'input' );
			mail.type = 'email';
			mail.className = 'pdswt-chat__email-input';
			mail.placeholder = i18n.emailPlaceholder || 'your@email.com';
			mail.required = true;
			form.appendChild( mail );

			// Honeypot: oculto para humanos, tentador para bots.
			var hp = document.createElement( 'input' );
			hp.type = 'text';
			hp.name = 'website';
			hp.className = 'pdswt-chat__email-hp';
			hp.tabIndex = -1;
			hp.setAttribute( 'autocomplete', 'off' );
			hp.setAttribute( 'aria-hidden', 'true' );
			form.appendChild( hp );

			var sendBtnEl = document.createElement( 'button' );
			sendBtnEl.type = 'submit';
			sendBtnEl.className = 'pdswt-chat__email-send';
			sendBtnEl.textContent = i18n.emailSend || 'Send it to me';
			form.appendChild( sendBtnEl );

			box.appendChild( form );

			var skip = document.createElement( 'button' );
			skip.type = 'button';
			skip.className = 'pdswt-chat__email-skip';
			skip.textContent = i18n.emailSkip || 'No, thanks';
			box.appendChild( skip );

			var priv = document.createElement( 'p' );
			priv.className = 'pdswt-chat__email-privacy';
			priv.textContent = i18n.emailPrivacy || '';
			box.appendChild( priv );

			function feedback( msg, isError ) {
				prompt.textContent = msg;
				form.remove(); skip.remove();
				if ( isError ) { box.classList.add( 'is-error' ); return; }
				// Confirmación breve y luego la opción desaparece.
				box.classList.add( 'is-sent' );
				priv.remove();
				setTimeout( function () {
					box.style.transition = 'opacity .4s ease';
					box.style.opacity = '0';
					setTimeout( function () { box.remove(); }, 400 );
				}, 3000 );
			}

			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var addr = mail.value.trim();
				if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( addr ) ) {
					mail.focus();
					return;
				}
				sendBtnEl.disabled = true;
				fetch( pdswtChat.transcriptUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					credentials: 'same-origin',
					body: JSON.stringify( { email: addr, website: hp.value, history: history } )
				} ).then( function ( r ) {
					return r.json().then( function ( d ) { return { ok: r.ok, data: d }; } );
				} ).then( function ( res ) {
					if ( res.ok && res.data && res.data.ok ) {
						setEmailDone();
						feedback( i18n.emailSent || 'Sent!', false );
					} else {
						sendBtnEl.disabled = false;
					}
				} ).catch( function () { sendBtnEl.disabled = false; } );
			} );

			skip.addEventListener( 'click', function () {
				setEmailDone();
				box.remove();
			} );

			stage.appendChild( box );
			box.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
		}

		function renderSources( el, sources ) {
			el.innerHTML = '';
			if ( ! sources || ! sources.length ) { el.hidden = true; return; }
			el.appendChild( document.createTextNode( ( i18n.sources || 'Sources' ) + ': ' ) );
			sources.forEach( function ( s, i ) {
				var a = document.createElement( 'a' );
				a.href = s.link; a.target = '_blank'; a.rel = 'noopener';
				a.textContent = s.title;
				el.appendChild( a );
				if ( i < sources.length - 1 ) { el.appendChild( document.createTextNode( ' · ' ) ); }
			} );
			el.hidden = false;
		}

		function isFull() { return root.classList.contains( 'is-fullscreen' ); }

		// Al enfocar el input, el chat cubre el viewport; la página queda detrás,
		// pero la cabecera fija del site permanece visible por encima.
		function enterFull() {
			if ( isFull() ) { return; }
			var header = document.querySelector( 'header.wp-block-template-part' ) || document.querySelector( 'header' );
			var hh = ( header && getComputedStyle( header ).position === 'fixed' ) ? Math.round( header.getBoundingClientRect().height ) : 0;
			root.style.setProperty( '--pdswt-header-h', hh + 'px' );
			root.classList.add( 'is-fullscreen' );
			if ( closeBtn ) { closeBtn.hidden = false; }
			document.body.style.overflow = 'hidden';
			stage.scrollTop = stage.scrollHeight;
		}
		function exitFull() {
			if ( ! isFull() ) { return; }
			root.classList.remove( 'is-fullscreen' );
			if ( closeBtn ) { closeBtn.hidden = true; }
			document.body.style.overflow = '';
		}

		// Mantiene el final del texto visible mientras se escribe: dentro del
		// escenario en pantalla completa, o siguiendo el scroll de la ventana.
		function keepInView( el ) {
			if ( isFull() ) {
				stage.scrollTop = stage.scrollHeight;
				return;
			}
			var margin = 80;
			var r = el.getBoundingClientRect();
			if ( r.bottom > window.innerHeight - margin ) {
				window.scrollBy( 0, Math.ceil( r.bottom - ( window.innerHeight - margin ) ) );
			}
		}

		// Escribe el texto en `el` con efecto máquina de escribir; done() al acabar.
		function typewrite( el, text, done ) {
			if ( typer ) { clearTimeout( typer ); typer = null; }
			el.textContent = '';
			el.classList.remove( 'is-error' );

			if ( REDUCED ) { el.textContent = text; if ( done ) { done(); } return; }

			var caret = document.createElement( 'span' );
			caret.className = 'pdswt-chat__caret';
			el.appendChild( document.createTextNode( '' ) );
			el.appendChild( caret );

			var i = 0;
			var step = text.length > 400 ? 8 : ( text.length > 180 ? 14 : 22 );
			function tick() {
				i++;
				el.firstChild.textContent = text.slice( 0, i );
				keepInView( caret );
				if ( i < text.length ) { typer = setTimeout( tick, step ); }
				else { caret.remove(); typer = null; if ( done ) { done(); } }
			}
			tick();
		}

		function setBusy( state ) {
			busy = state;
			sendBtn.disabled = state;
			input.disabled = state;
		}

		function send( text ) {
			if ( busy || ! text.trim() ) { return; }

			setBusy( true );
			history.push( { role: 'user', content: text } );
			saveHistory( history );

			// Nuevo turno con la pregunta; sube a la parte alta de la vista.
			var t = addTurn( text );
			t.turn.scrollIntoView( { behavior: 'smooth', block: 'start' } );

			// Estado "escribiendo" mientras llega la respuesta.
			var caret = document.createElement( 'span' );
			caret.className = 'pdswt-chat__caret';
			t.reply.appendChild( caret );

			var payload = {
				message: text,
				history: history.filter( function ( m ) { return m.role === 'user' || m.role === 'assistant'; } )
					.slice( -6 ).map( function ( m ) { return { role: m.role, content: m.content }; } )
			};

			fetch( pdswtChat.restUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'same-origin',
				body: JSON.stringify( payload )
			} ).then( function ( r ) {
				return r.json().then( function ( data ) { return { ok: r.ok, data: data }; } );
			} ).then( function ( res ) {
				if ( res.ok && res.data && res.data.reply ) {
					var sources = res.data.sources || [];
					var pieces  = res.data.pieces || [];
					var split   = splitFollowup( res.data.reply );
					typewrite( t.reply, split.body, function () {
						setFollowup( t.followup, split.followup );
						renderPieces( t.pieces, pieces );
						maybeOfferEmail();
					} );
					history.push( { role: 'assistant', content: res.data.reply, sources: sources, pieces: pieces } );
					saveHistory( history );
				} else {
					t.reply.textContent = ( res.data && res.data.error ) ? res.data.error : ( i18n.error || 'Error' );
					t.reply.classList.add( 'is-error' );
				}
			} ).catch( function () {
				t.reply.textContent = i18n.error || 'Error';
				t.reply.classList.add( 'is-error' );
			} ).then( function () { setBusy( false ); input.focus(); } );
		}

		// Reconstruye la conversación: welcome arriba + turnos guardados.
		function renderConversation() {
			stage.innerHTML = '';
			var welcome = root.getAttribute( 'data-welcome' );
			var fresh   = ! history.length;

			if ( welcome ) {
				var w = addTurn( null );
				if ( fresh ) { typewrite( w.reply, welcome ); }
				else { w.reply.textContent = welcome; }
			}

			for ( var i = 0; i < history.length; i++ ) {
				if ( history[ i ].role === 'user' ) {
					var bot = ( history[ i + 1 ] && history[ i + 1 ].role === 'assistant' ) ? history[ i + 1 ] : null;
					var t = addTurn( history[ i ].content );
					if ( bot ) {
						var sb = splitFollowup( bot.content );
						t.reply.textContent = sb.body;
						setFollowup( t.followup, sb.followup );
						renderPieces( t.pieces, bot.pieces );
						i++;
					}
				} else if ( history[ i ].role === 'assistant' ) {
					var a = addTurn( null );
					var sa = splitFollowup( history[ i ].content );
					a.reply.textContent = sa.body;
					setFollowup( a.followup, sa.followup );
					renderPieces( a.pieces, history[ i ].pieces );
				}
			}
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var text = input.value.trim();
			if ( ! text ) { return; }
			input.value = '';
			input.style.height = 'auto';
			send( text );
		} );

		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' && ! e.shiftKey ) {
				e.preventDefault();
				form.dispatchEvent( new Event( 'submit', { cancelable: true } ) );
			}
		} );

		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () { exitFull(); input.blur(); } );
		}
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) { exitFull(); }
		} );
		input.addEventListener( 'input', function () {
			input.style.height = 'auto';
			input.style.height = Math.min( input.scrollHeight, 140 ) + 'px';
		} );

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function () {
				history = [];
				saveHistory( history );
				renderConversation();
				input.focus();
			} );
		}

		renderConversation();
	}

	ready( function () {
		if ( ! window.pdswtChat ) { return; }
		var widgets = document.querySelectorAll( '.pdswt-chat' );
		Array.prototype.forEach.call( widgets, initWidget );
	} );
} )();
