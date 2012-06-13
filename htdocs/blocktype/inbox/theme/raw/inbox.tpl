{if !$items}
{str tag=nomessages section=blocktype.inbox}
{else}
<table id="inboxblock" class="fullwidth fixwidth">
{foreach from=$items item=i}
<tr class="{cycle values='r0,r1'}">
    <td class="icon-container">
  {if $i->read && $i->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/read' $i->type '.gif')}" alt="{$i->strtype}" />
  {elseif $i->type == 'usermessage'}
        <img src="{theme_url filename=cat('images/' $i->type '.gif')}" class="unreadmessage" />
  {else}
        <img src="{theme_url filename=cat('images/' $i->type '.gif')}" alt="{$i->strtype}" />
  {/if}
    </td>
    <td>
  {if $i->message}
      <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity{/if}" class="inbox-showmessage{if !$i->read} unread{/if}">{$i->subject}</a>
      <div class="inbox-message hidden messagebody messagebody-{$i->type}" id="inbox-message-{$i->id}">{$i->message|safe}
      {if $i->url}<br><a href="{$WWWROOT}{$i->url}">{if $i->urltext}{$i->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
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
<div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}account/activity?type={$desiredtypes}">{str tag=More section=blocktype.inbox} &raquo;</a></div>
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
            var unreadicon = getFirstElementByTagAndClassName('img', 'unreadmessage', message.parentNode.parentNode);
            toggleElementClass('hidden', message);
            if (hasElementClass(element, 'unread')) {
                var id = getNodeAttribute(message, 'id').replace(/inbox-message-(\d+)$/, '$1');
                var pd = {'readone':id};
                sendjsonrequest(config.wwwroot + 'account/activity/index.json.php', pd, 'GET', function(data) {
                    removeElementClass(element, 'unread');
                    if (unreadicon) {
                        swapDOM(unreadicon, IMG({'src' : {/literal}'{$readicon}'{literal}, 'alt' : getNodeAttribute(unreadicon, 'alt') + ' - ' + {/literal}'{str tag='read' section='activity'}'{literal} }));
                    };
                    updateUnreadCount(data);
                });
            }
        });
    });
});
{/literal}
</script>
{/if}
