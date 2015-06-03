
{if !$items}
<div class="panel-body">
<p class="lead text-small">{str tag=nomessages section=blocktype.inbox}</p>
</div>
{else}
<div id="inboxblock" class="list-group">
    {foreach from=$items item=i}
    <div class="list-group-item ">
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

        {if $i->message}
        <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity/index.php{/if}" class="link-block collapsed inbox-showmessage{if !$i->read} unread{/if} mochi-collapse">
            {if !$i->read}<span class="accessible-hidden sr-only">{str tag=unread section=activity}: </span>{/if}{$i->subject|truncate:50}
            <span class="text-small fa fa-chevron-down pls collapse-indicator pull-right"></span>
        </a>
        <div class="panel-body inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->table}-{$i->id}">
            <p>{$i->message|safe}</p>
            {if $i->url}
            <a href="{$WWWROOT}{$i->url}" class="btn btn-default btn-xs pull-right">
                {str tag="more..."} <span class="fa fa-arrow-right mls fa-sm"></span>
            </a>
            {/if}
        </div>
        {elseif $i->url}
        <a href="{$WWWROOT}{$i->url}">{$i->subject}</a>
        {else}
        {$i->subject}
        {/if}
    </div>
    {/foreach}
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
</div>
{if $desiredtypes}
    <a class="panel-footer" href="{$WWWROOT}account/activity/index.php?type={$desiredtypes}">{str tag=More section=blocktype.inbox} <span class="fa fa-arrow-circle-right mls  pull-right"></span></a>
{/if}

{/if}
