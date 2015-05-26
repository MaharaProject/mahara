{foreach from=$topics item=topic}
    <tr>
        <td>
            <h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">{$topic->topicname|str_shorten_text:65:true}</a></h3>
            <div class="forumpath text-small pts">
                <a href="{$topic->groupurl}" class="topicgroup">{$topic->groupname|str_shorten_text:30:true}</a> >
                <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}" class="topicforum">{$topic->forumname|str_shorten_text:30:true}</a>
            </div>
        </td>
        <td class="text-center">{$topic->postcount}</td>
        <td>
            <p class="postdetail text-muted">
                {$topic->body|str_shorten_html:80:true|strip_tags|safe}
            </p>
            
            <span class="poster">
                <a href="{profile_url($topic->poster)}">
                    {$topic->poster|display_name}
                </a>
                - {$topic->ctime|strtotime|format_date:'strftimedatetime'}
            </span>
        </td>
    </tr>
{/foreach}
