{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td class="icon-container">
  {if $item->read}
        <img src="{theme_image_url filename=$item->type}" alt="{$item->strtype}" />
  {else}
        <img src="{theme_image_url filename=$item->type}" alt="{$item->strtype}" class="unreadmessage" />
  {/if}
    </td>
    <td>
  {if $item->message}
      <a href="" onclick="showHideMessage({$item->id}); return false;" class="inbox-showmessage{if !$item->read} unread{/if}">{if !$item->read}<span class="accessible-hidden">{str tag=unread section=activity}: </span>{/if}{$item->subject} <span class="accessible-hidden">{str tag=clickformore}</span></a>
      <div id="message-{$item->id}" class="hidden inbox-message">{$item->message|safe}
      {if $item->url}<br>
          <a href="{$WWWROOT}{$item->url}">{if $item->urltext}{str tag=goto arg1=$item->urltext}{else}{str tag=gotomore}{/if}</a>
      {/if}
      </div>
  {elseif $item->url}
      <a href="{$WWWROOT}{$item->url}">{$item->subject}</a>
  {else}
      {$item->subject}
  {/if}
    </td>
    <td>{$item->date}</td>
    <td class="center">
  {if $item->read}
      <img src="{theme_image_url filename='star'}" alt="{str tag=read section=activity}">
  {else}
      <label class="accessible-hidden" for="unread-{$item->id}">{str tag=markasread section=activity}</label>
      <input id="unread-{$item->id}" type="checkbox" class="tocheckread" name="unread-{$item->id}">
  {/if}
    </td>
    <td class="center"><label class="accessible-hidden" for="delete-{$item->id}">{str tag=delete section=mahara}</label>
    <input id="delete-{$item->id}" type="checkbox" class="tocheckdel" name="delete-{$item->id}"></td>
  </tr>
{/foreach}
