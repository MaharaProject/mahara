<div class="friends">
    <div class="listing blocktype">
        <div class="listrow card-body">
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
