{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
	
    		<h2>{str tag="createviewstep2"}</h2>
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

    <button type="button" onclick="document.location='create1.php?createid={$createid}';">{str tag=Back}</button>
    <button type="button" onclick="document.location='./';">{str tag=Cancel}</button>

				</div>
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}

