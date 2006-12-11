{include file="header.tpl"}

<div id="column-left-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">

{$TITLE}

{if $VIEWCONTENT}
   {$VIEWCONTENT}
{else}
   {str tag=viewviewnotallowed}
{/if}

<table id="feedbacktable">
    <thead>
        <tr><th colspan=3>{str tag=feedback}</th></tr>
    </thead>
</table>

<div id="viewmenu"></div>

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
