{include file="header.tpl"}

{if $accessible}
    <span class="sr-only">{str tag=accessibilitymodedescription section=view}</span>
{/if}

<div class="view-instructions blocks">
    <form action="{$formurl}" method="post" class="row">
        <input type="submit" name="{$action_name}" id="action-dummy" class="d-none">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">

        <div class="col-with-collapse">
        {if $instructions}
            <div id="viewinstructions" class="last form-group collapsible-group small-group">
            <fieldset  class="pieform-fieldset collapsible collapsible-small">
                <legend>
                    <a href="#viewinstructions-dropdown" data-toggle="collapse" aria-expanded="{if $instructionscollapsed}false{else}true{/if}" aria-controls="viewinstructions-dropdown" class="{if $instructionscollapsed}collapsed{/if}">
                        {str tag='instructions' section='view'}
                        <span class="icon icon-chevron-down collapse-indicator right text-inline"></span>
                    </a>
                </legend>
                <div class="fieldset-body collapse viewinstructions {if !$instructionscollapsed} show {/if}" id="viewinstructions-dropdown">
                    {$instructions|clean_html|safe}
                </div>
            </fieldset>
            </div>
        {else}
            <div id="blocksinstruction" class="lead view-description with-addblock">
                {str tag='blocksintructionnoajax' section='view'}
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
                <button class="deletebutton close" name="action_removeblockinstance_id_{$id}">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=Close}</span>
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
                <button class="deletebutton close" name="close_configuration">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=closeconfiguration section=view}</span>
                </button>
                <h1 class="modal-title blockinstance-header text-inline"></h1>
                <span class="icon icon-cogs icon-2x float-right" role="presentation" aria-hidden="true"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
