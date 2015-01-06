{include file="header.tpl"}
{if $candeleteself}<div class="message deletemessage"><div class="deletebuttonwrap"><a href="{$WWWROOT}account/delete.php" class="btn delete">{str tag=deleteaccount section=account}</a></div></div>{/if}
			{$form|safe}
{include file="footer.tpl"}
