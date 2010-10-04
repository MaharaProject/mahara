{auto_escape off}
<div class="friends">
{if $groupmembers}
    <table id="userfriendstable" class="center fullwidth">
      <tbody>
      {$groupmembers.tablerows}
      </tbody>
    </table>
{/if}
<a class="morelink" href="{$show_all.url}">{$show_all.message|escape} &raquo;</a>
</div>
{/auto_escape}
