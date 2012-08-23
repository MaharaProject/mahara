{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td class="inboxicon">
  {if $item->read && $item->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/read' $item->type '.gif')}" alt="{$item->strtype} - {str tag='read' section='activity'}" />
  {elseif $item->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/' $item->type '.gif')}" alt="{$item->strtype}" class="unreadmessage" />
  {else}
        <img src="{theme_url filename=cat('images/' $item->type '.gif')}" alt="{$item->strtype}" />
  {/if}
    </td>
    <td>
  {if $item->message}
      <a href="" onclick="showHideMessage({$item->id}); return false;">{$item->subject}</a>
      <div id="message-{$item->id}" class="hidden">{$item->message|safe}
      {if $item->url}<br><a href="{$WWWROOT}{$item->url}">{if $item->urltext}{$item->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
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
      <img src="{theme_url filename='images/star.png'}" alt="{str tag=read section=activity}">
  {else}
      <input type="checkbox" class="tocheckread" name="unread-{$item->id}">
  {/if}
    </td>
    <td class="center"><input type="checkbox" class="tocheckdel" name="delete-{$item->id}"></td>
  </tr>
{/foreach}
