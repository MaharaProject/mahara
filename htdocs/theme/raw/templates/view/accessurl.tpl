{include file="header.tpl"}

{include file="view/editviewtabs.tpl" selected='share' issiteview=$issiteview}

<div id="accessurl-container">

<div class="view-container">
    {if $collectionid}
        <label>{str tag=pagepartofcollection section=view arg1=$collectiontitle}</label>
    {/if}
    <h2>{str tag=secreturls section=view}</h2>

    <!-- Url -->
    {if $newform != null}
    <div class="accessurl-button">
        {$newform|safe}
    </div>
    {/if}

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
                    <div class="row">
                        <div class="col-xs-12 col-sm-9">
                            <strong class="secret-url">{$item.url}</strong>
                        </div>
                        <div class="col-xs-12 col-sm-3">
                            <div class="btn-action-list">
                                <div class="btn-top-right btn-group btn-group-top">
                                    <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton btn btn-secondary btn-sm" title="{str tag=copytoclipboard}" href="#">
                                        <span class="icon icon-files-o icon-lg" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=copytoclipboard}</span>
                                    </a>
                                    <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline btn btn-secondary closed" title="{str tag=edit}" href="">
                                        <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                                        <span class="icon icon-chevron-down right" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=edit}</span>
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

<div class="pageshare">

    <h2 class="access-title">{str tag=sharedwithothers section=view}</h2>
    <!-- Access -->
    {$form|safe}
    {include file="progress_meter.tpl"}

</div>

</div>
{include file="footer.tpl"}
