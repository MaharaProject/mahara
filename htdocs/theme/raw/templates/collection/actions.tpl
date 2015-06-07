{if $canedit}
    <div class="text-right btn-group btn-top-right btn-group-top {if $GROUP}pagetabs{/if} ">
        <a class="btn btn-default" href="{$WWWROOT}collection/edit.php?new=1{$urlparamsstr}">
            <span class="text-success fa fa-plus fa-lg prs"></span>
            {str section=collection tag=newcollection}</a>
        <a class="btn btn-default" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">
            <span class="text-success fa fa-files-o fa-lg prs"></span>
            {str section=collection tag=copyacollection}
        </a>
    </div>
{/if}