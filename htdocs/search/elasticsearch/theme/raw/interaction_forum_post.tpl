{if $record->deleted}
    <h3 class="title">$record->subject <span class="artefacttype">({str tag=deletedforumpost section=search.elasticsearch})</span></h3>
{else}
    <h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$record->topic}#post{$record->id}">{$record->subject}</a> <span class="artefacttype">({str tag=forumpost section=search.elasticsearch})</span></h3>
    <div class="source"><label>{str tag='forum' section='search.elasticsearch'}:</label> {$record->forumname} ({$record->groupname})</div>
    <div class="poster"><label>{str tag='forumpostedbylabel' section='search.elasticsearch'}:</label> {str tag=forumpostedby section=search.elasticsearch arg1='$record->authorlink|safe' arg2='$record->ctime'}</div>
    <div class="detail">{$record->body|str_shorten_html:140:true:false|safe}</div>
{/if}