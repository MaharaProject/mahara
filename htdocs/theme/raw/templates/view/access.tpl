{include file="header.tpl"}
{if $views}
<div class="message">
  <strong>
  {str tag=collectioneditaccess section=collection arg1=$views.count}:
  {foreach from=$views.views item=view name=cviews}
    {$view->title}{if !$.foreach.cviews.last}, {/if}
  {/foreach}
  </strong>
</div>
{/if}
{$pagedescriptionhtml|safe}
{$form|safe}
{include file="footer.tpl"}
