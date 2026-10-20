( function () {
	'use strict';

	const guide = window.GMA_SHOWCASE;
	const KEY = 'gma-showcase-closed';

	if ( ! guide ) {
		return;
	}

	const el = ( tag, className, text ) => {
		const node = document.createElement( tag );
		if ( className ) {
			node.className = className;
		}
		if ( text ) {
			node.innerHTML = text;
		}
		return node;
	};

	// Markers: a numbered button beside each part of the screen, with its tooltip.
	const found = guide.tips.filter( ( tip ) => document.querySelector( tip[ 0 ] ) );

	found.forEach( ( tip, index ) => {
		const target = document.querySelector( tip[ 0 ] );
		const id = 'gma-sc-tip-' + index;
		const marker = el( 'span', 'gma-sc-marker' );
		const button = el( 'button', 'gma-sc-marker__button', String( index + 1 ) );
		const bubble = el( 'span', 'gma-sc-marker__tip', '<strong>' + tip[ 1 ] + '</strong> ' + tip[ 2 ] );

		button.type = 'button';
		button.setAttribute( 'aria-describedby', id );
		button.setAttribute( 'aria-label', 'About: ' + tip[ 1 ] );
		bubble.id = id;
		bubble.setAttribute( 'role', 'tooltip' );
		marker.append( button, bubble );

		// Inputs and selects cannot hold children, so the marker goes after them.
		if ( [ 'INPUT', 'SELECT', 'TEXTAREA' ].includes( target.tagName ) ) {
			target.insertAdjacentElement( 'afterend', marker );
		} else {
			target.append( marker );
		}
	} );

	// The panel: what this screen is, and the same numbered points as the markers.
	const panel = el( 'aside', 'gma-sc-panel' );
	const toggle = el( 'button', 'gma-sc-panel__toggle', 'What am I looking at?' );
	const body = el( 'div', 'gma-sc-panel__body' );

	panel.setAttribute( 'aria-label', 'About this screen' );
	toggle.type = 'button';
	toggle.id = 'gma-sc-toggle';
	body.id = 'gma-sc-body';
	toggle.setAttribute( 'aria-controls', body.id );

	body.append( el( 'h2', 'gma-sc-panel__title', guide.title ) );
	guide.intro.forEach( ( text ) => body.append( el( 'p', '', text ) ) );

	if ( found.length ) {
		const list = el( 'ol', 'gma-sc-panel__points' );
		found.forEach( ( tip ) => list.append( el( 'li', '', '<strong>' + tip[ 1 ] + ':</strong> ' + tip[ 2 ] ) ) );
		body.append( el( 'p', 'gma-sc-panel__hint', 'Hover over or tab to the numbered markers on the page, or read them here:' ), list );
	}

	body.append( el( 'p', 'gma-sc-panel__start', '<a href="' + guide.start + '">Back to Start here</a>' ) );
	panel.append( toggle, body );
	document.body.append( panel );

	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		body.hidden = ! open;
		try {
			open ? sessionStorage.removeItem( KEY ) : sessionStorage.setItem( KEY, '1' );
		} catch ( e ) {}
	};

	let closed = false;
	try {
		closed = '1' === sessionStorage.getItem( KEY );
	} catch ( e ) {}

	setOpen( ! closed );
	toggle.addEventListener( 'click', () => setOpen( 'true' !== toggle.getAttribute( 'aria-expanded' ) ) );

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' === event.key && ! body.hidden && panel.contains( document.activeElement ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );
}() );
