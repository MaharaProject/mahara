{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td class="inboxicon" onclick="toggleMessageDisplay('{$item->table}','{$item->id}');">
      {if $item->read && $item->type == 'usermessage'}
        <img src="{theme_image_url filename=cat('read' $item->type)}" alt="{$item->strtype} - {str tag='read' section='activity'}" />
      {elseif $item->type == 'usermessage'}
        <img src="{theme_image_url filename=$item->type}" alt="{$item->strtype}" class="unreadmessage" />
      {else}
        <img src="{theme_image_url filename=$item->type}" alt="{$item->strtype}" />
      {/if}
    </td>
    <td onclick="toggleMessageDisplay('{$item->table}','{$item->id}');">{$item->fromusr|display_name|truncate:$maxnamestrlength}</td>
    <td onclick="toggleMessageDisplay('{$item->table}','{$item->id}');">
      {if $item->message}
        <a href="" onclick="return false;">
          {$item->subject|truncate:60}
          <span class="accessible-hidden">{str tag='clickformore' section='artefact.multirecipientnotification'}</span>
        </a>
        <div id="message-{$item->table}-{$item->id}" class="hidden messagedisplaylong">
          {$item->message|safe}
          {if $item->url}
            <br />
            <a href="{$WWWROOT}{$item->url}">
              {if $item->urltext}
                {$item->urltext} {str tag='linkindicator' section="artefact.multirecipientnotification"}
              {else}
                {str tag="more..."}
              {/if}
            </a>
          {/if}
        </div>
      {elseif $item->url}
        <a href="{$WWWROOT}{$item->url}">{$item->subject|truncate:60}</a>
      {else}
        {$item->subject|truncate:60}
      {/if}
    </td>
    <td class="userlist">
      {if count($item->tousr) > 1}
        <span id="short{$item->id}" class="messagedisplayshort">
          <a onclick="return false;" href="javascript:void(0)">
            <img class="togglebtn" src="{theme_image_url filename='expand'}" onclick="toggleMessageDisplay('{$item->table}','{$item->id}');"/>
            {*<span class="accessible-hidden">{str tag='clickformore' section='artefact.multirecipientnotification'}</span>*}
          </a>
          {assign var="tousr" value=$item->tousr[0]}
          {if $tousr['link']}<a href="{$tousr['link']}">{/if}
            {$tousr['display']|truncate:$maxnamestrlength}
          {if $tousr['link']}</a>{/if}
        </span>
        <span class="hidden messagedisplaylong" id="long{$item->id}">
          <a onclick="return false;" href="javascript:void(0)">
            <img class="togglebtn" src="{theme_image_url filename='expanded'}" onclick="toggleMessageDisplay('{$item->table}','{$item->id}');"/>
          </a>
          <span class="recipientlist">
            {foreach from=$item->tousr item=tousr key=break}
              {if ($tousr['link'])}<a href="{$tousr['link']}">{/if}
                  {$tousr['display']|truncate:$maxnamestrlength}
              {if ($tousr['link'])}</a>{/if}
              <br />
            {/foreach}
          </span>
        </span>
      {else}
        {assign var="tousr" value=$item->tousr[0]}
        {if $tousr['link']}<a href="{$tousr['link']}">{/if}
          {$tousr['display']|truncate:$maxnamestrlength}
        {if $tousr['link']}</a>{/if}
      {/if}
    </td>
    <td onclick="toggleMessageDisplay('{$item->table}','{$item->id}');">{$item->date}</td>
    <td class="center" onclick="toggleMessageDisplay('{$item->table}','{$item->id}');">
      {if ($item->canreply || $item->canreplyall)}
        <span class="hidden messagedisplaylong">
          {if $item->canreplyall}
            <a title="{str tag=replyall section=artefact.multirecipientnotification}" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php?{if $item->startnewthread}id={$item->usr}{else}replyto={$item->id}{/if}&returnto=outbox">
                <img src="{theme_image_url filename='replyall'}" alt="{str tag=replyall section=artefact.multirecipientnotification}">
            </a>
          {elseif $item->canreply}
            <a title="{str tag=reply section=artefact.multirecipientnotification}" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php?{if $item->startnewthread}id={$item->usr}{else}replyto={$item->id}{/if}&returnto=outbox">
                <img src="{theme_image_url filename='reply'}" alt="{str tag=reply section=artefact.multirecipientnotification}">
            </a>
          {/if}
        </span>
        <span class="messagedisplayshort">
            <a href="javascript:void(0)" onclick="return false;">
                {str tag='replybuttonplaceholder' section='artefact.multirecipientnotification'}
            </a>
        </span>
      {/if}
    </td>
    <td class="center">
      {if $item->table === 'artefact_multirecipient_notification'}
        <label class="accessible-hidden" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
        <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
      {/if}
    </td>
  </tr>
{/foreach}
