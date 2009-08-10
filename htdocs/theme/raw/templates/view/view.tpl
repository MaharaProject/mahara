{include file="viewmicroheader.tpl"}

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
{include file="microfooter.tpl"}
