{include file="header.tpl"}

<h2>{str section=admin tag=editmenus}</h2>

<div> 
{$EDIT}
<select id="menuselect" name="menuselect">
{foreach from=$MENUS item=menu}
    <option value={$menu.value}>{$menu.name}</option>
{/foreach}
</select>
</div>

<table id="menuitemlist" class="menueditor"><tbody><tr><td></td></tr></tbody></table>

{include file="footer.tpl"}
