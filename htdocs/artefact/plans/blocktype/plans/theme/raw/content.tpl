<div class="blockinstance-content">
{if $plans.data}
<table id="planstable_{$blockid}">
    <colgroup width="50%" span="2"></colgroup>
    <thead>
        <tr>
            <th>{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
    {$plans.tablerows|safe}
    </tbody>
</table>
<div id="plans_page_container">{$plans.pagination|safe}</div>
<script>
addLoadEvent(function() {literal}{{/literal}
    {$plans.pagination_js|safe}
    removeElementClass('plans_page_container', 'hidden');
{literal}}{/literal});
</script>
{else}
    <p>{str tag='noplans' section='artefact.plans'}</p>
{/if}
</div>

