window.addEventListener('DOMContentLoaded', function () {
    var players = [], seeking = false,
        elements = document.querySelectorAll('.wpfc-sermon-player,.wpfc-sermon-video-player');

    for (var e in elements) {
        if (elements.hasOwnProperty(e)) {
            var seek = elements[e].dataset.plyr_seek;
            players.push(new Plyr(elements[e], {
                debug: wpfc_sm_plyr_settings.debug,
                plyr_seek: typeof seek !== 'undefined' ? parseInt(seek) : null
            }));
        }
    }

    for (var p in players) {
        if (players.hasOwnProperty(p)) {
            players[p].on('ready', function (event) {
                if (players[p].config.plyr_seek !== null) {
                    players[p].currentTime = players[p].config.plyr_seek;
                    seeking = true;
                }
            });

            players[p].on('playing', function () {
                if (seeking) {
                    players[p].pause();
                    seeking = false;
                }
            });
        }
    }
});
