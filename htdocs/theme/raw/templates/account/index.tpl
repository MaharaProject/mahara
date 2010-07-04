{include file="header.tpl"}
{if $candeleteself}<div class="rbuttons"><a href="{$WWWROOT}account/delete.php">{str tag=deleteaccount section=account}</a></div>{/if}
			{$form|safe}
{include file="footer.tpl"}
