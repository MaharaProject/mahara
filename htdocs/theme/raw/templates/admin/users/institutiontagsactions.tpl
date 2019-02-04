{if $canedit && !$new}
    <div class="btn-group btn-top-right btn-group-top {if $GROUP}pagetabs{/if}">
        <a class="btn btn-secondary" href="{$WWWROOT}admin/users/institutiontags.php?new=1&institution={$institution}">
            <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
            {str tag=createtag}</a>
    </div>
{/if}
