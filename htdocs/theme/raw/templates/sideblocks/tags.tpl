<div class="sidebar-header"><h3><a href="{$WWWROOT}tags.php">{str tag="tags"}</a></h3></div>
<div class="sidebar-content tagblock">
{if $sbdata.tags}
  {foreach from=$sbdata.tags item=tag}
  <a class="tag"{if $tag->size} style="font-size: {$tag->size}em;"{/if} href="{$WWWROOT}tags.php?tag={$tag->tag|urlencode|safe}" title="{str tag=numitems arg1=$tag->count}">{$tag->tag|str_shorten_text:20}</a>
  {/foreach}
{else}
  {str tag=youhavenottaggedanythingyet}
{/if}
</div>
