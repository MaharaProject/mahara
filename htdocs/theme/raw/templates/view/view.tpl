{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message delete narrow">{$notrudeform|safe}</div>{/if}

{if $maintitle}<h1>{$maintitle|safe}</h1>{/if}

{if !$microheaders && ($mnethost || $editurl)}
<div class="rbuttons">
  {if $editurl}{strip}
    {if $new}
      <a class="btn" href="{$editurl}">{str tag=back}</a>
    {else}
      <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn">{str tag=editthisview section=view}</a>
    {/if}
  {/strip}{/if}
  {if $mnethost}<a href="{$mnethost.url}" class="btn">{str tag=backto arg1=$mnethost.name}</a>{/if}
</div>
{/if}

<div id="view-description">{$viewdescription|clean_html|safe}</div>

{if !$microheaders && $collection}
    {include file=collectionnav.tpl}
{/if}

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
    {if $releaseform}<div class="releaseviewform">{$releaseform|safe}</div>{/if}
    {if $view_group_submission_form}<div class="submissionform">{$view_group_submission_form|safe}</div>{/if}
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
