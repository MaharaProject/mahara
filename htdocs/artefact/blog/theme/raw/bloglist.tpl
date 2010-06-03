{auto_escape off}
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
        <div><strong><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->title|escape}</a></strong></div>
        <div>{$blog->description|clean_html}</div>
      </td>
      <td class="right">
        <a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->postcount}</a>
        <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->id}" class="btn-settings">{str tag=settings}</a>
        <a onClick="confirmdelete({$blog->id});" class="btn-del">{str tag=delete}</a>
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn-add">{str tag=addpost section=artefact.blog}</a>
      </td>
    </tr>
  {/foreach}
{/auto_escape}
