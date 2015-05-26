<div class="friends">
    <div class="listing blocktype">
        <div class="listrow panel-body">
        {if $groupmembers}
            {$groupmembers.tablerows|safe}
        {/if}
        </div>
        <a class="panel-footer" href="{$show_all.url}">
            {$show_all.message}
            <span class="fa fa-arrow-circle-right mls"></span>
        </a>
    </div>
</div>
