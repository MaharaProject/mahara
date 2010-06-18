{auto_escape off}
<div class="friends">
{if $friends}
    <table id="userfriendstable" class="center fullwidth">
      <tbody>
      {$friends.tablerows}
      </tbody>
    </table>
{/if}
<div class="message"><a href="{$show_all.url}">{$show_all.message|escape}</div>
</div>
{/auto_escape}
