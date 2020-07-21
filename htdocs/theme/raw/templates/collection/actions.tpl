{if $canedit}
    <div class="btn-group btn-top-right btn-group-top {if $GROUP}pagetabs{/if}">
        <a class="btn btn-secondary" href="{$WWWROOT}collection/edit.php?new=1{$urlparamsstr}">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str section=collection tag=newcollection}</a>
        <a class="btn btn-secondary" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">
            <span class="icon icon-regular icon-copy left" role="presentation" aria-hidden="true"></span>
            {str section=collection tag=copyacollection}
        </a>
    </div>
{/if}
