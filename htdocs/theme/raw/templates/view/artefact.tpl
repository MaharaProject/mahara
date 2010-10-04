{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message delete">{$notrudeform|safe}</div>{/if}

        <h2>
            <a href="{$WWWROOT}view/view.php?id={$viewid}">{$viewtitle}</a>{if $ownername} {str tag=by section=view}
            <a href="{$WWWROOT}{$ownerlink}">{$ownername}</a>{/if}{foreach from=$artefactpath item=a}:
                {if $a.url}<a href="{$a.url}">{/if}{$a.title}{if $a.url}</a>{/if}{if $hasfeed}<a href="{$feedlink}"><img class="feedicon" src="{theme_url filename='images/rss.gif'}"></a>{/if}
            {/foreach}
        </h2>

        <div id="view">
            <div id="bottom-pane">
                <div id="column-container">
                {$artefact|safe}
                </div>
            </div>
        </div>

      <div class="viewfooter cb">
        {if $feedback->count || $enablecomments}
        <table id="feedbacktable" class="fullwidth table">
          <thead><tr><th>{str tag="feedback" section="artefact.comment"}</th></tr></thead>
          <tbody>
            {$feedback->tablerows|safe}
          </tbody>
        </table>
        {$feedback->pagination|safe}
        {/if}
        <div id="viewmenu">
{include file="view/viewmenu.tpl"}
        </div>
        <div>{$addfeedbackform|safe}</div>
        <div>{$objectionform|safe}</div>
      </div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
