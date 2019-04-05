<div class="friends card-body">
    <div class="listing blocktype">
        <div class="listrow">
        {if $groupmembers}
            {$groupmembers.tablerows|safe}
        {/if}
        </div>
        <a class="card-footer text-small" href="{$show_all.url}">
            {$show_all.message}
            <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
</div>
