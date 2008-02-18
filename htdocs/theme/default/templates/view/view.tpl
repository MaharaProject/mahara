{include file="header.tpl"}

{include file="columnfullstart.tpl"}

<h2>{$heading}</h2>

{if $can_edit}
<div class="fr editview">
    <span class="settingsicon">
        <a href="blocks.php?id={$viewid}&amp;new={$new}">{$streditviewbutton}</a>
    </span>
</div>
{/if}
<div id="view">
        <p class="view-description">{$viewdescription}</p>
	
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
	<div id="publicfeedback">
	<table id="feedbacktable" class="fullwidth">
		<thead>
			<tr><th>{str tag="feedback" section="view"}</th></tr>
		</thead>
	</table>
	</div>
	<div id="viewmenu"></div>
</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
