/**
 * Adds keystroke navigation to Mahara.
 *
 * Copyright: 2006-2008 Catalyst IT Ltd
 * This file is licensed under the same terms as Mahara itself
 */
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
                    document.location.href = config.wwwroot + 'group/mygroups.php';
                    break;
                case 'v':
                    document.location.href = config.wwwroot + 'view';
                    break;
                case '/':
                    document.usf.query.focus();
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
