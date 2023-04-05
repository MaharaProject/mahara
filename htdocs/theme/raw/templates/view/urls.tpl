{include file="header.tpl"}

<div class="btn-top-right btn-group btn-group-top">
    {$newform|safe}
</div>

<div class="view-container">
    {if !$allownew}
    <div class="alert alert-info">
        {if $onprobation}
            {str tag=publicaccessnotallowedforprobation section=view}
        {else}
            {str tag=publicaccessnotallowed section=view}
        {/if}
    </div>
    {/if}

    {if $editurls}
    <div class="card">
        <h2 class="card-header">
            {str tag=secreturls section=view}
        </h2>
        <div class="secreturls list-group">
            {foreach from=$editurls item=item name=urls}
                <div class="{cycle values='r0,r1' advance=false} list-group-item">
                    <div class="flex-row">
                        <div class="flex-title">
                            <h3 class="list-group-item-heading secret-url">{$item.url}</h3>
                        </div>
                        <div class="flex-controls">
                            <div class="btn-action-list">
                                <div class="btn-top-right btn-group btn-group-top">
                                    <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-secondary btn-sm" title="{str tag=copytoclipboard}" href="#">
                                        <span class="icon icon-regular icon-clone" role="presentation" aria-hidden="true"></span>
                                        <span class="visually-hidden">{str tag=copytoclipboard}</span>
                                    </a>
                                    <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-secondary btn-sm closed" title="{str tag=edit}" href="">
                                        <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                                        <span class="visually-hidden">{str tag=edit}</span>
                                    </a>
                                    {$item.deleteform|safe}
                                </div>
                            </div>
                        </div>
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

{* Modal form *}
<div tabindex="0" class="modal fade" id="copy-secreturl">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    <span class="icon icon-regular icon-clone"></span>
                    {str tag=copyingsecreturl section=view}
                </h1>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<script>
$('.url-copytoclipboardbutton').each(function() {
    var clipbutton = $(this);
    $(clipbutton).on('click', function() {
        $("#copy-secreturl .modal-body").text(clipbutton.data('clipboard-text'));
        $("#copy-secreturl").modal('show');
    });
});
</script>
{include file="footer.tpl"}
