{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<h3>{$NAME}</h3>
			<table><tbody>
		{foreach from=$USERFIELDS key=key item=item}
			<tr><td>{str section=mahara tag=$key}</td><td>{$item}</td></tr>
		{/foreach}
			</tbody></table>
		
		{if $PROFILE}
			<h4>{str section=artefact.internal tag=profile}</h4>
			<table><tbody>
		{foreach from=$PROFILE key=key item=item name=profile}
			<tr><td>{str section=artefact.internal tag=$key}</td><td>{$item}</td></tr>
		{/foreach}
			</tbody></table>
		{/if}
		
		{if $VIEWS}
			<h4>{str section=mahara tag=views}</h4>
			<ul>
		{foreach from=$VIEWS key=key item=item name=view}
			<li><a href="{$WWWROOT}view/view.php?view={$key}">{$item}</a></li>
		{/foreach}
			</ul>
		{/if}
                <br>
		{$INVITEFORM}
		{$ADDFORM}
		{$FRIENDFORM}
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
