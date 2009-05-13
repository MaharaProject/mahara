{include file="header.tpl"}

<div id="column-right">
</div>

{include file="columnleftstart.tpl"}
			<h2>{str section=admin tag=editmenus}</h2>
			
			{str tag=edit}:
			<select id="menuselect" name="menuselect">
			{foreach from=$MENUS item=menu}
				<option value={$menu.value}>{$menu.name}</option>
			{/foreach}
			</select>
			
				<div id="menuitemlist"></div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
