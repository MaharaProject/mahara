{if !$items}
{str tag=nomessages section=blocktype.inbox}
{else}
<table>
{foreach from=$items item=i}
<tr>
    <td class="icon-container">
      <div class="icon">
        <img src="{theme_url filename=cat('images/' $i->type '.gif')}" />
      </div>
    </td>
    <td>
  {if $i->message}
      <a href="" class="inbox-showmessage{if !$i->read} unread{/if}">{$i->subject|escape}</a>
      <div class="inbox-message hidden" id="inbox-message-{$i->id}">{$i->message|clean_html}
      {if $i->url}<br><a href="{$i->url|escape}" class="s">{if $i->urltext}{$i->urltext|escape} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
      </div>
  {elseif $i->url}
      <a href="{$i->url|escape}">{$i->subject|escape}</a>
  {else}
      {$i->subject|escape}
  {/if}
    </td>
</tr>
{/foreach}
</table>
{if $desiredtypes}
<a href="{$WWWROOT}account/activity?type={$desiredtypes|escape}">{str tag=More section=blocktype.inbox} &raquo;</a>
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
            toggleElementClass('hidden', message);
            if (hasElementClass(element, 'unread')) {
                var id = getNodeAttribute(message, 'id').replace(/inbox-message-(\d+)$/, '$1');
                var pd = {'readone':id};
                sendjsonrequest(config.wwwroot + 'account/activity/index.json.php', pd, 'GET', function(data) {
                    removeElementClass(element, 'unread');
                    updateUnreadCount(1, 'decrement');
                });
            }
        });
    });
});
{/literal}
</script>
{/if}