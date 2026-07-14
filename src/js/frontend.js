/**
 * Bolt Share frontend.
 */

const initializedRoots = new WeakSet();
const instances = new WeakMap();
let documentListenersBound = false;

/**
 * @typedef {Object} BoltShareInstance
 * @property {HTMLElement} root
 * @property {HTMLButtonElement} trigger
 * @property {HTMLElement} panel
 * @property {HTMLElement|null} status
 * @property {boolean} isOpen
 */

document.addEventListener( 'DOMContentLoaded', initAll );

function initAll() {
	document.querySelectorAll( '[data-bolt-share]' ).forEach( ( root ) => {
		if ( initializedRoots.has( root ) ) {
			return;
		}

		initializedRoots.add( root );
		initInstance( root );
	} );

	if ( ! documentListenersBound ) {
		document.addEventListener( 'click', handleDocumentClick );
		document.addEventListener( 'keydown', handleDocumentKeydown );
		documentListenersBound = true;
	}
}

/**
 * @param {HTMLElement} root
 */
function initInstance( root ) {
	const trigger = root.querySelector( '.bolt-share__trigger' );
	const panel = root.querySelector( '[data-bolt-share-panel]' );

	if ( ! ( trigger instanceof HTMLButtonElement ) || ! panel ) {
		return;
	}

	/** @type {BoltShareInstance} */
	const instance = {
		root,
		trigger,
		panel,
		status: root.querySelector( '[data-bolt-share-status]' ),
		isOpen: false,
	};

	instances.set( root, instance );

	trigger.addEventListener( 'click', () => {
		if ( instance.isOpen ) {
			closePanel( instance );
			return;
		}

		closeOtherPanels( root );
		openPanel( instance );
	} );

	const instagramButton = root.querySelector( '[data-bolt-share-instagram]' );

	if ( instagramButton instanceof HTMLButtonElement ) {
		instagramButton.addEventListener( 'click', () => {
			shareToInstagram( instagramButton, instance );
		} );
	}
}

/**
 * @param {BoltShareInstance} instance
 */
function openPanel( instance ) {
	instance.panel.hidden = false;
	instance.trigger.setAttribute( 'aria-expanded', 'true' );
	instance.isOpen = true;
}

/**
 * @param {BoltShareInstance} instance
 */
function closePanel( instance ) {
	instance.panel.hidden = true;
	instance.trigger.setAttribute( 'aria-expanded', 'false' );
	instance.isOpen = false;
	setStatus( instance, '' );
}

/**
 * @param {HTMLElement} exceptRoot
 */
function closeOtherPanels( exceptRoot ) {
	document.querySelectorAll( '[data-bolt-share]' ).forEach( ( root ) => {
		if ( root === exceptRoot ) {
			return;
		}

		const instance = instances.get( root );

		if ( instance?.isOpen ) {
			closePanel( instance );
		}
	} );
}

/**
 * @param {MouseEvent} event
 */
function handleDocumentClick( event ) {
	const target = event.target;

	if ( ! ( target instanceof Node ) ) {
		return;
	}

	document.querySelectorAll( '[data-bolt-share]' ).forEach( ( root ) => {
		const instance = instances.get( root );

		if ( ! instance?.isOpen ) {
			return;
		}

		if ( ! root.contains( target ) ) {
			closePanel( instance );
		}
	} );
}

/**
 * @param {KeyboardEvent} event
 */
function handleDocumentKeydown( event ) {
	if ( event.key !== 'Escape' ) {
		return;
	}

	document.querySelectorAll( '[data-bolt-share]' ).forEach( ( root ) => {
		const instance = instances.get( root );

		if ( ! instance?.isOpen ) {
			return;
		}

		closePanel( instance );
		instance.trigger.focus();
	} );
}

/**
 * @param {HTMLButtonElement} button
 * @param {BoltShareInstance} instance
 */
async function shareToInstagram( button, instance ) {
	const url = button.dataset.shareUrl || '';
	const title = button.dataset.shareTitle || '';

	if ( ! url ) {
		return;
	}

	if ( typeof navigator.share === 'function' ) {
		try {
			await navigator.share( { title, url } );
			return;
		} catch ( error ) {
			if ( isShareCancellation( error ) ) {
				return;
			}
		}
	}

	try {
		await copyUrl( url );
		setStatus( instance, getInstagramCopiedMessage() );
	} catch {
		setStatus( instance, getInstagramCopiedMessage() );
	}
}

/**
 * @param {string} url
 */
async function copyUrl( url ) {
	if ( navigator.clipboard?.writeText ) {
		try {
			await navigator.clipboard.writeText( url );
			return;
		} catch {
			// Fall through to legacy copy method.
		}
	}

	const textarea = document.createElement( 'textarea' );
	textarea.value = url;
	textarea.setAttribute( 'readonly', '' );
	textarea.style.position = 'absolute';
	textarea.style.left = '-9999px';
	document.body.appendChild( textarea );
	textarea.select();
	document.execCommand( 'copy' );
	document.body.removeChild( textarea );
}

/**
 * @param {BoltShareInstance} instance
 * @param {string} message
 */
function setStatus( instance, message ) {
	if ( ! instance.status ) {
		return;
	}

	instance.status.textContent = message;
}

function getInstagramCopiedMessage() {
	if (
		typeof boltShareL10n !== 'undefined' &&
		typeof boltShareL10n.instagramCopied === 'string' &&
		boltShareL10n.instagramCopied
	) {
		return boltShareL10n.instagramCopied;
	}

	return 'Link copied – open Instagram and paste it.';
}

/**
 * @param {unknown} error
 */
function isShareCancellation( error ) {
	return (
		typeof error === 'object' &&
		error !== null &&
		'name' in error &&
		error.name === 'AbortError'
	);
}
