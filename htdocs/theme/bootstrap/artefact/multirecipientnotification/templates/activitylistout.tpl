<div class="collapsible-group">
{foreach from=$data item=item name='notification'}
    <div class="panel panel-default panel-collapse collapsible notification collapsible-group {if $dwoo.foreach.notification.first}first{/if} {if $dwoo.foreach.notification.last}last{/if}">
        <h4 class="panel-heading">
            <a class="collapsed" href="#notification-{$item->id}" data-toggle="collapse" aria-expanded="1" aria-controls="notification-{$item->id}">
                {if $item->read && $item->type == 'usermessage'}
                <span class="fa fa-envelope type-icon prl"></span><span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
                {elseif $item->strtype == 'usermessage'}
                <span class="fa fa-envelope type-icon prl"></span><span class="sr-only">{$item->strtype}</span>
                {else}
                <span class="fa fa-wrench type-icon prl"></span>
                <span class="sr-only">{$item->strtype}</span>
                {/if}

                <span class="sr-only">{str section='activity' tag='subject'}</span>
                {if !$item->read} 
                    <span class="accessible-hidden sr-only">
                        {str tag='unread' section='activity'}: 
                    </span>
                {/if}

                {$item->subject|truncate:40}

                <span class="metadata">
                    <span>
                        {str section='artefact.multirecipientnotification' tag='touser'}:
                    </span>
                    <span class="username">
                        {if count($item->tousr) > 1}
                            {assign var="tousr" value=$item->tousr[0]}
                            {$tousr['username']|truncate:$maxnamestrlength}
                            <span>... ({count($item->tousr)})</span>
                            <span class="sr-only">more</span>
                        {else}
                            {assign var="tousr" value=$item->tousr[0]}
                            {$tousr['username']|truncate:$maxnamestrlength}
                        {/if}
                    </span>
                    <span class="sentdate">
                    , {$item->date}
                    </span>
                </span>
                <span class="fa fa-chevron-down pls collapse-indicator pull-right"></span>
            </a>
            <span class="panel-control">
                <span class="control">
                    <span class="control-wrapper prl">
                        <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
                        <label class="marked delete" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
                    </span>
                </span>
            </span>
        </h4>
        <div id="notification-{$item->id}" class="collapse">
            {if $item->message}
            <div class="content panel-body">
                <p class="tousers">
                    <span class="recipientlist">
                    <strong>
                        {str section='artefact.multirecipientnotification' tag='touser'}: 
                    </strong>
                    {if count($item->tousr) > 1}
                    <span>
                        {foreach from=$item->tousr item=tousr key=break}
                        {if ($tousr['link'])}<a href="{$tousr['link']}">{/if}
                            <span class="prm">
                            {$tousr['display']|truncate:$maxnamestrlength}
                            </span>
                        {if ($tousr['link'])}</a>{/if}
                        {/foreach}
                    </span>
                    {else}
                    <span>
                        {assign var="tousr" value=$item->tousr[0]}
                        {if $tousr['link']}<a href="{$tousr['link']}">{/if}
                            <span class="prm">
                            {$tousr['display']|truncate:$maxnamestrlength}
                            </span>
                        {if $tousr['link']}</a>{/if}
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
</div>