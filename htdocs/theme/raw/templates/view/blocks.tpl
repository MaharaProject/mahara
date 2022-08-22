{include file="header.tpl"}

<div class="view-instructions blocks">
    <form action="{$formurl}" method="post" class="row">
        <input type="submit" name="{$action_name}" id="action-dummy" class="d-none">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">

        <div class="col-with-collapse">
        {if $accessible}
            <div id="blocksinstructionaccessible" class="lead view-description with-addblock">
                {str tag='accessibilitymodedescription1' section='view'}
            </div>
        {else}
            <div id="blocksinstruction" class="lead view-description with-addblock">
                {str tag='blocksinstructionajaxlive2' section='view'}
            </div>
        {/if}
        {if $instructions}
            <div id="viewinstructions" class="first last form-group collapsible-group small-group">
            <fieldset  class="pieform-fieldset collapsible collapsible-small">
                <legend>
                    <a href="#viewinstructions-dropdown" data-bs-toggle="collapse" aria-expanded="{if $instructionscollapsed}false{else}true{/if}" aria-controls="viewinstructions-dropdown" class="{if $instructionscollapsed}collapsed{/if}">
                        {str tag='instructions' section='view'}<span class="icon icon-chevron-down collapse-indicator right text-inline"></span>
                    </a>
                </legend>
                <div class="fieldset-body collapse viewinstructions {if !$instructionscollapsed} show {/if}" id="viewinstructions-dropdown">
                    {$instructions|clean_html|safe}
                </div>
            </fieldset>
            </div>
        {/if}
        </div>
        {include file="view/editviewpageactions.tpl" selected='content' ineditor=true}
    </form>
</div>
<div class="view-container" selected='content'>
    <form action="{$formurl}" method="post" class="row">
        <input type="submit" name="{$action_name}" id="action-dummy" class="d-none">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">

        <div class="col">
            <div id="bottom-pane" data-role="workspace">
                <div id="column-container" class="user-page-content">
                    <div class="grid-stack gridedit">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal" id="addblock" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton btn-close" name="action_removeblockinstance_id_{$id}">
                    <span class="times">&times;</span>
                    <span class="visually-hidden">{str tag=Close}</span>
                </button>
                <h1 class="modal-title blockinstance-header text-inline" id="addblock-heading"></h1>
            </div>
            <div class="modal-body blockinstance-content">
                <div class="block-inner">{$addform|safe}</div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-shown modal-docked-right modal-docked closed blockinstance configure" id="configureblock" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton btn-close" name="close_configuration">
                    <span class="times">&times;</span>
                    <span class="visually-hidden">{str tag=closeconfiguration section=view}</span>
                </button>
                <h1 class="modal-title blockinstance-header text-inline float-start"></h1>
                <span class="icon icon-pencil-alt icon-2x float-end" role="presentation" aria-hidden="true"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
