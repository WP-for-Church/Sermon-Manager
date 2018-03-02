(function () {
    var element = document.getElementsByClassName('wpfc-sermon');
    
    for (var i = 0; i < element.length; i++) {
        if (element[i].offsetWidth > 600) {
            element[i].className += " wpfc-sermon-horizontal";
        }
    }
})();
