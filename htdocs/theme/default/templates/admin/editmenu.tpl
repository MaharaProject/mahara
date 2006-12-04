{include file="header.tpl"}


<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>{str section=admin tag=editmenus}</h2>

{str tag=edit}:
<select id="menuselect" name="menuselect">
{foreach from=$MENUS item=menu}
    <option value={$menu.value}>{$menu.name}</option>
{/foreach}
</select>

<div id="menuitemlist"></div>

	</div>
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
