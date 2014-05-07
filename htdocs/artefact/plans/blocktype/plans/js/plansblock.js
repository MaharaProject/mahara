function rewriteTaskTitles(blockid) {
    forEach(
        getElementsByTagAndClassName('a', 'task-title', 'tasktable_' + blockid),
        function(element) {
            disconnectAll(element);
            connect(element, 'onclick', function(e) {
                e.stop();
                var description = getFirstElementByTagAndClassName('div', 'task-desc', element.parentNode);
                toggleElementClass('hidden', description);
            });
        }
    );
}
function TaskPager(blockid) {
    var self = this;
    paginatorProxy.addObserver(self);
    connect(self, 'pagechanged', partial(rewriteTaskTitles, blockid));
}

var taskPagers = [];

function initNewPlansBlock(blockid) {
    if ($('plans_page_container_' + blockid)) {
        new Paginator('block' + blockid + '_pagination', 'tasktable_' + blockid, null, 'artefact/plans/viewtasks.json.php', null);
        taskPagers.push(new TaskPager(blockid));
    }
    rewriteTaskTitles(blockid);
}
