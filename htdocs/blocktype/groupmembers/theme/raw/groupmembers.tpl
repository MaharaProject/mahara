{auto_escape off}
<div class="friends">
{if $groupmembers}
    <table id="userfriendstable" class="center fullwidth">
      <tbody>
      {$groupmembers.tablerows}
      </tbody>
    </table>
{/if}
<div class="message"><a href="{$show_all.url}">{$show_all.message|escape}</div>
</div>
{/auto_escape}
