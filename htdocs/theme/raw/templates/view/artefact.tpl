{include file="header.tpl"}

        <h1>
            <a href="{$WWWROOT}view/view.php?id={$viewid}">{$viewtitle|escape}</a>{if $ownername} {str tag=by section=view}
            <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}{foreach from=$artefactpath item=a}:
                {if $a.url}<a href="{$a.url}">{/if}{$a.title|escape}{if $a.url}</a>{/if}
            {/foreach}
        </h1>

        <div id="view">
            <div id="bottom-pane">
                <div id="column-container">
                {$artefact}
                </div>
            </div>
        </div>

      <div class="viewfooter">
        <table id="feedbacktable" class="fullwidth table">
          <thead><tr><th>{str tag="feedback" section="view"}</th></tr></thead>
          <tbody>
            {$feedback->tablerows}
          </tbody>
        </table>
        {$feedback->pagination}
        <div id="viewmenu">
{include file="view/viewmenu.tpl"}
        </div>
        <div>{$addfeedbackform}</div>
        <div>{$objectionform}</div>
      </div>

{include file="footer.tpl"}
