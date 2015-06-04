{include file="header.tpl"}

<div class="text-right btn-top-right btn-group btn-group-top">
    {$newform|safe}
</div>

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
                <td><strong>{$item.url}</strong></td>
                <td class="control-buttons">
                    <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-default btn-xs" title="{str tag=copytoclipboard}" href="#">
                        <span class="fa fa-files-o"></span>
                        <span class="sr-only">{str tag=copytoclipboard}</span>
                    </a>
                    <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-default btn-xs" title="{str tag=edit}" href="">
                        <span class="fa fa-pencil"></span>
                        <span class="sr-only">{str tag=copytoclipboard}</span>
                    </a>
                    {$item.deleteform|safe}
                </td>
            </tr>
            <tr class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
                <td colspan=2>{$item.editform|safe}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{/if}

{include file="footer.tpl"}
