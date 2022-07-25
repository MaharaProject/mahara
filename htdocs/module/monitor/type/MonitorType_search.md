# Module Type: Search

The new Search Monitor Type takes into account that search is itself modular and has plugins.  This will allow future search plugins to work without needing to update the Monitor module.

## HOWTO

To enable your search plugin to be monitored, implement the following methods within it.

### monitor_support()
The mere presence of this method lets the Search Monitor know monitoring is available.

## monitor_task_list()
This returns an array of callback methods, referred to as 'tasks', that present a line each on the report table.

## monitor_title()
The title for this monitor. This appears above the table of task results.

## monitor_subnav_title()
This appears on the page H1 and the Monitor Sub Nav.

## The task callbacks
These are the method names listed in `monitor_task_list()`. While they can be named anything at all I suggest the prefix `monitor_` so they are easily identified.

Each of these return a keyed array of the following form:
```php
return [
  'title' => 'what the task is',
  'value' => 'the state of the task',
];
```
# Required methods
The CLI/API expects a minimum of 3 tasks. While more can be presented on the web UI for the Monitor module these are the ones required for automated monitoring.

## Monitor Tasks

### monitor_get_failed_queue_size()
Reports on records in a failed status state. The `value` should be numeric.

### monitor_is_queue_older_than()
Take a min record ID which has status = 0 and time seen. If, after a refresh, the ID is still there and it has been for longer than the configured time `status` is toggled to `true` to raise the alarm.

In addition to the standard `task` and `value` keys this method also returns `status` (raises the alarm) and `hours` (used in the notice returned to the user).

## CLI strings
There are 3 main messages we need text for.  The Monitor module will prefix these with the status of the result, so there is no need to include that.

### monitor_get_failed_queue_size_message()
Message to display the queue size check fails.

### monitor_is_queue_older_than_message()
Message to display if the queue has old items in it. This should return an indicator of the max age of an item in the queue.

e.g. There are items older than 2 hours.

### monitor_checking_search_succeeded_message()
Message to display if the search queue is in a good state.
