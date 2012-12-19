{include file="header.tpl"}
{if $candeleteself}<div class="message"><a href="{$WWWROOT}account/delete.php" class="btn delete">{str tag=deleteaccount section=account}</a></div>{/if}
			{$form|safe}
{include file="footer.tpl"}
