  {foreach from=$data item=result}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td class="center" style="width:25px;">{if $result->icon}<img src="{$result->icon}" alt="{$result->typestr}"> {/if}</td>
      <td>
        <div><strong><a href="{$result->url}">{$result->title}</a></strong></div>
        <div>{$result->description|str_shorten_html:100|safe}</div>
        {if $result->tags}
        <div>{str tag=tags}: {list_tags tags=$result->tags owner=1}</div>
        {/if}
      </td>
      <td class="right s"><div class="ctime">{$result->ctime}</div>{$result->typestr}</td>
    </tr>
  {/foreach}

