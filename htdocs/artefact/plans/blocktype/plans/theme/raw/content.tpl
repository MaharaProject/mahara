<div class="blockinstance-content">
{if $tasks.data}
<table id="taskstable_{$blockid}">
    <colgroup width="50%" span="2"></colgroup>
    <thead>
        <tr>
            <th>{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
    {$tasks.tablerows|safe}
    </tbody>
</table>
<div id="plans_page_container">{$tasks.pagination|safe}</div>
<script>
addLoadEvent(function() {literal}{{/literal}
    {$tasks.pagination_js|safe}
    removeElementClass('plans_page_container', 'hidden');
{literal}}{/literal});
</script>
{else}
    <p>{str tag='notasks' section='artefact.plans'}</p>
{/if}
</div>

