{if $record->deleted}
    <h3 class="title">$record->subject <span class="artefacttype">({str tag=deletedforumpost section=search.elasticsearch})</span></h3>
{else}
    <h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$record->topic}#post{$record->id}">{$record->subject}</a> <span class="artefacttype">({str tag=forumpost section=search.elasticsearch})</span></h3>
    <div class="detail">{$record->body|str_shorten_html:140:true:false|safe}</div>
    <div class="poster"><a href="{profile_url($record->poster)}" class="forumuser">{$record->poster|display_name:null:true}</a></div>
{/if}