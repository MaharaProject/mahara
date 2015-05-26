<div class="collapsible-group">
{foreach from=$data item=item name='notification'}
    <div class="panel panel-default collapsible notification collapsible-group {if $dwoo.foreach.notification.first}first{/if} {if $dwoo.foreach.notification.last}last{/if}">
        <h4 class="panel-heading">
            <label class="panel-control">
                <span class="control prl">
                    <input type="checkbox" class="tocheck" name="select-{$item->table}-{$item->id}" id="select-{$item->table}-{$item->id}">
                    <span class="sr-only">{str tag='select' section='mahara'}</span>
                </span>
            </label>
            <a class="collapsed" href="#notification-{$item->id}" data-toggle="collapse" aria-expanded="1" aria-controls="notification-{$item->id}">
                <span class="details-group">
                    {if $item->read && $item->type == 'usermessage'}
                    <span class="fa fa-envelope type-icon prxl plxl"></span><span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
                    {elseif $item->strtype == 'usermessage'}
                    <span class="fa fa-envelope type-icon prxl plxl"></span><span class="sr-only">{$item->strtype}</span>
                    {elseif $item->strtype == 'Institution message'}
                         <span class="fa fa-university type-icon prxl plxl"></span>
                         <span class="sr-only">{$item->strtype}</span>
                    {else}
                    <span class="fa fa-wrench type-icon prxl plxl"></span>
                    <span class="sr-only">{$item->strtype}</span>
                    {/if}

                    <span class="sr-only">{str section='activity' tag='subject'}</span>
                    {if !$item->read}
                        <span class="accessible-hidden sr-only">
                            {str tag='unread' section='activity'}:
                        </span>
                    {/if}

                    {$item->subject|truncate:40}

                    <span class="metadata"> -
                        <span>
                            {str section='artefact.multirecipientnotification' tag='touser'}:
                        </span>
                        <span class="username">
                            {if count($item->tousr) > 0}
                                {assign var="tousr" value=$item->tousr[0]}
                                {if count($tousr['username']) > 0}
                                    {$tousr['username']|truncate:$maxnamestrlength}
                                {else}
                                    {$tousr['display']|truncate:$maxnamestrlength}
                                {/if}
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
                 </span>
                <span class="fa fa-chevron-down pls collapse-indicator pull-right"></span>
            </a>
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
