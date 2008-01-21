{include file="header.tpl"}

{include file="columnfullstart.tpl"}
        <h3><a href="{$WWWROOT}view/view.php?id={$viewid|escape}">{$viewtitle|escape}</a> {str tag=by section=view} <a href="{$WWWROOT}user/view.php?id={$viewowner}">{$formattedowner|escape}</a>: {foreach name=path from=$artefactpath item=path}{if $path.url}<a href="{$path.url|escape}">{/if}{$path.title|escape}{if $path.url}</a>{/if}{if !$smarty.foreach.path.last}: {/if}{/foreach}</h3>
        <div id="view">
            <div id="bottom-pane">
                <div id="column-container">
                {$artefact}
                </div>
            </div>
        </div>

        <div id="publicfeedback">
            <table id="feedbacktable">
                <thead>
                    <tr><th colspan=5>{str tag=feedback section=view}</th></tr>
                </thead>
            </table>
        </div>
        <div id="viewmenu"></div>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
