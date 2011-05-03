  {foreach from=$blogs->data item=blog}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td>
        <h4><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->title}</a></h4>
        <div id="blogdesc">{$blog->description|clean_html|safe}</div>
      </td>
      <td class="valign"><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->postcount} {if $blog->postcount == 1}{str tag=post section=artefact.blog}{else}{str tag=posts section=artefact.blog}{/if}</a></td>
      <td class="valign s"><a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn">{str tag=addpost section=artefact.blog}</a></td>
      <td class="valign buttonscell right">
        {if $blog->locked}
        	<span class="s dull">{str tag=submittedforassessment section=view}</span>
        {else}
        	<a href="{$WWWROOT}artefact/blog/settings/?id={$blog->id}" title="{str tag=settings}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=settings}"></a>
            {$blog->deleteform|safe}
        {/if}
      </td>
    </tr>
  {/foreach}
