{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message deletemessage">{$notrudeform|safe}</div>{/if}

        <h2>
            {$view->display_title()|safe}{foreach from=$artefactpath item=a}:
                {if $a.url}<a href="{$a.url}">{/if}{$a.title}{if $a.url}</a>{/if}{if $hasfeed}<a href="{$feedlink}"><img class="feedicon" src="{theme_image_url filename='feed'}"></a>{/if}
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
        <h3 class="title">{str tag="feedback" section="artefact.comment"}</h3>
        <div id="feedbacktable" class="feedbacktable fullwidth">
            {$feedback->tablerows|safe}
        </div>
        {$feedback->pagination|safe}
        {/if}
        <div id="viewmenu">
{include file="view/viewmenuartefact.tpl"}
        </div>
        <div>{$addfeedbackform|safe}</div>
        <div>{$objectionform|safe}</div>
      </div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
