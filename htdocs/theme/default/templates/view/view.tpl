{include file="header.tpl"}

{include file="columnfullstart.tpl"}
{if $can_edit}
<div class="fr editview">
    <span class="settingsicon">
        <a href="blocks.php?id={$viewid}">{str tag=editthisview section=view}</a>
    </span>
</div>
{/if}
<div id="view">
	<h3>{$viewtitle|escape} {str tag=by section=view} <a href="{$WWWROOT}user/view.php?id={$viewowner}">{$formattedowner|escape}</a></h3>

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
