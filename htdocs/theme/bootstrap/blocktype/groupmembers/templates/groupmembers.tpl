<div class="friends">
{if $groupmembers}
    {$groupmembers.tablerows|safe}
{/if}
<a class="morelink panel-footer" href="{$show_all.url}">
    {$show_all.message}
    <span class="fa fa-arrow-circle-right mls pull-right"></span>
</a>
</div>
