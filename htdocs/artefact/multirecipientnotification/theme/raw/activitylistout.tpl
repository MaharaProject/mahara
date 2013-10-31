{foreach from=$data item=item}
  <script language="JavaScript" type="text/javascript" src="js/toggle_recipient.js"> </script>
  <tr class="{cycle values='r0,r1'}">
    <td class="inboxicon">
      {if $item->read && $item->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/read' $item->type '.png')}" alt="{$item->strtype} - {str tag='read' section='activity'}" />
      {elseif $item->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/' $item->type '.png')}" alt="{$item->strtype}" class="unreadmessage" />
      {else}
        <img src="{theme_url filename=cat('images/' $item->type '.png')}" alt="{$item->strtype}" />
      {/if}
    </td>
    <td>{$item->fromusr|display_name|truncate:$maxnamestrlength}</td>
    <td>
      {if $item->message}
        <a href="" onclick="showHideMessage({$item->id}, '{$item->table}'); return false;">
          {$item->subject|truncate:60}
          <span class="accessible-hidden">{str tag='clickformore' section='artefact.multirecipientnotification'}</span>
        </a>
        <div id="message-{$item->table}-{$item->id}" class="hidden">
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
        <span id="short{$item->id}">
          <a onclick="return toggleMe('long{$item->id}', 'short{$item->id}');" href="javascript:void(0)">
            <img class="togglebtn" src="{theme_url filename='images/expand.png'}" />
            {*<span class="accessible-hidden">{str tag='clickformore' section='artefact.multirecipientnotification'}</span>*}
          </a>
          {assign var="tousr" value=$item->tousr[0]}
          {if $tousr['link']}<a href="{$tousr['link']}">{/if}
            {$tousr['display']|truncate:$maxnamestrlength}
          {if $tousr['link']}</a>{/if}
        </span>
        <span style="display:none;" id="long{$item->id}">
          <a onclick="return toggleMe('short{$item->id}', 'long{$item->id}');" href="javascript:void(0)">
            <img class="togglebtn" src="{theme_url filename='images/expanded.png'}" />
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
    <td>{$item->date}</td>
    <td class="center">
      <label class="accessible-hidden" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
      <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
    </td>
  </tr>
{/foreach}