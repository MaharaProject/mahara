{include file="header.tpl"}

{include file="columnfullstart.tpl"}
{if $can_edit}
<div class="fr editview">
    <span class="settingsicon">
        <a href="blocks.php?id={$viewid}&amp;new={$new}">{str tag=editmyview section=view}</a>
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
	<table id="feedbacktable">
		<thead>
			<tr><th colspan=5>{str tag=feedback section=view}</th></tr>
		</thead>
	</table>
	</div>
	<div id="viewmenu"></div>
</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
