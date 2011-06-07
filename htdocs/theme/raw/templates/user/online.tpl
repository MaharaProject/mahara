{include file="header.tpl"}
<div id="onlinelistcontainer">
    <div><p>({str tag="lastminutes" args=$lastminutes})</p></div>
    <table id="onlinelist" class="fullwidth listing">
        <tbody>
{$data.tablerows|safe}
        </tbody>
    </table>
</div>
{$data.pagination|safe}
{include file="footer.tpl"}
