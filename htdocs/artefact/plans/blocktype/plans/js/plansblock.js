function rewriteTaskTitles(blockid) {
    forEach(
        getElementsByTagAndClassName('a', 'task-title', 'tasktable_' + blockid),
        function(element) {
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
