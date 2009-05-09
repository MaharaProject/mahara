{include file="header.tpl"}

{include file="columnfullstart.tpl"}

<h2>{if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle|escape}{if !$new}</a>{/if}{if $ownername} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}</h2>

{if $can_edit}
<div class="fr editview">
    <span class="settingsicon">
        <a href="blocks.php?id={$viewid}&amp;new={$new}">{$streditviewbutton}</a>
    </span>
</div>
{/if}

<h2>{if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle}{if !$new}</a>{/if}{if $ownername} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername}</a>{/if}</h2>


<p class="view-description">{$viewdescription}</p>

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
</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
