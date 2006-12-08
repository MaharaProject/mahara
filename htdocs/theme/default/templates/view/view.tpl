{include file="header.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">

{$TITLE}

{if $VIEWCONTENT}
   {$VIEWCONTENT}
{/if}

<table id="feedbacktable">
    <thead>
        <tr><th colspan=4>{str tag=feedback}</th></tr>
    </thead>
</table>

<div id="viewmenu"></div>

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
