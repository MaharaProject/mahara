
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
            <span class="icon icon-envelope type-icon"></span>
            <span class="sr-only">{$i->strtype}</span>
            {else}
            <!-- Supposed to be unread -->
            <span class="icon icon-envelope type-icon"></span>
            <span class="sr-only">{$i->strtype}</span>
            {/if}
        </div>

        {if $i->message}
        <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}module/multirecipientnotification/inbox.php{/if}" class="link-block collapsed inbox-showmessage{if !$i->read} unread{/if} mochi-collapse">
            {if !$i->read}<span class="accessible-hidden sr-only">{str tag=unread section=activity}: </span>{/if}{$i->subject|truncate:50}
            <span class="text-small icon icon-chevron-down pls collapse-indicator pull-right"></span>
        </a>
        <div class="panel-body inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->table}-{$i->id}">
            <p>{$i->message|safe}</p>
            {if $i->url}
            <a href="{$WWWROOT}{$i->url}" class="btn btn-default btn-xs pull-right">
                {str tag="more..."} <span class="icon icon-arrow-right mls icon-sm"></span>
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
    <script type="application/javascript">
        var blockid = '{$blockid}';
        {literal}
        jQuery(window).ready(function() {
            jQuery("#" + blockid + " a.inbox-showmessage").each(function() {
                var el = jQuery(this);
                el.click(function(e) {
                    e.preventDefault();
                    var message = jQuery(e.target).parent().find(".inbox-message");
                    message.toggleClass('hidden');
                    var unreadText = jQuery(e.target).find(".accessible-hidden");
                    if (unreadText.length) {
                        var tableinfo = message.attr('id').split('-');
                        var id = tableinfo.pop();
                        var table = tableinfo.pop();
                        var pd = {'readone':id, 'table':table};
                        sendjsonrequest(config.wwwroot + 'module/multirecipientnotification/indexin.json.php', pd, 'GET', function(data) {
                            unreadText.remove();
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
    <a class="panel-footer" href="{$WWWROOT}module/multirecipientnotification/inbox.php?type={$desiredtypes}">{str tag=More section=blocktype.inbox} <span class="icon icon-arrow-circle-right mls  pull-right"></span></a>
{/if}

{/if}
