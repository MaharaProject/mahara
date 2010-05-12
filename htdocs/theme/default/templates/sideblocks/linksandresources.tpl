<h3>{str tag="linksandresources"}</h3>

{if $data}
    <ul>
{foreach from=$data item=item}
      <li><strong><a href="{$item.link|escape}">{$item.name|escape}</a></strong></li>
{/foreach}
    </ul>
{/if}

