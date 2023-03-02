{include file="header.tpl" headertype="outcomeoverview"}

{if $outcomes}

  <div class="card outcomeoverview">
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
      <fieldset class="pieform-fieldset collapsible">
      {* <fieldset class="first last pieform-fieldset collapsible"> *}
        {* Outcome header *}
        <legend>
          <button type="button" data-bs-target="#dropdown{$outcome->id}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown{$outcome->id}" class="collapsed" >
            {$outcome->short_title|safe}
            <div class="d-flex right float-end">

              {* Outcome status icon *}
              {if $actionsallowed}
                {if $outcome->complete}
                  <a href="#" class="outcome-state outcome-complete " data-outcome={$outcome->id} title="{str tag='completeoutcomeaction' section='collection' arg1=$outcome->short_title|safe}" >
                    <span class="icon icon-check-circle completed mt-1 px-4" role="presentation" ></span>
                  </a>
                {else}
                    <a href="#" class="outcome-state outcome-incomplete secondary-link" data-outcome={$outcome->id} title="{str tag='incompleteoutcomeaction' section='collection' arg1=$outcome->short_title|safe}" >
                      <span class="icon-circle action icon-regular mt-1 px-4" data-outcome={$outcome->id}></span>
                    </a>
                {/if}
              {else}
                {if $outcome->complete}
                  <a href="#" class="outcome-state" title="{str tag='completeoutcome' section='collection' arg1=$outcome->short_title|safe}" >
                    <span class="icon icon-check-circle completed mt-1 px-4 "></span>
                  </a>
                {else}
                  <a href="#" class="outcome-state" title="{str tag='incompleteoutcomedisabled' section='collection' arg1=$outcome->short_title|safe}" >
                    <span class="icon icon-circle dot mt-1 px-4 disabled "></span>
                  </a>
                {/if}
              {/if}
              <span class="icon icon-chevron-down collapse-indicator "> </span>
            </div>
          </button>
        </legend>

        {* Outcome collapsible panel *}
        <div class="fieldset-body collapse" id="dropdown{$outcome->id}">

          <div class="form-group form-group-no-border">{$outcome->full_title|safe}</div>
          {* Outcome full title *}
          {* <div class="form-group last">{$outcome->full_title|safe}</div> *}

          {* Outcome type code *}
          {if $outcome->outcome_type}
            <div class="form-group form-group-no-border" id="outcome{$outcome->id}_type_container">
              <label for="outcometype-{$outcome->id}">{str tag="outcometype" section="collection"}</label>
              <div id="outcometype-{$outcome->id}" class="outcome-type">
                <span class="badge rounded-pill text-bg-{$outcometypes[$outcome->outcome_type]->styleclass}">{$outcometypes[$outcome->outcome_type]->abbreviation}</span>
              </div>
              {contextualhelp
              plugintype='core'
              pluginname='collection'
              form="outcome$outcome->id"
              element='type'
              page='type'}
            </div>
          {/if}

          {* Outcome Activity table *}
          {if !$activities }
            <p>{str tag='noactivities' section='collection'}</p>
          {else}
          <table class="fullwidth table tablematrix progresscompletion" id="tablematrix">
              <caption class="visually-hidden">{str tag="tabledesc" section="collection"}</caption>
              <tr class="table-pager">
                  <th>{str tag="activity" section='collection'}</th>
                  <th class="userrole">{str tag="signoff" section="view"}</th>
              </tr>
              {foreach from=$activities[$outcome->id] item=activity}
              <tr data-activity="{$activity->id}">
                {include file="collection/activitytablerow.tpl" signedoff=$activity->achieved activityid=$activity->id viewid=$activity->view title=$activity->title}
              </tr>
              {/foreach}
          </table>
          {/if}

          {* Add activity button *}
          {if !$outcome->complete && $actionsallowed}
            <button id="addactivity" class="addactivity btn btn-secondary btn-sm "
            data-bs-target="{$WWWROOT}view/editlayout.php?new=1{$urlparamsstr}&group={$group}&collection={$collection}&outcome={$outcome->id}">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"> </span>
            {str tag="addactivity" section="collection"}
          </button>
          {/if}

          {* Outcome support switchbox *}
          {$supportform[$outcome->id]|safe}

          {* Outcome progress textarea *}
          <form class="outcome-progress-form" id="progress{$outcome->id}">
            <div class="form-group form-group-no-border">
              <label class="pseudolabel" for="progress{$outcome->id}_textarea">{str tag="progress" section="collection"}</label>
              <div class="textarea-section">
              {if $outcome->complete || !$actionsallowed}
                <div class="text progress-detail">
                  {$outcome->progress|safe}
                </div>
                {if $outcome->lastauthorprogress}
                  <div class="text-small postedon">
                    {strip}
                    <a href="{profile_url($outcome->lastauthorprogress)}" class="progress-author">
                    {display_name($outcome->lastauthorprogress, null, true)}
                    </a>, {str tag='ondate' section='collection' arg1=$outcome->lasteditprogress|strtotime|format_date:'strftimedatetime'}
                    {/strip}
                  </div>
                {/if}
              {else}
                <div>
                  <textarea id="progress{$outcome->id}_textarea" class="form-control resizable" tabindex="0" cols="180" rows="3" maxlength="16777216">{$outcome->progress|safe}</textarea>
                </div>
                <button type="submit" id="progress{$outcome->id}_save" name="save" tabindex="0" class="btn btn-secondary btn-sm outcome-progress-save">{str tag='save'}</button>
              {/if}
              </div>
            </div>
            <input type="hidden" class="hidden autofocus" id="progressid_{$outcome->id}_id" name="id" value="{$outcome->id}">
          </form>
        </div>
      </fieldset>
    </div>
  {/foreach}
{else}
  {str tag="nooutcomesmessage" section="collection" }
{/if}

{if $actionsallowed}
  {include file='collection/outcomesoverviewmodals.tpl'}
{/if}

<script>
    $j(".addactivity").on('click', function() {
        // redirect to the special 'activity page'
        let addurl = $j(this).attr('data-bs-target');
        let url = addurl + '&type=activity';
        window.location = url;
    })
</script>

{include file="footer.tpl"}
