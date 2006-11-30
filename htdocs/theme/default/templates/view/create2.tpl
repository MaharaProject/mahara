{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str tag="createviewstep2"}</h2>
{literal}
    <select onchange="templates.doupdate({'offset': 0, 'category': this.options[this.selectedIndex].value });">
{/literal}
        <option value="">{str tag="category.all" section="view"}</option>
{foreach from=$categories item=category}
        <option value="{$category|escape}">{str tag="category.$category" section="view"}</option>
{/foreach}
    </select>
    <table id="templates">
    </table>

    <button type="button" onclick="document.location='create1.php?createid={$createid}';">{str tag=Back}</button>
    <button type="button" onclick="document.location='./';">{str tag=Cancel}</button>
</div>

{include file="footer.tpl"}

