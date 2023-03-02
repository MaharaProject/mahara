{*
args
  activityid
  title
  viewid
  signedoff
*}

{if $actionsallowed}
  {* Admins and tutors *}
  {if $signedoff}
    <td class="progresstitle">
      <div>
        <a href="{$WWWROOT}view/view.php?id={$viewid}">{$title}</a>
      </div>
    </td>
    <td>
      <a
        href="#" class="activity-state activity-complete"
        data-activity={$activityid}
        title="{str tag='completeactivityaction' section='collection' arg1=$title|safe}" >
        <span class="icon icon-check-circle completed mt-1 px-4" role="presentation" ></span>
      </a>
    </td>
  {else}
    <td class="progresstitle">
      <div>
        <a href="{$WWWROOT}view/blocks.php?id={$viewid}&{$querystring}">{$title}</a>
      </div>
    </td>
    <td>
      <a
        href="#" class="activity-state activity-incomplete"
        data-activity={$activityid}
        title="{str tag='incompleteactivityaction' section='collection' arg1=$title|safe}" >
        <span class="icon icon-circle action icon-regular mt-1 px-4" data-activity={$activityid}></span>
      </a>
    </td>
  {/if}
{else}
  {* Members *}
  {if $signedoff}
    <td class="progresstitle">
      <div>
        <a href="{$WWWROOT}view/view.php?id={$viewid}">{$title}</a>
      </div>
    </td>
    <td>
      <span
          class="icon icon-check-circle completed mt-1 px-4" role="presentation"
          title="{str tag='completeactivity' section='collection' arg1=$title|safe}" >
      </span>
      </a>
    </td>
  {else}
    <td class="progresstitle">
      <div>
        <a href="{$WWWROOT}view/blocks.php?id={$viewid}&{$querystring}">{$title}</a>
      </div>
    </td>
    <td>
      <span
        class="icon icon-circle dot disabled mt-1 px-4"
        title="{str tag='incompleteactivitydisabled' section='collection' arg1=$title|safe}">
      </span>
    </td>
  {/if}
{/if}