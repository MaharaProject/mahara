<div class="friends">
    <div class="listing blocktype">
        <div class="listrow panel-body">
        {if $groupmembers}
            {$groupmembers.tablerows|safe}
        {/if}
        </div>
        <a class="panel-footer text-small" href="{$show_all.url}">
            {$show_all.message}
            <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
</div>
