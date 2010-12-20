{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  <h1>{$viewtitle}</h1>
  {include file="header.tpl"}
{/if}

{include file="view/editviewtabs.tpl" selected='title'}
<div class="subpage rel">
			{$editview|safe}
</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
