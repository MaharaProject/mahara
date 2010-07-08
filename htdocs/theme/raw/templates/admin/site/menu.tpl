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

            <h1>{str tag=footermenu section=admin}</h1>
            <p>{str tag=footermenudescription section=admin}</p>
            {$footerform|safe}

{include file="footer.tpl"}
