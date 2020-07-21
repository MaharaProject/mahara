{include file="header.tpl"}
{if $candeleteself && !$deletionsent}
<div class="btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}account/delete.php" class="btn btn-secondary delete">
        <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=deleteaccount1}</span>
    </a>
</div>
{/if}
{if $deletionsent}
<div class="btn-top-right btn-group btn-group-top">
    <a href="{$WWWROOT}account/cancelrequest.php" class="btn btn-secondary">
        <span class="icon icon-times left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=cancelrequest section=account}</span>
    </a>
    <a href="{$WWWROOT}account/resendnotification.php" class="btn btn-secondary">
        <span class="icon icon-paper-plane left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=resenddeletionnotification section=account}</span>
    </a>
</div>
<div class="deletion-message">{str tag=pendingdeletionsince section=account arg1=$requestdate}</div>
{/if}
<div class="view-container">
    {$form|safe}
</div>
{include file="footer.tpl"}
