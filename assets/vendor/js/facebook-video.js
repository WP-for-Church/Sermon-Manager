/* taken from https://developers.facebook.com/docs/plugins/embedded-video-player#wordpress */

window.fbAsyncInit = function() {
    FB.init({
        xfbml      : true,
        version    : 'v2.5'
    });
}; (function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));