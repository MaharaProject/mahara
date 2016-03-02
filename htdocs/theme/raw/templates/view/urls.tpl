{include file="header.tpl"}

<div class="btn-top-right btn-group btn-group-top">
    {$newform|safe}
</div>

<div class="view-container">
    {if !$allownew}
    <div class="message info">
        {if $onprobation}
            {str tag=publicaccessnotallowedforprobation section=view}
        {else}
            {str tag=publicaccessnotallowed section=view}
        {/if}
    </div>
    {/if}

    {if $editurls}
    <div class="panel panel-default">
        <h2 class="panel-heading">
            {str tag=secreturls section=view}
        </h2>
        <div class="secreturls list-group">
            {foreach from=$editurls item=item name=urls}
                <div class="{cycle values='r0,r1' advance=false} list-group-item">
                    <strong>{$item.url}</strong>
                    <div class="btn-top-right btn-group btn-group-top">
                        <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-default btn-xs" title="{str tag=copytoclipboard}" href="#">
                            <span class="icon icon-files-o icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str tag=copytoclipboard}</span>
                        </a>
                        <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-default btn-xs closed" title="{str tag=edit}" href="">
                            <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="icon icon-chevron-down right" role="presentation" aria-hidden="true"></span>
                            <span class="sr-only">{str tag=edit}</span>
                        </a>
                        {$item.deleteform|safe}
                    </div>
                </div>
                <div class="editrow {cycle} url-editform js-hidden list-group-item" id="edit-{$item.id}-form">
                    {$item.editform|safe}
                </div>
            {/foreach}
        </div>
    </div>
    {/if}
</div>

{include file="footer.tpl"}
