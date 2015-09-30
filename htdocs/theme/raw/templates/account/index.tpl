{include file="header.tpl"}
{if $candeleteself}
<div class="btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}account/delete.php" class="btn btn-default  delete">
        <span class="icon icon-trash icon-lg text-danger prs"></span>
        <span>{str tag=deleteaccount section=account}</span>
    </a>
</div>
{/if}
<div class="ptxl">
    {$form|safe}
</div>
{include file="footer.tpl"}