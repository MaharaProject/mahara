{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message delete">{$notrudeform|safe}</div>{/if}

{if $maintitle}<h1>{$maintitle|safe}</h1>{/if}

{if !$microheaders && $collection}
    {include file=collectionnav.tpl}
{/if}

{if !$microheaders && $mnethost}
<div class="rbuttons">
  <a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>
</div>
{/if}

<p id="view-description">{$viewdescription|clean_html|safe}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent|safe}
                <div class="cb">
                </div>
            </div>
        </div>
  <div class="viewfooter">
    {if $tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</div>{/if}
    <div>{$releaseform|safe}</div>
    {if $view_group_submission_form}<div>{$view_group_submission_form|safe}</div>{/if}
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
        {include file="view/viewmenu.tpl" enablecomments=$enablecomments}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform|safe}</div>{/if}
    {if $objectionform}<div>{$objectionform|safe}</div>{/if}
  </div>
</div>
{if $visitstring}<div class="ctime center s">{$visitstring}</div>{/if}

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
