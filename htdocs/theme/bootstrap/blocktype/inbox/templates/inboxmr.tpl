{if !$items}
{str tag=nomessages section=blocktype.inbox}
{else}
<div id="inboxblock" class="list-group">
    {foreach from=$items item=i}
    <div class="list-group-item">
        <div class="icon-container pull-left pls prm">
            {if $i->read}
            <span class="fa fa-envelope type-icon"></span>
            <span class="sr-only">{$i->strtype}</span>
            {else}
            <!-- Supposed to be unread -->
            <span class="fa fa-envelope type-icon"></span>
            <span class="sr-only">{$i->strtype}</span>
            {/if}
        </div>
        <div>
            {if $i->message}
            <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity/index.php{/if}" class="inbox-showmessage{if !$i->read} unread{/if}">
                {if !$i->read}<span class="accessible-hidden sr-only">{str tag=unread section=activity}: </span>{/if}{$i->subject|truncate:50}
            </a>
            <div class="inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->msgtable}-{$i->id}">{$i->message|safe}
                {if $i->url}<br><a href="{$WWWROOT}{$i->url}">{if $i->urltext}{$i->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
            </div>
            {elseif $i->url}
            <a href="{$WWWROOT}{$i->url}">{$i->subject}</a>
            {else}
            {$i->subject}
            {/if}
        </div>
    </div>
    {/foreach}
</div>
{if $desiredtypes}
<div class="morelinkwrap panel-footer"><a class="morelink" href="{$WWWROOT}account/activity/index.php?type={$desiredtypes}">{str tag=More section=blocktype.inbox} &raquo;</a></div>
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
