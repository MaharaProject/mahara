{include file="header.tpl" headertype="progresscompletion"}

{if $outcomes}
  <div class="card progresscompletion">
      <div class="card-body">
          <p id="quota_message">
              {$quotamessage|safe}
          </p>
          <div id="quotawrap" class="progress">
              <div id="quota_fill" class="progress-bar {if $completedactionspercentage < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $completedactionspercentage }{$completedactionspercentage}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$completedactionspercentage}%;">
                  <span>{$completedactionspercentage}%</span>
              </div>
          </div>
      </div>
  </div>

  {foreach $outcomes item=outcome}
  <div class="form-group collapsible-group">
      <fieldset class="first last pieform-fieldset collapsible">
          <legend>
            <button type="button" data-bs-target="#dropdown{$outcome->id}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown{$outcome->id}" class="collapsed" >
              {$outcome->short_title|safe}
              <div class="d-flex right float-end">
                {if $outcome->complete}
                  <a href="#" class="outcome-state outcome-complete " data-outcome={$outcome->id} title="{str tag='completeoutcome' section='collection' arg1=$outcome->short_title|safe}" >
                    <span class="icon icon-check-circle completed mt-1 px-4" role="presentation" ></span>
                  </a>
                {else}
                  <a href="#" class="outcome-state outcome-incomplete  secondary-link" data-outcome={$outcome->id} title="{str tag='incompleteoutcome' section='collection' arg1=$outcome->short_title|safe}" >
                    <span class="icon-circle action icon-regular mt-1 px-4" data-outcome={$outcome->id}></span>
                  </a>
                {/if}
                <span class="icon icon-chevron-down collapse-indicator "> </span>
              </div>
            </button>
          </legend>

          <div class="fieldset-body collapse" id="dropdown{$outcome->id}">

            <div class="form-group last">{$outcome->full_title|safe}</div>

            {if $outcome->outcome_type}
              <div class="form-group last">
                <label for="outcometype-{$outcome->id}">{str tag="outcometype" section="collection"}</label>
                <div id="outcometype-{$outcome->id}" class="outcome-type">
                  <span>{$outcometypes[$outcome->outcome_type]->abbreviation}</span>
                </div>
              </div>
            {/if}

            <br/>** Table goes here ** <br/><br/>

            <button class="btn btn-secondary btn-sm" >
              <span class="icon icon-plus left" role="presentation" aria-hidden="true"> </span>
              {str tag="addactivity" section="collection"}
            </button>

            {$supportform[$outcome->id]|safe}

            <div class="outcome-progress-form" id="progress{$outcome->id}">
              <div class="form-group last">
                <label class="pseudolabel" for="progress{$outcome->id}_textarea">{str tag="progress" section="collection"}</label>
                <div class="textarea-section" >
                {if $outcome->complete}
                  <div class="form-group last">
                    {$outcome->progress|safe}
                  </div>
                {else}
                  <div>
                    <textarea id="progress{$outcome->id}_textarea" class="form-control resizable" tabindex="0" cols="180" rows="3" >{$outcome->progress|safe}</textarea>
                  </div>
                  <button type="submit" id="progress{$outcome->id}_save" name="save" tabindex="0" class="btn btn-primary btn-sm outcome-progress-save">{str tag='save'}</button>
                {/if}
                </div>
              </div>

              <input type="hidden" class="hidden autofocus" id="progress{$outcome->id}_id" name="id" value="{$outcome->id}">
            </div>
          </div>
      </fieldset>
  </div>
  {/foreach}
{else}
  {str tag="nooutcomesmessage" section="collection" }
{/if}

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

<script type="application/javascript">
$(function() {

  if ({$actionsallowed}) {

    // Set complete
    $("a.outcome-incomplete").on('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $("#complete-confirm-form").modal('show');
      $("#complete-confirm-form").attr('outcomeid', $(this).attr('data-outcome'));
    });

    // click 'No' button on modals
    $("#complete-back-button").on('click', function() {
        $("#complete-confirm-form").modal('hide');
    });

    $('#complete-yes-button').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var outcomeid = $("#complete-confirm-form").attr('outcomeid');
        sendjsonrequest('{$WWWROOT}collection/setcompleteoutcome.json.php', { 'outcomeid': outcomeid }, 'POST', function (data) {
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

    // click 'No' button on modals
    $("#incomplete-back-button").on('click', function() {
        $("#incomplete-confirm-form").modal('hide');
    });

    $('#incomplete-yes-button').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var outcomeid = $("#incomplete-confirm-form").attr('outcomeid');
        sendjsonrequest('{$WWWROOT}collection/setincompleteoutcome.json.php', { 'outcomeid': outcomeid }, 'POST', function (data) {
          if (data) {
            location.reload();
          }
        }, function(error) {
          console.log(error);
        });
    });
  }

  $('form.supportform input.switchbox').on('change', function(e) {
    const id = $(e.target).parents('form').find('[name=id]').val();
    const support = $(e.target).prop('checked');
    const data = {
      'update_type': 'support',
      'outcomeid': id,
      'support': support
    };
    sendjsonrequest(config.wwwroot + 'collection/updateoutcome.json.php', data, 'POST', function(data) {
      console.log(data);
    },
    function(error) {
      console.log(error);
    })
  });

  $(".outcome-progress-save").on('click', function(e) {
    const form = $(e.target).parents('.outcome-progress-form');
    const id = $(form).find('input[name="id"]').val();
    const text = $(form).find('textarea').val();
    const data = {
      'update_type': 'progress',
      'outcomeid': id,
      'progress': text
    };
    sendjsonrequest('{$WWWROOT}collection/updateoutcome.json.php', data, 'POST', function (data) {
          if (data) {
            console.log(data);
          }
        }, function(error) {
          console.log(error);
        });

  })
});
</script>
{include file="footer.tpl"}
