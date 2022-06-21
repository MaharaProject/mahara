{if $record->deleted}
    <h2 class="list-group-item-heading text-inline">
        <span class="icon icon-regular icon-comment-dots left" role="presentation" aria-hidden="true"></span>
        {$record->subject}
    </h2>
    <span class="artefacttype text-midtone">({str tag=deletedforumpost section=search.elasticsearch7})</span>
{else}
    <h2 class="title list-group-item-heading text-inline">
        <span class="icon icon-regular icon-comment-dots left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}interaction/forum/topic.php?id={$record->topic}#post{$record->id}">
            {$record->subject}
        </a>
    </h2>
    <span class="artefacttype text-midtone">({str tag=forumpost section=search.elasticsearch7})</span>
    <div class="source">{str tag='forum' section='search.elasticsearch7'}: {$record->forumname} ({$record->groupname})</div>
    <div class="poster">{str tag='forumpostedbylabel' section='search.elasticsearch7'}: {str tag=forumpostedby section=search.elasticsearch7 arg1='$record->authorlink|safe' arg2='$record->ctime'}</div>
    <div class="detail">{$record->body|str_shorten_html:140:true:false|safe}</div>
{/if}
