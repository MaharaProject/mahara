<div class="blockinstance-content">
{if $tasks.data}
<table id="tasktable_{$blockid}" class="plansblocktable">
    <colgroup width="50%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="c1">{str tag='completiondate' section='artefact.plans'}</th>
            <th class="c2">{str tag='title' section='artefact.plans'}</th>
            <th class="c3">{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
    {$tasks.tablerows|safe}
    </tbody>
</table>
{if $tasks.pagination}
<div id="plans_page_container">{$tasks.pagination|safe}</div>
{/if}
{if $tasks.pagination_js}
<script>
{literal}
function rewriteTaskTitles() {
    forEach(
{/literal}
        getElementsByTagAndClassName('a', 'task-title','tasktable_{$blockid}'),
{literal}
        function(element) {
            connect(element, 'onclick', function(e) {
                e.stop();
                var description = getFirstElementByTagAndClassName('div', 'task-desc', element.parentNode);
                toggleElementClass('hidden', description);
            });
        }
    );
}

addLoadEvent(function() {{/literal}
    {$tasks.pagination_js|safe}
    removeElementClass('plans_page_container', 'hidden');
{literal}}{/literal});

function TaskPager_{$blockid}() {literal}{
    var self = this;
    paginatorProxy.addObserver(self);
    connect(self, 'pagechanged', rewriteTaskTitles);
}
{/literal}
var taskPager_{$blockid} = new TaskPager_{$blockid}();
addLoadEvent(rewriteTaskTitles);
</script>
{/if} {* pagination_js *}
{else}
    <p>{str tag='notasks' section='artefact.plans'}</p>
{/if}
</div>
