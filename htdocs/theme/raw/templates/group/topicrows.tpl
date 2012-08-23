{foreach from=$topics item=topic}
    <tr class="{cycle values='r0,r1'}">
      <td>
        <h4><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">{$topic->topicname|str_shorten_text:65:true}</a></h4>
        <div class="s">
          <a href="{$topic->groupurl}" class="topicgroup">{$topic->groupname|str_shorten_text:30:true}</a> >
          <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}" class="topicforum">{$topic->forumname|str_shorten_text:30:true}</a>
        </div>
      </td>
      <td class="center">{$topic->postcount}</td>
      <td>
        <div class="s">"{$topic->body|str_shorten_html:80:true|strip_tags|safe}"</div>
        <span class="s poster"><a href="{profile_url($topic->poster)}">{$topic->poster|display_name}</a> - </span>
        <span class="postedon nowrap">{$topic->ctime|strtotime|format_date:'strftimerecent'}</span>
      </td>
    </tr>
{/foreach}
