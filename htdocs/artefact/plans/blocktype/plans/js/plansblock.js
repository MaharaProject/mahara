function rewriteTaskTitles(blockid) {
  jQuery('tasktable_' + blockid + ' a.task-title').each(function() {
      jQuery(this).off();
      jQuery(this).on('click', function(e) {
          e.preventDefault();
          var description = jQuery(this).parent().find('div.task-desc');
          description.toggleClass('hidden');
      });
  });
}
function TaskPager(blockid) {
    var self = this;
    paginatorProxy.addObserver(self);
    jQuery(self).on('pagechanged', rewriteTaskTitles.bind(null, blockid));
}

var taskPagers = [];

function initNewPlansBlock(blockid) {
    if (jQuery('#plans_page_container_' + blockid)) {
        new Paginator('block' + blockid + '_pagination', 'tasktable_' + blockid, null, 'artefact/plans/viewtasks.json.php', null);
        taskPagers.push(new TaskPager(blockid));
    }
    rewriteTaskTitles(blockid);
}
