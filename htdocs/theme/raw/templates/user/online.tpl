{include file="header.tpl"}
<div id="onlinelistcontainer">
    <p>({str tag="lastminutes" args=$lastminutes})</p>
    <div id="onlinelist" class="listing">
    {$data.tablerows|safe}
    </div>
</div>
{$data.pagination|safe}
{if $data.pagination_js}
    <script>
    {$data.pagination_js|safe}
    </script>
{/if}
{include file="footer.tpl"}
