{include file="header.tpl"}

<h1>{$viewtitle}</h1>
{include file="view/editviewtabs.tpl" selected='title'}
<div class="subpage rel">
			{$editview|safe}
</div>

{include file="footer.tpl"}
