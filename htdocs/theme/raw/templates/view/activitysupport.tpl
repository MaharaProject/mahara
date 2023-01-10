{if $is_activity_page}
    <div class="bt-inbox card card-secondary clearfix collapsible ">
        <h2 class="title card-header js-heading">
            <a data-bs-toggle="collapse" href="#target" aria-expanded="true" class="outer-link"></a>
            {$activity->description}
            <span class="icon icon-chevron-down collapse-indicator float-end" role="presentation" aria-hidden="true">
            </span>
            <ul class="nav float-right">
                <li> <span class="btn">{str tag="outcome" section="collection"}: {$activity->outcome}</span></li>
                <li> <span class="btn">{str tag="outcometype" section="collection"}:
                        <div id="outcometype-{$outcome->id}" class="outcome-type">
                            <span
                                class="badge rounded-pill text-bg-{$activity->styleclass}">{$activity->outcome_type}</span>
                        </div>
                    </span>
                </li>
                <li> <div style="position: relative; z-index: 3" class="btn">{$activity_achieved_switch|safe}
                        <input id="hidden_achieved" type="hidden" name="achieved" value="false" />
                    </div>
                </li>
            </ul>
        </h2>

        <div class="block collapse hide" id="target" aria-expanded="true">
            {$activity_support_form|safe}
        </div>
    </div>
{/if}

{* signoff modal form *}
<div tabindex="0" class="modal fade" id="signoff-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=signoffpagetitle section=view}
                </h1>
            </div>
            <div class="modal-body">
                <p id="signoff-off">{str tag=activitysignoff section=collection}</p>
                <div class="btn-group">
                    <button id="signoff-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="signoff-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>

{* undo signoff modal form *}
<div tabindex="0" class="modal fade" id="unsignoff-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=signoffpagetitle section=view}
                </h1>
            </div>
            <div class="modal-body">
                <p id="signoff-on">{str tag=activitysignoffundo section=collection}</p>
                <div class="btn-group">
                    <button id="unsignoff-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="unsignoff-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
    $j(".activity_support").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const supportText = $('textarea#activity_support_' + this.id)[0].value;
        const postData = {
            supportType: this.id,
            supportText: supportText,
            activityId: {$activity->id},
            viewId: {$activity->view}
        };

        sendjsonrequest(
            '{$WWWROOT}view/activitysupport.json.php',
            postData,
            'POST', data => data,
            function(error) {
                console.log(error);
            });
    });

$j("#dummy_activity_form_achieved").on('click',  function(e) {
    e.stopPropagation();
    e.preventDefault();
    let isAchieved = e.currentTarget

    // Trigger the verify modal
    // Set the switch to the opposite
    if (!isAchieved) {
        $("#signoff-confirm-form").modal('show');
    } else {
        $("#unsignoff-confirm-form").modal('show');
    }});

    // click 'No' button on modals - cancel signoff
    $("#signoff-back-button").on('click', function() {
      $("#signoff-confirm-form").modal('hide');
    });

    // click 'Yes' button on modals - mark activity as signed off
    $('#signoff-yes-button').on('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      var activityid = $("#signoff-confirm-form").attr('activity');
      sendjsonrequest('{$WWWROOT}collection/updateactivity.json.php', { 'update_type': 'signoff', 'activityid': {$activity->id}, 'collectionid': {$collectionid} }, 'POST', function (data) {
        if (data) {
          $("#signoff-confirm-form").modal('hide');
          location.reload();
        }
      }, function(error) {
        console.log(error);
      });
    });

    // click 'No' button on modals - keep signed off value
    $("#unsignoff-back-button").on('click', function() {
      $("#unsignoff-confirm-form").modal('hide');
      location.reload();
    });

    // click 'Yes' button on modals - remove signoff state
    $('#unsignoff-yes-button').on('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      var activityid = $("#unsignoff-confirm-form").attr('activity');
      sendjsonrequest('{$WWWROOT}collection/updateactivity.json.php', { 'update_type': 'unsignoff', 'activityid': {$activity->id}, 'collectionid': {$collectionid} }, 'POST', function (data) {
        if (data) {
          $("#unsignoff-confirm-form").modal('hide');
          location.reload();
        }
      }, function(error) {
        console.log(error);
      });
    });

</script>