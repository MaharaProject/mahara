{if $post->id}<a name="post{$post->id}"></a>{/if}


<table class="forumpost fullwidth">
<tr>
	<td class="forumpostleft">
      <div class="author">
         <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$post->poster}" alt="" class="center">
         <div class="poster"><a href="{profile_url($post->poster)}"{if in_array($post->poster, $groupadmins)} class="groupadmin"{elseif $post->moderator} class="moderator"{/if}>{$post->poster|display_name}</a></div>
         {if $post->postcount}<div class="postcount">{$post->postcount}</div>{/if}
      </div>
    </td>
    <td class="postedits">
    {if $post->subject && !$nosubject}
        <div class="forumsubject"><h5>{if $post->id}<a href="#post{$post->id}">{$post->subject}</a>{else}{$post->subject}{/if}</h5></div>
    {/if}
    <div class="posttime">{$post->ctime}</div>{$post->body|clean_html|safe}
{if $post->edit}
        <div class="editstopost"><h5>{str tag="editstothispost" section="interaction.forum"}</h5>
        <ul>
            {foreach from=$post->edit item=edit}
            <li>
                <a href="{profile_url($edit.editor)}"
                {if $edit.editor == $groupowner} class="groupowner"
                {elseif $edit.moderator} class="moderator"
                {/if}
                >
                <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$edit.editor}" alt="">
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
