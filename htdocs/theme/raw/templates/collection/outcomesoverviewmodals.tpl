{* complete outcome modal form *}
<div tabindex="0" class="modal fade" id="complete-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=markcomplete section=collection}
                </h1>
            </div>
            <div class="modal-body">
                <div class="btn-group">
                    <button id="complete-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="complete-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>

{* incomplete outcome modal form *}
<div tabindex="0" class="modal fade" id="incomplete-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=markincomplete section=collection}
                </h1>
            </div>
            <div class="modal-body">
                <div class="btn-group">
                    <button id="incomplete-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="incomplete-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>

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
$(function() {
// Outcome icons

    // Set complete
    $("a.outcome-incomplete").on('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $("#complete-confirm-form").modal('show');
      $("#complete-confirm-form").attr('outcomeid', $(this).attr('data-outcome'));
    });

    // click 'No' button on modals to cancel 'set complete' action
    $("#complete-back-button").on('click', function() {
        $("#complete-confirm-form").modal('hide');
    });

    // click 'Yes' button on modals to 'set complete' action
    $('#complete-yes-button').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var outcomeid = $("#complete-confirm-form").attr('outcomeid');
        sendjsonrequest('{$WWWROOT}collection/updateoutcome.json.php', { 'update_type': 'markcomplete', 'outcomeid': outcomeid, 'collectionid': {$collection} }, 'POST', function (data) {
          if (data) {
            location.reload();
          }
        }, function(error) {
          console.log(error);
        });
    });

    // Set incomplete
    $("a.outcome-complete").on('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $("#incomplete-confirm-form").modal('show');
      $("#incomplete-confirm-form").attr('outcomeid', $(this).attr('data-outcome'));
    });

     // click 'No' button on modals to cancel 'set incomplete' action
    $("#incomplete-back-button").on('click', function() {
        $("#incomplete-confirm-form").modal('hide');
    });

    // click 'Yes' button on modals to 'set incomplete' action
    $('#incomplete-yes-button').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var outcomeid = $("#incomplete-confirm-form").attr('outcomeid');
// what is the difference between $collection and $collectionid?
        sendjsonrequest('{$WWWROOT}collection/updateoutcome.json.php', { 'update_type': 'markincomplete', 'outcomeid': outcomeid, 'collectionid': {$collection} }, 'POST', function (data) {
          if (data) {
            location.reload();
          }
        }, function(error) {
          console.log(error);
        });
    });

  // Activity icons
    function complete_activity_click_event(link) {
      $(link).on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $("#unsignoff-confirm-form").modal('show');
        $("#unsignoff-confirm-form").attr('activity', $(this).attr('data-activity'));
      });
    }

    function incomplete_activity_click_event(link) {
      $(link).on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $("#signoff-confirm-form").modal('show');
        $("#signoff-confirm-form").attr('activity', $(this).attr('data-activity'));
      });
    }

    // Signoff
    // link icons with signoff modal
    $("a.activity-incomplete").map((_, link)=> incomplete_activity_click_event(link));

    // click 'No' button on modals - cancel signoff
    $("#signoff-back-button").on('click', function() {
      $("#signoff-confirm-form").modal('hide');
    });

    // click 'Yes' button on modals - mark activity as signed off
    $('#signoff-yes-button').on('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      var activityid = $("#signoff-confirm-form").attr('activity');
      sendjsonrequest('{$WWWROOT}collection/updateactivity.json.php', { 'update_type': 'signoff', 'activityid': activityid, 'collectionid': {$collection} }, 'POST', function (data) {
        if (data) {
          $("#signoff-confirm-form").modal('hide');
          $('tr[data-activity=' + activityid + ']').html(data.html);
          // link modal to icon
          $('tr[data-activity=' + activityid + '] a.activity-complete').map((_, link)=> complete_activity_click_event(link));
        }
      }, function(error) {
        console.log(error);
      });
    });

    // Undo signoff
    // link icons with undo signoff modal
    $("a.activity-complete").map((_, link)=> complete_activity_click_event(link));

    // click 'No' button on modals - keep signed off value
    $("#unsignoff-back-button").on('click', function() {
      $("#unsignoff-confirm-form").modal('hide');
    });

    // click 'Yes' button on modals - remove signoff state
    $('#unsignoff-yes-button').on('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      var activityid = $("#unsignoff-confirm-form").attr('activity');
      sendjsonrequest('{$WWWROOT}collection/updateactivity.json.php', { 'update_type': 'unsignoff', 'activityid': activityid, 'collectionid': {$collection} }, 'POST', function (data) {
        if (data) {
          $("#unsignoff-confirm-form").modal('hide');
          $('tr[data-activity=' + activityid + ']').html(data.html);
          // link modal to icon
          $('tr[data-activity=' + activityid + '] a.activity-incomplete').map((_, link)=> incomplete_activity_click_event(link));
        }
      }, function(error) {
        console.log(error);
      });
    });
  });

  // Save support switchbox value
  $('form.supportform input.switchbox').on('change', function(e) {
    const id = $(e.target).parents('form').find('[name=id]').val();
    const support = $(e.target).prop('checked');
    const data = {
      'update_type': 'support',
      'outcomeid': id,
      'collectionid': {$collection},
      'support': support
    };
    sendjsonrequest(config.wwwroot + 'collection/updateoutcome.json.php', data, 'POST', function(data) {
      console.log(data);
    },
    function(error) {
      console.log(error);
    })
  });

  // Save progress text
  $(".outcome-progress-save").on('click', function(e) {
    e.preventDefault();
    const form = $(e.target).parents('.outcome-progress-form');
    const id = $(form).find('input[name="id"]').val();
    const text = $(form).find('textarea').val();
    if (text.length <= 255) {
      const data = {
        'update_type': 'progress',
        'outcomeid': id,
        'collectionid': {$collection},
        'progress': text
      };
      sendjsonrequest('{$WWWROOT}collection/updateoutcome.json.php', data, 'POST', function (data) {
            const formid = $(form).attr('id');
            formchangemanager.add(formid);
          }, function(error) {
            console.log(error);
      });
    }
  })

  // Track if the progress text has been edited
  $('form.outcome-progress-form').map((i, form) => {
    const formid = $(form).attr('id');
    formchangemanager.add(formid);
  })

</script>
