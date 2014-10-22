{foreach from=$data item=item}
  <script language="JavaScript" type="text/javascript" src="js/toggle_recipient.js"> </script>
  <tr class="{cycle values='r0,r1'}{if !$item->read} unread{/if}">
    <td class="inboxicon">
      {if $item->read && $item->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/read' $item->type '.png')}" alt="{$item->strtype} - {str tag='read' section='activity'}" />
      {elseif $item->strtype == 'usermessage'}
        <img src="{theme_url filename=cat('images/' $item->type '.png')}" alt="{$item->strtype}" class="unreadmessage" />
      {else}
        <img src="{theme_url filename=cat('images/' $item->type '.png')}" alt="{$item->strtype}" />
      {/if}
    </td>
    <td>
      {if ($item->fromusr != 0)}
        {if ($item->fromusrlink)}<a href="{$item->fromusrlink}">{/if}
          {$item->fromusr|display_name|truncate:$maxnamestrlength}
        {if ($item->fromusrlink)}</a>{/if}
      {else}
          {str tag="system"}
      {/if}
    </td>
    <td>
    {if $item->message}
      <a href="" onclick="showHideMessage({$item->id}, '{$item->table}'); return false;">
        {if !$item->read} <span class="accessible-hidden">{str tag='unread' section='activity'}: </span> {/if}
        {$item->subject|truncate:60}
        <span class="accessible-hidden">{str tag='clickformore' section='artefact.multirecipientnotification'}</span>
      </a>

      <div id="message-{$item->table}-{$item->id}" class="hidden">{$item->message|safe}
        {if $item->url}
        <br />
        <a href="{$WWWROOT}{$item->url}">
            {if $item->urltext}
                {$item->urltext}
            {else}
                {str tag="more..."}
            {/if}
            {str tag='linkindicator' section="artefact.multirecipientnotification"}
            </a>
        {/if}
        {if $item->return}
          <br /><a href="{$WWWROOT}{$item->return}">{$item->returnoutput}</a>
        {/if}
      </div>

    {elseif $item->url}
      <a href="{$WWWROOT}{$item->url}">{$item->subject|truncate:60}</a>
    {else}
      {$item->subject|truncate:60}
    {/if}
    </td>
    <td class="userlist">
      {if $item->return}
        <span id="short{$item->id}">
            <a onclick="return toggleMe('long{$item->id}', 'short{$item->id}')" href="javascript:void(0)">
                <img class="togglebtn" src="{theme_url filename='images/expand.png'}" />
            </a>
            {assign var="tousr" value=$item->tousr[0]}
            {if $tousr['link']}<a href="{$tousr['link']}">{/if}
                {$tousr['display']|truncate:$maxnamestrlength}
            {if $tousr['link']}</a>{/if}
        </span>
        <span style="display:none;" id="long{$item->id}">
          <a onclick="return toggleMe('short{$item->id}', 'long{$item->id}')" href="javascript:void(0)">
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
        {if ($tousr['link'])}<a href="{$tousr['link']}">{/if}
            {$tousr['display']|truncate:$maxnamestrlength}
        {if ($tousr['link'])}</a>{/if}
        {/if}
    </td>
    <td>{$item->date}</td>
    <td class="center">
      {if $item->read}
        <img src="{theme_url filename='images/star.png'}" alt="{str tag=read section=activity}">
      {else}
        <label class="accessible-hidden" for="unread-{$item->table}-{$item->id}">{str tag='markasread' section='activity'}</label>
        <input type="checkbox" class="tocheckread" name="unread-{$item->table}-{$item->id}" id="unread-{$item->table}-{$item->id}">
  {/if}
    </td>
    <td class="center">
        <label class="accessible-hidden" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
        <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
    </td>
  </tr>
{/foreach}
