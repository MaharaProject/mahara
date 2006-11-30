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
    <form name="template_selection" method="post" action="" id="template_selection">
        <input type="hidden" name="createid" value="{$createid}">
        <table id="templates">
        </table>
        <button name="action" value="back">{str tag=Back}</button>
        <button name="action" value="cancel">{str tag=Cancel}</button>
    </form>

</div>

{include file="footer.tpl"}

