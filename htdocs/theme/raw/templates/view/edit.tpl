{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='title' new=$new}
<div class="subpage rel">
			{$editview|safe}
</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
