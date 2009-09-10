{include file="viewmicroheader.tpl"}

<h1>{if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle|escape}{if !$new}</a>{/if}</h1>

<p id="view-description">{$viewdescription}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
  <div class="viewfooter">
    {if $tags}<div class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</div>{/if}
    <div>{$releaseform}</div>
    <table id="feedbacktable" class="fullwidth table">
      <thead><tr><th>{str tag="feedback" section="view"}</th></tr></thead>
    </table>
	<div id="viewmenu">
        {include file="view/viewmenu.tpl"}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform}</div>{/if}
    {if $objectionform}<div>{$objectionform}</div>{/if}
  </div>
</div>
{include file="microfooter.tpl"}
