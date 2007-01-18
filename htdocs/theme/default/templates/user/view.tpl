{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<div id="userview">
			<h3>{$NAME}</h3>
			<table id="userview_profile"><tbody>
		{foreach from=$USERFIELDS key=key item=item}
			<tr><th>{str section=artefact.internal tag=$key}</th><td>{$item}</td></tr>
		{/foreach}
			</tbody></table>
		{if $VIEWS}
			<table id="userview_views"><thead><tr><th>
			{str section=mahara tag=views}
			</td></tr></thead>
			<tbody><tr><td><ul>
		{foreach from=$VIEWS key=key item=item name=view}
			<li><a href="{$WWWROOT}view/view.php?view={$key}">{$item}</a></li>
		{/foreach}
			</ul></td></tr></tbody></table>
		{/if}
                <br>
		{$INVITEFORM}
		{$ADDFORM}
		{$FRIENDFORM}
		</div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
