{foreach from=$posts item=post}
<tr id="posttitle_{$post->id}">
  <th class="posttitle">{$post->title}</th>
  <th id="poststatus{$post->id}" class="poststatus">
    {if $post->published}
      {str tag=published section=artefact.blog}
    {else}
      {str tag=draft section=artefact.blog}
      {if !$post->locked && !$post->published}&nbsp;{$post->publish|safe}{/if}
    {/if}
  </th>
  <th class="controls">
    {if $post->locked}
      {str tag=submittedforassessment section=view}
    {else}
      <form name="edit_{$post->id}" action="{$WWWROOT}artefact/blog/post.php">
        <input type="hidden" name="id" value="{$post->id}">
        <input type="image" src="{theme_url filename="images/edit.gif"}" title="{str tag=edit}">
      </form>
      {$post->delete|safe}
    {/if}
  </th>
</tr>
<tr id="postdescription_{$post->id}">
  <td colspan=3>{$post->description|clean_html|safe}</td>
</tr>
{if $post->files}
<tr id="postfiles_{$post->id}">
  <td colspan=3>
    <table class="attachments fullwidth">
      <col width="5%">
      <col width="40%">
      <col width="55%">
      <tbody>
        <tr><th colspan=3>{str tag=attachedfiles section=artefact.blog}</th></tr>
      {foreach from=$post->files item=file}
        <tr class="{cycle values='r1,r0'}">
          <td><img src="{$file->icon}" alt=""></td>
          <td class="valign"><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a></td>
          <td class="valign">{$file->description}</td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  </td>
</tr>
{/if}
<tr id="postdetails_{$post->id}"><td colspan=2 class="postdetails">{str tag=postedon section=artefact.blog} {$post->ctime}</td></tr>
{/foreach}