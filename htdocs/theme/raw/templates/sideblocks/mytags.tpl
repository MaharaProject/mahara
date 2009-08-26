{if $data.tags}
    <h3>{str tag="mytags"}</h3>
    <div class="sidebar-content mytags">
  {foreach from=$data.tags item=tag}
      <a class="tag" style="font-size: {$tag->size}em;" href="{$WWWROOT}tags.php?tag={$tag->tag|urlencode}">{$tag->tag|escape}</a>
  {/foreach}
    </div>
{/if}