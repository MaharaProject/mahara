{if $post->id}<a name="post{$post->id}"></a>{/if}


<table class="forumpost fullwidth {if $highlightreported}reported{/if}">
<tr>
	<td class="forumpostleft">
      <div class="author">
         <img src="{profile_icon_url user=$post->poster maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$post->poster|display_default_name}" class="center">
         <div class="poster"><a href="{profile_url($post->poster)}"{if in_array($post->poster, $groupadmins)} class="groupadmin"{elseif $post->moderator} class="moderator"{/if}>{$post->poster|display_name}</a></div>
         {if $post->postcount}<div class="postcount">{$post->postcount}</div>{/if}
      </div>
    </td>
    <td class="postedits">
    {if $post->subject && !$nosubject}
        <div class="forumsubject"><h3 class="title">{if $post->id}<a href="#post{$post->id}">{$post->subject}</a>{else}{$post->subject}{/if}</h3></div>
    {/if}
    <div class="posttime">{$post->ctime}</div>{$post->body|clean_html|safe}
{if $post->edit}
        <div class="editstopost"><h4 class="title">{str tag="editstothispost" section="interaction.forum"}</h4>
        <ul>
            {foreach from=$post->edit item=edit}
            <li>
                <a href="{profile_url($edit.editor)}"
                {if $edit.editor == $groupowner} class="groupowner"
                {elseif $edit.moderator} class="moderator"
                {/if}
                >
                <img src="{profile_icon_url user=$edit.editor maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$edit.editor|display_default_name}">
                {$edit.editor|display_name}
                </a> - <span class="posttime">{$edit.edittime}</span>
            </li>
            {/foreach}
        </ul>
        </div>
{/if}
    </td>
</tr>
</table>
