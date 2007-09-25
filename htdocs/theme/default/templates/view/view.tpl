{include file="header.tpl"}

{include file="columnfullstart.tpl"}
<div id="view">
	<h3>
        {foreach name=viewnav from=$VIEWNAV item=item}
          {$item}
          {if !$smarty.foreach.viewnav.last}
            :
          {/if}
        {/foreach}
        </h3>
	
            <div id="bottom-pane">
                <div id="column-container">
                    {if $VIEWCONTENT}
                       {$VIEWCONTENT}
                    {/if}
                    <div id="clearer">
                    </div>
                </div>
            </div>
	<div id="publicfeedback">
	<table id="feedbacktable">
		<thead>
			<tr><th colspan=5>{str tag=feedback}</th></tr>
		</thead>
	</table>
	</div>
	<div id="viewmenu"></div>
</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
