{foreach from=$data item=item}
<div class="notification-item panel {if !$item->read}unread panel-warning {else} panel-default{/if}">
    <div class="notification-header panel-heading clearfix" data-toggle="collapse" data-target="#notification-{$item->id}" aria-expanded="false" aria-controls="#notification-{$item->id}">
        <div class="notification-icon pull-left">
            {if $item->read && $item->type == 'usermessage'}
            <span class="fa fa-envelope"></span><span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
            {elseif $item->strtype == 'usermessage'}
            <span class="fa fa-envelope"></span><span class="sr-only">{$item->strtype}</span>
            {else}
            <span class="fa fa-wrench"></span>
            <span class="sr-only">{$item->strtype}</span>
            {/if}
        </div>
        <h3 class="notification-subject">
            <span class="sr-only">{str section='activity' tag='subject'}</span>
            {if !$item->read} 
            <span class="accessible-hidden sr-only">
                {str tag='unread' section='activity'}: 
            </span>
            {/if}
            {$item->subject|truncate:40}
        </h3>
        <div class="notification-metadata">
            <span class="sr-only">
                {str section='artefact.multirecipientnotification 'tag='fromuser'}:
            </span>
            {if ($item->fromusr != 0)}
            {if ($item->fromusrlink)}
            <span class="username">
                {/if}
                - {$item->fromusr|display_name|truncate:$maxnamestrlength}
                {if ($item->fromusrlink)}
            </span>
            {/if}
            {else}
            <span class="username">
                - {str tag="system"}
            </span>
            {/if}
            <span class="sentdate">
                , {$item->date}
            </span>
        </div>
        <div class="notification-control">
            <div class="control">
                {if !$item->read}
                <div class="control-wrapper prm">
                    <input type="checkbox" class="tocheckread" name="unread-{$item->table}-{$item->id}" id="unread-{$item->table}-{$item->id}">
                    <label class="marked read" for="unread-{$item->table}-{$item->id}">{str tag='markasread' section='activity'}</label>
                </div>
                {/if}
                <div class="control-wrapper prl">
                    <input type="checkbox" class="tocheckdel" name="delete-{$item->table}-{$item->id}" id="delete-{$item->table}-{$item->id}">
                    <label class="marked delete" for="delete-{$item->table}-{$item->id}">{str tag='delete' section='mahara'}</label>
                </div>
            </div>
            <span class="content-expanded fa fa-chevron-up"></span>
        </div>
    </div>
    <div id="notification-{$item->id}" class="collapse">
        {if $item->message}
        <div class="notification-content panel-body">
            {if ($item->fromusr != 0)}
            <p class="notification-fromusers">
                <strong>
                    {str section='artefact.multirecipientnotification' tag='fromuser'}:
                </strong>
                {if ($item->fromusrlink)}
                <span class="fromusers prm"> 
                    <a href="{$item->fromusrlink}">
                        {/if}
                        {$item->fromusr|display_name|truncate:$maxnamestrlength}
                        {if ($item->fromusrlink)}
                    </a>
                </span>
                {/if}
            </p>
                {else}
            <p class="notification-fromusers">
                <strong>
                    {str section='artefact.multirecipientnotification' tag='fromuser'}:
                </strong>
                <span>{str tag="system"}</span>
            </p>
            {/if}
            <p class="notification-tousers">
                <strong>
                    {str section='artefact.multirecipientnotification' tag='touser'}: 
                </strong>
                {if $item->return}
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
                <span>
                    {assign var="tousr" value=$item->tousr[0]}
                </span>
                {if ($tousr['link'])}
                <a href="{$tousr['link']}">
                    {/if}
                    <span>
                        {$tousr['display']|truncate:$maxnamestrlength}
                    </span>
                    {if ($tousr['link'])}
                </a>{/if}
                {/if}
            </p>
            <p>{$item->message|safe}</p>
            {if $item->url && $item->urltext === 'Collection'}
            <a class="notification-action" href="{$WWWROOT}{$item->url}">
                <span class="fa fa-arrow-right"></span>
                {$item->urltext}
            </a>
            {/if}
        </div>
        {/if}
        {if $item->url && $item->urltext === 'Reply'}
        <div class="notification-actions panel-footer mbl">
            <div class="notification-url">
                <a class="notification-action" href="{$WWWROOT}{$item->url}">
                    <span class="fa fa-reply"></span> 
                    {$item->urltext}
                </a>
                {if $item->return}
                <a class="notification-action" href="{$WWWROOT}{$item->return}">
                    <span class="fa fa-reply-all"></span> {$item->returnoutput}
                </a>
                {/if}
            </div>
        </div>
        {/if}
    </div>
</div>
{/foreach}
