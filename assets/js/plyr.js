if ( typeof sm_data === 'undefined' ) {
	sm_data = {
		debug: false,
		use_native_player_safari: false,
	};
} else {
	sm_data.debug = sm_data.debug === '1';
	sm_data.use_native_player_safari = sm_data.use_native_player_safari === '1';
}

window.addEventListener( 'DOMContentLoaded', function () {
	const players = Plyr.setup( document.querySelectorAll( '.wpfc-sermon-player,.wpfc-sermon-video-player' ), {
		debug: sm_data.debug,
		enabled: sm_data.use_native_player_safari ? ( !/Safari/.test( navigator.userAgent ) || ( /Safari/.test( navigator.userAgent ) && /Chrome|OPR/.test( navigator.userAgent ) ) ) : true,
	} );

	for ( let p in players ) {
		if ( players.hasOwnProperty( p ) ) {
			players[ p ].on( 'loadedmetadata ready', function ( event ) {
				let instance = event.detail.plyr;

				if ( instance.elements.original.dataset.plyr_seek !== undefined ) {
					instance.currentTime = parseInt( instance.elements.original.dataset.plyr_seek );
					instance.embed.setCurrentTime(parseInt( instance.elements.original.dataset.plyr_seek ));
				}
			} );
		}
	}
} );
