{include file="header.tpl"}
<div id="onlinelistcontainer">
    <p>({str tag="lastminutes" args=$lastminutes})</p>
    <div id="onlinelist" class="fullwidth listing">

{$data.tablerows|safe}

    </div>
</div>
{$data.pagination|safe}
{include file="footer.tpl"}
