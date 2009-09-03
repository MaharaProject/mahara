  {foreach from=$data item=result}
    <tr class="{cycle name=rows values=r1,r0}">
      <td style="width:2em;">{if $result->icon}<img src="{$result->icon}" alt="{$result->typestr}"> {/if}</td>
      <td>
        <div><strong><a href="{$result->url}">{$result->title|escape}</a></strong></div>
        <div>{$result->description|str_shorten_html:100}</div>
        {if !empty($result->tags)}
        <div>{str tag=tags}:
          {foreach from=$result->tags item=tag name=ftags}
          <a class="tag" href="{$WWWROOT}tags.php?tag={$tag|urlencode}">{$tag|escape}</a>{if !$smarty.foreach.ftags.last},{/if}
          {/foreach}
        </div>
        {/if}
      </td>
      <td class="right s">{$result->typestr}<div class="ctime">{$result->ctime}</div></td>
    </tr>
  {/foreach}
