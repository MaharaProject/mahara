{include file="header.tpl"}
<div id="extract">
    <h3>{str tag=pleasewaitwhileyourfilesarebeingunzipped section=artefact.file}</h3>
    <div id="progress_iframe_outer" class="progress-iframe-outer">
        <div id="progress_iframe_inner" class="progress-iframe-inner">
            <div id="progress_meter_fill" class="meter-value" style="width: 0"></div>
        </div>
        <div id="progress_meter_message" class="text">{str tag=unzipprogress section=artefact.file arg1='0/0'}</div>
    </div>
    <script>
          function pmeter_update(data) {
              $('#progress_meter_fill').animate({ width: data.progress.percent + '%' });
              $('#progress_meter_message').text(data.progress.status);
              if (data.finished) {
                  $('#progress-iframe-complete').prop('href', data.next).removeClass('d-none');
                  clearTimeout(pmeter_timeout);
              }
          }

          sendjsonrequest(config.wwwroot + 'artefact/file/extract-progress.php', { 'percent': 0 }, 'POST', function (data) {
              pmeter_update(data.data);
          },
          function (data) {
              location.href = data.data.redirect;
          });

          var pmeter_timeout;

          function pmeter_update_timer(instance) {
              sendjsonrequest(config.wwwroot + 'artefact/file/extract-progress-percent.php', { 'instance' : instance }, 'GET', function(data) {
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

    <div>
        <a id="progress-iframe-complete" class="btn btn-primary d-none" href="#">{str tag=Continue section=artefact.file}</a>
    </div>
</div>
{include file="footer.tpl"}
