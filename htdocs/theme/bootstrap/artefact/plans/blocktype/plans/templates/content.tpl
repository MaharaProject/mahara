{if $tags}<p class="tags s"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>{/if}
{if $tasks.data}
<table id="tasktable_{$blockid}" class="plansblocktable fullwidth">
    <thead>
        <tr>
            <th class="c1">{str tag='completiondate' section='artefact.plans'}</th>
            <th class="c2">{str tag='title' section='artefact.plans'}</th>
            <th class="c3 center">{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
    {$tasks.tablerows|safe}
    </tbody>
</table>
{if $tasks.pagination}
<div id="plans_page_container_{$blockid}" class="nojs-hidden-block">{$tasks.pagination|safe}</div>
{/if}
{else}
    <p>{str tag='notasks' section='artefact.plans'}</p>
{/if}
