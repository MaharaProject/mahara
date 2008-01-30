{include file="header.tpl"}

{include file="columnfullstart.tpl"}
	
			<h2>{str tag=linksandresourcesmenu section=admin}</h2>

            <p>{str tag=linksandresourcesmenupagedescription section=admin args=$descriptionstrargs}</p>
			
			<div id="editmenus">
			
			{str tag=edit}:
			<select id="menuselect" name="menuselect">
			{foreach from=$MENUS item=menu}
				<option value={$menu.value}>{$menu.name}</option>
			{/foreach}
			</select>
            {contextualhelp plugintype='core' pluginname='admin' section='adminmenuselect'}
			
				<div id="menuitemlist"></div>
			
			</div>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
