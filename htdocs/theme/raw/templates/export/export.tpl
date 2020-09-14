{include file="header.tpl"}
<div id="exportgeneration">
    <h3>{str tag=pleasewaitwhileyourexportisbeinggenerated section=export}</h3>
    <div id="progress_iframe_outer" class="progress-iframe-outer">
        <div id="progress_iframe_inner" class="progress-iframe-inner">
            <div id="progress_meter_fill" class="meter-value" style="width: 0"></div>
        </div>
        <div id="progress_meter_message" class="text">{str tag=unzipprogress section=artefact.file arg1='0/0'}</div>
    </div>
    <script>
        var pmeter_timeout;
        function pmeter_update(data) {
            $('#progress_meter_fill').animate({ width: data.progress.percent + '%' });
            $('#progress_meter_message').text(data.progress.status);
            if (data.finished) {
                clearTimeout(pmeter_timeout);
            }
        }

        sendjsonrequest(config.wwwroot + 'export/download.php', { 'percent': 0 }, 'POST', function (data) {
            pmeter_update(data.data);
            $('#export_continue').removeClass('btn-secondary').addClass('btn-primary');
            location.href = data.data.serve_file;
        }, function (data) {
            location.href = data.data.redirect;
        });

        function pmeter_update_timer(instance) {
            sendjsonrequest(config.wwwroot + 'export/download-progress.php', { 'instance' : instance }, 'GET', function(data) {
                if (typeof(data) != 'undefined') {
                    if (!data.data.finished) {
                        pmeter_timeout = setTimeout(function() { pmeter_update_timer(instance) }, 300);
                    }
                    pmeter_update(data.data);
                }
            }, false, true, false, true);
        }
        pmeter_update_timer('progress_iframe_inner');
    </script>
    <a class="btn btn-secondary" id="export_continue" href="{$WWWROOT}export/index.php">{str tag=Continue section=artefact.file}</a>
</div>
{include file="footer.tpl"}
