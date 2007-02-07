{include file="header.tpl"}

{include file="columnfullstart.tpl"}
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

    <button type="button" onclick="document.location='create1.php?createid={$createid}';" id="createview2_back">{str tag="back" section="view"}</button>
    <button type="button" onclick="document.location='./';" id="createview2_cancel">{str tag="cancel"}</button>

				</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

