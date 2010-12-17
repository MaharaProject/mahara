<table id="tasktable">
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
<div id="plans_page_container">{$tasks.pagination|safe}</div>
<script>
{literal}
function rewriteTaskTitles() {
    forEach(
        getElementsByTagAndClassName('a', 'task-title','tasktable'),
        function(element) {
            connect(element, 'onclick', function(e) {
                e.stop();
                var description = getFirstElementByTagAndClassName('div', 'task-desc', element.parentNode);
                toggleElementClass('hidden', description);
            });
        }
    );
}

addLoadEvent(function() {
    {/literal}{$tasks.pagination_js|safe}{literal}
    removeElementClass('plans_page_container', 'hidden');
});

function TaskPager() {
    var self = this;
    paginatorProxy.addObserver(self);
    connect(self, 'pagechanged', rewriteTaskTitles);
}
var taskPager = new TaskPager();
addLoadEvent(rewriteTaskTitles);
{/literal}
</script>
