{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $maintitle}<h1>{$maintitle}</h1>{/if}

{if !$microheaders && $mnethost}
<div class="rbuttons">
  <a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>
</div>
{/if}

<p id="view-description">{$viewdescription}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
  <div class="viewfooter cb">
    {if $tags}<div class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</div>{/if}
    <div>{$releaseform}</div>
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
    {if $addfeedbackform}<div>{$addfeedbackform}</div>{/if}
    {if $objectionform}<div>{$objectionform}</div>{/if}
  </div>
</div>
{if $visitstring}<div class="ctime center s">{$visitstring}</div>{/if}

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}