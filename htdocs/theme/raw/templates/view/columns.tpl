{include file="header.tpl"}

<h1>{$viewtitle}</h1>

{include file="view/editviewtabs.tpl" selected='layout'}
<div class="subpage rel">

        <p>{str tag='viewcolumnspagedescription' section='view'}</p>

        {$form|safe}

</div>

{include file="footer.tpl"}
