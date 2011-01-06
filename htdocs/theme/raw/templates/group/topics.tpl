{include file="header.tpl"}
<p>{str tag=activetopicsdescription section=interaction.forum}</p>
<table class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=Topic section=interaction.forum}</th>
      <th class="center">{str tag=Posts section=interaction.forum}</th>
      <th>{str tag=lastpost section=interaction.forum}</th>
    </tr>
  </thead>
  <tbody>
{foreach from=$topics item=topic}
    <tr class="{cycle values='r0,r1'}">
      <td>
        <div><strong><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id}">{$topic->topicname|str_shorten_text:65:true}</a></strong></div>
        <div>
          <a href="{$WWWROOT}group/view.php?id={$topic->groupid}">{$topic->groupname|str_shorten_text:30:true}</a>:
          <a href="{$WWWROOT}interaction/forum/view.php?id={$topic->forumid}">{$topic->forumname|str_shorten_text:30:true}</a>
        </div>
      </td>
      <td class="center">{$topic->postcount}</td>
      <td>
        <div>{$topic->body|str_shorten_html:80:true|strip_tags|safe}</div>
        <a href="{$WWWROOT}user/view.php?id={$topic->poster}">{$topic->poster|display_name|escape}</a>
        <span class="postedon nowrap">{$topic->ctime|strtotime|format_date:'strftimerecent'}</span>
      </td>
    </tr>
{/foreach}
  </tbody>
</table>
{$pagination|safe}
{include file="footer.tpl"}
