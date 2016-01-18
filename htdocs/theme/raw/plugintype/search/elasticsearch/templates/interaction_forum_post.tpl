{if $record->deleted}
    <h3 class="title list-group-item-heading text-inline">
        <span class="icon icon-commenting-o left" role="presentation" aria-hidden="true"></span>
        {$record->subject}
    </h3>
    <span class="artefacttype text-midtone">({str tag=deletedforumpost section=search.elasticsearch})</span>
{else}
    <h3 class="title list-group-item-heading text-inline">
        <span class="icon icon-commenting-o left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}interaction/forum/topic.php?id={$record->topic}#post{$record->id}">
            {$record->subject}
        </a>
    </h3>
    <span class="artefacttype text-midtone">({str tag=forumpost section=search.elasticsearch})</span>
    <div class="source"><strong>{str tag='forum' section='search.elasticsearch'}:</strong> {$record->forumname} ({$record->groupname})</div>
    <div class="poster"><strong>{str tag='forumpostedbylabel' section='search.elasticsearch'}:</strong> {str tag=forumpostedby section=search.elasticsearch arg1='$record->authorlink|safe' arg2='$record->ctime'}</div>
    <div class="detail">{$record->body|str_shorten_html:140:true:false|safe}</div>
{/if}
