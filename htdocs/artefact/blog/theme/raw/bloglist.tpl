{if empty($blogs->data)}
<div>{str tag=youhavenoblogs section=artefact.blog}</div>
{else}
<table class="tablerenderer fullwidth">
  <thead>
    <tr>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$blogs->data item=blog}
    <tr class="{cycle name=rows values=r1,r0}">
      <td>
        <div><strong><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->title|escape}</a></strong></div>
        <div>{$blog->description|clean_html}</div>
      </td>
      <td class="right">
        <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->id}" class="btn-settings">{str tag=settings}</a>
        <a href="{$WWWROOT}artefact/blog/index.php?delete={$blog->id}" class="btn-del">{str tag=delete}</a>
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn-add">{str tag=addpost section=artefact.blog}</a>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}
