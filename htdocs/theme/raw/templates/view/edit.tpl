{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
{/if}

{include file="view/editviewtabs.tpl" selected='title' new=$new issiteview=$issiteview}
<div class="subpage">
  {$editview|safe}
</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
