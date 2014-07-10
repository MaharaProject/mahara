{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message deletemessage">{$notrudeform|safe}</div>{/if}

{if !$microheaders && ($mnethost || $editurl)}
<div class="viewrbuttons">
  {if $editurl}{strip}
    {if $new}
      <a class="btn" href="{$editurl}">{str tag=back}</a>
    {else}
      <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn editview">{str tag=editthisview section=view}</a>
    {/if}
  {/strip}{/if}
  {if $mnethost}<a href="{$mnethost.url}" class="btn">{str tag=backto arg1=$mnethost.name}</a>{/if}
</div>
{/if}

{if $maintitle}<h1 id="viewh1">{$maintitle|safe}</h1>{/if}

{if !$microheaders && $collection}
    {include file=collectionnav.tpl}
{/if}

<p>
{assign var='author_link_index' value=1}
{include file=author.tpl}
<p>

<div id="view-description">{$viewdescription|clean_html|safe}</div>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent|safe}
                <div class="cb">
                </div>
            </div>
        </div>
  <div class="viewfooter">
    {if $tags}<div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</div>{/if}
    {if $releaseform}<div class="releaseviewform">{$releaseform|safe}</div>{/if}
    {if $view_group_submission_form}<div class="submissionform">{$view_group_submission_form|safe}</div>{/if}
    {if $feedback->position eq 'base'}
        {if $feedback->count || $enablecomments}
        <h3 class="title">{str tag="feedback" section="artefact.comment"}</h3>
        <div id="feedbacktable" class="fullwidth">
            {$feedback->tablerows|safe}
        </div>
        {$feedback->pagination|safe}
        {/if}
    {/if}
	<div id="viewmenu">
        {if $feedback->position eq 'base' && $enablecomments}
            <a id="add_feedback_link" class="feedback" href="">{str tag=placefeedback section=artefact.comment}</a>
        {/if}
        {include file="view/viewmenu.tpl"}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform|safe}</div>{/if}
    {if $objectionform}<div>{$objectionform|safe}</div>{/if}
  </div>
</div>
{if $visitstring}<div class="ctime center s">{$visitstring}</div>{/if}

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
