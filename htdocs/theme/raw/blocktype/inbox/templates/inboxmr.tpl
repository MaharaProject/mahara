{if !$items}
{str tag=nomessages section=blocktype.inbox}
{else}
<table id="inboxblock" class="fullwidth fixwidth">
{foreach from=$items item=i}
<tr class="{cycle values='r0,r1'}">
    <td class="icon-container">
  {if $i->read}
      <img src="{theme_image_url filename=$i->type}" alt="{$i->strtype}" />
  {else}
      <img src="{theme_image_url filename=$i->type}" class="unreadmessage" alt="{$i->strtype}" />
  {/if}
    </td>
    <td>
  {if $i->message}
      <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity/index.php{/if}" class="inbox-showmessage{if !$i->read} unread{/if}">
      {if !$i->read}<span class="accessible-hidden">{str tag=unread section=activity}: </span>{/if}{$i->subject|truncate:50}
      </a>
      <div class="inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->table}-{$i->id}">{$i->message|safe}
      {if $i->url}<br><a href="{$WWWROOT}{$i->url}">{if $i->urltext}{$i->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
      {if $i->canreplyall}
        <br>
        <a title="{str tag=replyall section=artefact.multirecipientnotification}" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php?replyto={$i->id}&returnto=outbox">
            {str tag='replyall'  section='artefact.multirecipientnotification'}
        </a>
      {elseif $i->canreply}
        <br>
        <a title="{str tag=reply section=artefact.multirecipientnotification}" href="{$WWWROOT}a
rtefact/multirecipientnotification/sendmessage.php?id={$i->fromusr}{if !$i->startnewthread}&re
plyto={$i->id}{/if}&returnto=outbox">
            {str tag='reply' section='artefact.multirecipientnotification'}
        </a>
      {/if}
      </div>
  {elseif $i->url}
      <a href="{$WWWROOT}{$i->url}">{$i->subject}</a>
  {else}
      {$i->subject}
  {/if}
    </td>
</tr>
{/foreach}
</table>
{if $desiredtypes}
<div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}account/activity/index.php?type={$desiredtypes}">{str tag=More section=blocktype.inbox} &raquo;</a></div>
<div class="cb"></div>
{/if}
<script>
{literal}
addLoadEvent(function() {
    forEach(
{/literal}
        getElementsByTagAndClassName('a', 'inbox-showmessage', '{$blockid}'),
{literal}
        function(element) {
        connect(element, 'onclick', function(e) {
            e.stop();
            var message = getFirstElementByTagAndClassName('div', 'inbox-message', element.parentNode);
            var unreadText = getFirstElementByTagAndClassName(null, 'accessible-hidden', element);
            toggleElementClass('hidden', message);
            if (hasElementClass(element, 'unread')) {
                var tableid = getNodeAttribute(message, 'id').replace(/inbox-message-(.+)$/, '$1');
                var delimiterposition = tableid.indexOf("-");
                var table = tableid.substr(0, delimiterposition);
                var id = tableid.substr(delimiterposition + 1);
                var pd = {'readone':id, 'table':table};
                sendjsonrequest(config.wwwroot + 'artefact/multirecipientnotification/indexin.json.php', pd, 'GET', function(data) {
                    removeElementClass(element, 'unread');
                    removeElement(unreadText);
                    updateUnreadCount(data);
                });
            }
        });
    });
});
{/literal}
</script>
{/if}
