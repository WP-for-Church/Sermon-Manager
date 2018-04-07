var refTagger = {
    settings: {
        bibleVersion: verse.bible_version
    }
};

(function (d, t) {
    var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
    g.src = "https://api.reftagger.com/v2/RefTagger" + (verse.language === 'es_ES' ? '.es' : '') + ".js";
    s.parentNode.insertBefore(g, s);
}(document, "script"));