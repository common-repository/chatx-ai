var ChatxAI = {
    canClick: false,
    startup: function() {
        window._chatxInstnace = new ChatxSearch(chatxSettings.api_key)
    },
    isDev: function() {
        return document.cookie.indexOf('chatxdev=') !== -1;
    },
    log: function(log) {
        if (!this.isDev()) return;
        if (typeof log == "object") {
            return console.log(log);
        }
        console.log('ChatX : %s', log);
    }
}
jQuery(document).ready(function($) {
    ChatxAI.startup();
});
