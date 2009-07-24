{include file="header.tpl"}
{if $mnethost}
<span class="fr"><a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a></span>
{/if}

<h1>{if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle|escape}{if !$new}</a>{/if}{if $ownername} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}</h1>

{if $can_edit}
<div class="page-buttons">
    <a class="btn-link" href="blocks.php?id={$viewid}&amp;new={$new}">{$streditviewbutton}</a>
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
        <div>{$releaseform}</div>
	<div id="publicfeedback">
	<table id="feedbacktable" class="fullwidth table">
		<thead>
			<tr><th>{str tag="feedback" section="view"}</th></tr>
		</thead>
	</table>
	</div>
	<div id="viewmenu">
        {include file="view/viewmenu.tpl"}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform}</div>{/if}
    {if $objectionform}<div>{$objectionform}</div>{/if}
</div>
{include file="footer.tpl"}
