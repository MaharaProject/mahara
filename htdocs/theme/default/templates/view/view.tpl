{include file="header.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>{$TITLE}</h2>

{if $VIEWCONTENT}
   {$VIEWCONTENT}
{else}
   {str tag=viewviewnotallowed}
{/if}

<table id="feedbacktable">
    <thead>
        <tr><th colspan=3>{str tag=feedback}</th></tr>
        <tr><th>{str tag=name}</th><th>{str tag=date}</th><th>{str tag=message}</th></tr>
    </thead>
</table>

<div id="viewmenu"></div>

	</div>
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
