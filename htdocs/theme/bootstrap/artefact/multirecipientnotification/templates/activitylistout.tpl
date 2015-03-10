{foreach from=$data item=item name='notification'}
<div class="notification panel panel-default {if $dwoo.foreach.notification.last}last{/if}">
   <a class="header collapsed panel-heading" href="#notification-{$item->id}" data-toggle="collapse" aria-expanded="1" aria-controls="notification-{$item->id}">
        <div class="icon pull-left">
            {if $item->read && $item->type == 'usermessage'}
            <span class="fa fa-envelope"></span><span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
            {elseif $item->strtype == 'usermessage'}
            <span class="fa fa-envelope"></span><span class="sr-only">{$item->strtype}</span>
            {else}
            <span class="fa fa-wrench"></span>
            <span class="sr-only">{$item->strtype}</span>
            {/if}
        </div>
        <h3 class="subject">
            <span class="sr-only">{str section='activity' tag='subject'}</span>
            {if !$item->read} 
            <span class="accessible-hidden sr-only">
                {str tag='unread' section='activity'}: 
            </span>
            {/if}
            {$item->subject|truncate:40}
        </h3>
        <div class="metadata">
            <span>
                {str section='artefact.multirecipientnotification' tag='touser'}:
            </span>
            <span class="username">
                {if count($item->tousr) > 1}
                {assign var="tousr" value=$item->tousr[0]}
                {$tousr['username']|truncate:$maxnamestrlength}
                <span>... ({count($item->tousr)})<span><span class="sr-only">more</span>
                {else}
                {assign var="tousr" value=$item->tousr[0]}
                {$tousr['username']|truncate:$maxnamestrlength}
                {/if}
            </span>
            <span class="sentdate">
                , {$item->date}
            </span>
        </div>
        <div class="notification-control">
            <div class="control">
                <div class="control-wrapper prl">
                    <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
                    <label class="marked delete" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
                </div>
            </div>
            <span class="content-expanded fa fa-chevron-up"></span>
        </div>
    </a>
    <div id="notification-{$item->id}" class="collapse">
        {if $item->message}
        <div class="content panel-body">
            <p class="tousers">
                <span class="recipientlist">
                <strong>
                    {str section='artefact.multirecipientnotification' tag='touser'}: 
                </strong>
                {if count($item->tousr) > 1}
                <span class="tousers">
                    {foreach from=$item->tousr item=tousr key=break}
                    {if ($tousr['link'])}
                    <a class="prm" href="{$tousr['link']}">
                        {/if}
                        {$tousr['display']|truncate:$maxnamestrlength}
                        {if ($tousr['link'])}
                    </a>{/if}
                    {/foreach}
                </span>
                {else}
                <span class="tousers">
                {assign var="tousr" value=$item->tousr[0]}
                {if $tousr['link']}
                    <a href="{$tousr['link']}">
                    {/if}
                    {$tousr['display']|truncate:$maxnamestrlength}
                    {if $tousr['link']}
                    </a>{/if}
                </span>
                {/if}
            </p>
            <p>{$item->message|safe}</p>
        </div>
        {/if}
        <div class="actions panel-footer mbl">
            <div class="url">
                {if $item->url}
                <a class="action" href="{$WWWROOT}{$item->url}">
                    <span class="fa fa-reply"></span> 
                    {if $item->urltext}
                    {$item->urltext}
                    {/if}
                </a>
                {/if}
                {if $item->return}
                <a class="action" href="{$WWWROOT}{$item->return}">
                    <span class="fa fa-reply-all"></span> {$item->returnoutput}
                </a>
                {/if}
            </div>
        </div>
    </div>
</div>
{/foreach}
