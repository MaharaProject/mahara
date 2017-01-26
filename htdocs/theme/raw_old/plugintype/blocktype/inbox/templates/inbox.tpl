{if !$items}
{str tag=nomessages section=blocktype.inbox}
{else}
<table id="inboxblock" class="fullwidth fixwidth table table-striped">
{foreach from=$items item=i}
<tr class="{cycle values='r0,r1'}">
    <td class="icon-container">
  {if $i->read}
      <img src="{theme_url filename=cat('images/' $i->type '.png')}" alt="{$i->strtype}" />
  {else}
      <img src="{theme_url filename=cat('images/' $i->type '.png')}" class="unreadmessage" alt="{$i->strtype}" />
  {/if}
    </td>
    <td>
  {if $i->message}
      <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity/index.php{/if}" class="inbox-showmessage{if !$i->read} unread{/if}">
      {if !$i->read}<span class="accessible-hidden sr-only">{str tag=unread section=activity}: </span>{/if}{$i->subject}
      </a>
      <div class="inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->id}">{$i->message|safe}
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
{if $morelink}
<div class="morelinkwrap"><a class="morelink" href="{$morelink}">{str tag=More section=blocktype.inbox} &raquo;</a></div>
<div class="cb"></div>
{/if}
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
                        sendjsonrequest(config.wwwroot + 'account/activity/index.json.php', pd, 'GET', function(data) {
                            unreadText.remove();
                            updateUnreadCount(data);
                        });
                    }
                });
            });
        });
        {/literal}
    </script>
{/if}
