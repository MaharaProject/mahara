{auto_escape off}
{include file="header.tpl"}

            <p>{str tag=linksandresourcesmenupagedescription section=admin args=$descriptionstrargs}</p>
			
			<div id="editmenus">
			
			<label>{str tag=edit}:</label>
			<select id="menuselect" name="menuselect">
			{foreach from=$MENUS item=menu}
				<option value={$menu.value}>{$menu.name}</option>
			{/foreach}
			</select>
            {contextualhelp plugintype='core' pluginname='admin' section='adminmenuselect'}
			
				<div id="menuitemlist"></div>
			
			</div>

{include file="footer.tpl"}
{/auto_escape}
