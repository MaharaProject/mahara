<script type="text/javascript">
    function confirmdelete(id) {
        if(confirm("{str tag=deleteblog? section=artefact.blog}")) {
            window.location = "{$WWWROOT}artefact/blog/index.php?delete=" + id;
        }
    }
</script>
  {foreach from=$blogs->data item=blog}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td>
        <div><strong><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->title}</a></strong></div>
        <div>{$blog->description|clean_html|safe}</div>
      </td>
      <td class="right">
        {if $blog->locked}
        <span class="s dull">{str tag=submittedforassessment section=view}</span>
        {else}
        <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->id}" class="btn-settings">{str tag=settings}</a>
        <a onClick="confirmdelete({$blog->id});" class="btn-del">{str tag=delete}</a>
        {/if}
        <div class="fr postcontrols">
        <a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->postcount} {if $blog->postcount == 1}{str tag=post section=artefact.blog}{else}{str tag=posts section=artefact.blog}{/if}</a>
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn-add">{str tag=addpost section=artefact.blog}</a>
        </div>
      </td>
    </tr>
  {/foreach}
