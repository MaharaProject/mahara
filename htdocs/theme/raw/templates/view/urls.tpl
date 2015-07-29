{include file="header.tpl"}

<div class="clearfix"></div>

{if !$allownew}
    <div class="message alert alert-warning mtxl">
        {if $onprobation}
            {str tag=publicaccessnotallowedforprobation section=view}
        {else}
            {str tag=publicaccessnotallowed section=view}
        {/if}
    </div>
{/if}

{if $editurls}
    <div class="panel panel-secondary mtl">
        <ul class="secreturls list-group">
        {foreach from=$editurls item=item name=urls}
            <li class="list-group-item plm {cycle values='r0,r1' advance=false}">
                <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-default btn-xs" title="{str tag=copytoclipboard}" href="#">
                    <span class="icon icon-files-o"></span>
                    <span class="sr-only">{str tag=copytoclipboard}</span>
                </a>
                <span class="metadata plm prm">{$item.url}</span>
                <span class="control-buttons ptm btn-group">
                    {$item.deleteform|safe}
                    <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-default btn-xs" title="{str tag=edit}" href="">
                        <span class="icon icon-pencil"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                </span>
                <div class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
                    {$item.editform|safe}
                </div>
            </li>
        {/foreach}
        </ul>
        <div class="plm">
            {$newform|safe}
        </div>
    </div>
{else}
    <div class="mtl lead text-center">
        {$newform|safe}
    </div>
{/if}

{include file="footer.tpl"}
