<div class="friends">
{if $groupmembers}
      {$groupmembers.tablerows|safe}
{/if}
<div class="cl morelinkwrap"><a class="morelink" href="{$show_all.url}">{$show_all.message} &raquo;</a></div>
</div>
