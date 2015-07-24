{include file="header.tpl"}

<div class="text-right btn-top-right btn-group btn-group-top mbl">
    {$newform|safe}
</div>

{* Clear the float from btn-group-top *}
<div class="clearfix"></div>

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
    <table class="secreturls table">
        <tbody>
        {foreach from=$editurls item=item name=urls}
            <tr class="{cycle values='r0,r1' advance=false}">
                <td>
                    {$item.deleteform|safe}
                </td>
                <td>
                    <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-default mrs" title="{str tag=copytoclipboard}" href="#">
                        <span class="icon icon-files-o icon-lg"></span>
                        <span class="sr-only">{str tag=copytoclipboard}</span>
                    </a>
                    <strong>{$item.url}</strong>
                </td>
                <td class="control-buttons">
                    <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-default" title="{str tag=edit}" href="">
                        <span class="icon icon-pencil icon-lg"></span>
                        <span class="icon icon-chevron-down pls"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                </td>
            </tr>
            <tr class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
                <td colspan=3>
                    {$item.editform|safe}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{/if}

{include file="footer.tpl"}
