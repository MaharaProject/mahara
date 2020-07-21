{include file="header.tpl"}

{if $userplantemplates}
    <div class="modal fade" id="template_selection_dialog" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h1 class="modal-title">{$choosetemplate}</h1>
                </div>
                <div class="modal-body">
                    <p>{$templatedialogdescription}</p>

                    <div class="radio">
                        <label>
                            <input class="templatelist" type="radio" name="optradio" value="" checked>{$notemplate}
                        </label>
                    </div>
                    {foreach from=$userplantemplates item=template}
                        <div class="radio">
                            <label>
                                <input class="templatelist" type="radio" name="optradio" value="{$template->id}">{$template->title}
                            </label>
                        </div>
                    {/foreach}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{$close}</button>
                </div>
            </div>

        </div>
    </div>
    <button id="template_selection_button" type="button" class="btn btn-secondary">{$fromtemplate}</button>
{/if}

{$form|safe}
{include file="pagemodal.tpl"}
{include file="footer.tpl"}
