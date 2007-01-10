{include file="header.tpl"}

{include file="columnfullstart.tpl"}

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

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
