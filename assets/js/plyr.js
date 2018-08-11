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
	var players = plyr.setup( document.querySelectorAll( '.wpfc-sermon-player,.wpfc-sermon-video-player' ), {
		debug: sm_data.debug,
		enabled: sm_data.use_native_player_safari ? ( !/Safari/.test( navigator.userAgent ) || ( /Safari/.test( navigator.userAgent ) && /Chrome|OPR/.test( navigator.userAgent ) ) ) : true,
	} );
	for ( var p in players ) {
		if ( players.hasOwnProperty( p ) ) {
			players[ p ].on( 'loadedmetadata ready', function ( event ) {
				if ( typeof this.firstChild.dataset.plyr_seek !== 'undefined' ) {
					var instance = event.detail.plyr;
					instance.seek( parseInt( this.firstChild.dataset.plyr_seek ) );
				}
			} );
		}
	}
} );
