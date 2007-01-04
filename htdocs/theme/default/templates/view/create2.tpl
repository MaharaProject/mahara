{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>
{include file="columnleftstart.tpl"}
    		<h2>{str tag="createviewstep2" section="view"}</h2>
				<div id="createview2">
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
        <input type="hidden" name="template" id="template" value="">
        <table id="templates">
        </table>
    </form>

    <button type="button" onclick="document.location='create1.php?createid={$createid}';">{str tag="back" section="view"}</button>
    <button type="button" onclick="document.location='./';">{str tag="cancel"}</button>

				</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}

