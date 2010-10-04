{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>
        <img src="{theme_url filename=cat('images/' $item->type '.gif')}" alt="{$item->strtype}" />
    </td>
    <td>
  {if $item->message}
      <a href="" onclick="showHideMessage({$item->id}); return false;">{$item->subject}</a>
      <div id="message-{$item->id}" class="hidden">{$item->message|safe}
      {if $item->url}<br><a href="{$item->url}">{if $item->urltext}{$item->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
      </div>
  {elseif $item->url}
      <a href="{$item->url}">{$item->subject}</a>
  {else}
      {$item->subject}
  {/if}
    </td>
    <td>{$item->date}</td>
    <td class="center">
  {if $item->read}
      <img src="{theme_url filename='images/star.png'}" alt="{str tag=read section=activity}">
  {else}
      <input type="checkbox" class="tocheckread" name="unread-{$item->id}">
  {/if}
    </td>
    <td class="center"><input type="checkbox" class="tocheckdel" name="delete-{$item->id}"></td>
  </tr>
{/foreach}
