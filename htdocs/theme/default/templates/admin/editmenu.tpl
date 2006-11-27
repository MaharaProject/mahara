{include file="header.tpl"}

<h2>{str section=admin tag=editmenus}</h2>

<div> 
{str tag=edit}:
<select id="menuselect" name="menuselect">
{foreach from=$MENUS item=menu}
    <option value={$menu.value}>{$menu.name}</option>
{/foreach}
</select>
</div>

<div id="menuitemlist"></div>

{include file="footer.tpl"}
