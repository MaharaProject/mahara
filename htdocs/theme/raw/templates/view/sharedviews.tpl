{include file="header.tpl"}
<p>{str tag=sharedviewsdescription section=view}</p>
<div>{$searchform|safe}</div>
<table class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=name}</th>
      <th class="center">{str tag=Comments section=artefact.comment}</th>
      <th class="center">{str tag=lastcomment section=artefact.comment}</th>
    </tr>
  </thead>
  <tbody>
{foreach from=$views item=view}
    <tr class="{cycle values='r0,r1'}">
      <td>
        <div><strong><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|str_shorten_text:65:true}</a></strong></div>
        {if $view.sharedby}
        <div>
          {if $view.group}
            <a href="{$WWWROOT}group/view.php?id={$view.group}" class="s">{$view.sharedby}</a>
          {elseif $view.owner}
            <a href="{$WWWROOT}user/view.php?id={$view.owner}" class="s">{$view.sharedby}</a>
          {else}
            {$view.sharedby}
          {/if}
          <span class="postedon nowrap">{$view.mtime|strtotime|format_date:'strftimerecent'}</span>
        </div>
        {/if}
        <div>{$view.description|str_shorten_html:70:true|strip_tags|safe}</div>
        {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
      </td>
      <td class="center">{$view.commentcount}</td>
      <td>
        {if $view.commenttext}
            <a href="{$WWWROOT}view/view.php?id={$view.id}&showcomment={$view.commentid}" class="fr" title="{str tag=viewcomment section=artefact.comment}"><img src="{theme_url filename="images/icon-reply.gif"}" alt="{str tag=viewcomment section=artefact.comment}" /></a>
            <div>{$view.commenttext|str_shorten_html:40:true|strip_tags|safe}</div>
          {if $view.commentauthor}
            <a href="{$WWWROOT}user/view.php?id={$view.commentauthor}" class="s">{$view.commentauthor|display_name|escape}</a>
          {else}
            {$view.commentauthorname}
          {/if}
            <span class="postedon nowrap">{$view.lastcommenttime|strtotime|format_date:'strftimerecent'}</span>
        {/if}
      </td>
    </tr>
{/foreach}
  </tbody>
</table>
{$pagination|safe}
{include file="footer.tpl"}
