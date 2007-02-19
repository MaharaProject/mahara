addLoadEvent(function() {
    connect(window,'onkeypress',function(e) {
        var targetType = e.target().nodeName;
        
        if (
            targetType == 'INPUT'
            || targetType == 'TEXTAREA'
            || targetType == 'SELECT'
            || targetType == 'BUTTON'
        ) {
            return;
        }

        if (config.commandMode) {
            switch(e.key().string) {
                case 'a':
                    document.location.href = config.wwwroot + 'admin/';
                    break;
                case 'h':
                    document.location.href = config.wwwroot;
                    break;
                case 'b':
                    document.location.href = config.wwwroot + 'artefact/blog/';
                    break;
                case 'p':
                    document.location.href = config.wwwroot + 'artefact/internal/';
                    break;
                case 'f':
                    document.location.href = config.wwwroot + 'artefact/file/';
                    break;
                case 'g':
                    document.location.href = config.wwwroot + 'contacts/groups/';
                    break;
                case 'c':
                    document.location.href = config.wwwroot + 'contacts/communities/';
                    break;
                case 'o':
                    document.location.href = config.wwwroot + 'contacts/communities/owned.php';
                    break;
                case '/':
                    document.searchform.query.focus();
                    break;
            }
            config.commandMode = false;
        }
        else {
            if (e.key().string == 'g') {
                config.commandMode = true;
            }
        }
    });
});
