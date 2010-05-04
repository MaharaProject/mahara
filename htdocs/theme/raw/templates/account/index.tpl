{auto_escape off}
{include file="header.tpl"}
{if $candeleteself}<div class="rbuttons"><a href="{$WWWROOT}account/delete.php">{str tag=deleteaccount section=account}</a></div>{/if}
			{$form}
{include file="footer.tpl"}
{/auto_escape}
