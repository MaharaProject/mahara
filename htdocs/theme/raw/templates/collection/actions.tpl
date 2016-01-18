{if $canedit}
    <div class="btn-group btn-top-right btn-group-top {if $GROUP}pagetabs{/if}">
        <a class="btn btn-default" href="{$WWWROOT}collection/edit.php?new=1{$urlparamsstr}">
            <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
            {str section=collection tag=newcollection}</a>
        <a class="btn btn-default" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">
            <span class="icon icon-files-o icon-lg left" role="presentation" aria-hidden="true"></span>
            {str section=collection tag=copyacollection}
        </a>
    </div>
{/if}
