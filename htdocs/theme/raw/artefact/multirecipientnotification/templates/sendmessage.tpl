{include file="header.tpl"}

{if $messages}
<table id="messagethread" class="fullwidth fixwidth listing">
    <tbody>
    {foreach from=$messages item=message}
        <tr class="{cycle values='r0,r1'}">
          <td class="profilepicturecolumn">
            <img src="{profile_icon_url user=$message->fromid maxwidth=20 maxheight=20}" alt="">
          </td>
          <td>
            <h5>
              {if ($message->fromusrlink)}<a href="{$message->fromusrlink}">{/if}
                  <span class="accessible-hidden">{str tag='From' section='mahara'}</span>
                  {$message->fromusrname}
              {if ($message->fromusrlink)}</a>{/if}
              <span class="postedon">{$message->ctime|strtotime|format_date}</span>
            </h5>
            <div>
                <label>{str tag='labelrecipients' section='artefact.multirecipientnotification'}</label>

                {foreach from=$message->tousrs item=recipient key="index"}
                    {if $recipient['link']}<a href="{$recipient['link']}">{/if}
                        <span class="accessible-hidden">{str tag='labelrecipients' section='artefact.multirecipientnotification'}</span>
                        {$recipient['display']}{if ($index + 1) < count($message->tousrs)}; {/if}
                    {if $recipient['link']}</a>{/if}
                {/foreach}
            </div>
            <div class="subjectdiv">
                <label>{str tag='labelsubject' section='artefact.multirecipientnotification'}</label>&nbsp;
                    <a href="{$link}?replyto={$message->id}&returnto={$returnto}">
                        <span class="accessible-hidden">{str tag='labelsubject' section='artefact.multirecipientnotification'}</span>
                        {$message->subject}
                    </a>
            </div>
            <div class="messagebody">{$message->message}</div>
          </td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/if}

{$form|safe}

{include file="footer.tpl"}
