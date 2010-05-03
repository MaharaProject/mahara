{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>
        <img src="{theme_url filename=cat('images/' $item->type '.gif')}" alt="{$item->strtype|escape}" />
    </td>
    <td>
  {if $item->message}
      <a href="" onclick="showHideMessage({$item->id}); return false;">{$item->subject|escape}</a>
      <div id="message-{$item->id}" class="hidden">{$item->message|clean_html}
      {if $item->url}<br><a href="{$item->url|escape}" class="s">{str tag="more..."}</a>{/if}
      </div>
  {elseif $item->url}
      <a href="{$item->url|escape}">{$item->subject|escape}</a>
  {else}
      {$item->subject|escape}
  {/if}
    </td>
    <td>{$item->date|escape}</td>
    <td class="center">
  {if $item->read}
      <img src="{theme_url filename='images/star.png'}" alt="{str tag=read section=activity}">
  {else}
      <input type="checkbox" class="tocheckread" name="unread-{$item->id|escape}">
  {/if}
    </td>
    <td class="center"><input type="checkbox" class="tocheckdel" name="delete-{$item->id|escape}"></td>
  </tr>
{/foreach}
