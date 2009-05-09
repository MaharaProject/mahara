{include file="header.tpl"}

{include file="columnfullstart.tpl"}

        <h2>
            <a href="{$WWWROOT}view/view.php?id={$viewid}">{$viewtitle}</a>{if $ownername} {str tag=by section=view}
            <a href="{$WWWROOT}{$ownerlink}">{$ownername}</a>{/if}{foreach from=$artefactpath item=a}:
                {if $a.url}<a href="{$a.url}">{/if}{$a.title}{if $a.url}</a>{/if}
            {/foreach}
        </h2>

        <div id="view">
            <div id="bottom-pane">
                <div id="column-container">
                {$artefact}
                </div>
            </div>
        </div>

        <div id="publicfeedback">
        <table id="feedbacktable" class="fullwidth">
          <thead>
            <tr><th>{str tag="feedback" section="view"}</th></tr>
          </thead>
        </table>
        </div>
        <div id="viewmenu">
{include file="view/viewmenu.tpl"}
        </div>
        <div>{$addfeedbackform}</div>
        <div>{$objectionform}</div>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
