    <h3>{str tag="mytags"}</h3>
    <div class="sidebar-content">
{if $data.tags}
    <ul>
  {foreach from=$data.tags item=tag}
      <li><a href="{$WWWROOT}tags.php?tag={$tag->tag|urlencode}">{$tag->tag|escape}</a> ({$tag->count})</li>
  {/foreach}
    </ul>
{/if}
    </div>