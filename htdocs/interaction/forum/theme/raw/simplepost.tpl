{if $post->id}<a name="post{$post->id}"></a>{/if}


<table class="forumpost fullwidth">
{if $post->subject && !$nosubject}
<tr>
	<td colspan="2" class="forumsubject"><h6>{if $post->id}<a href="#post{$post->id}">{$post->subject}</a>{else}{$post->subject}{/if}</h6></td>
</tr>
{/if}
<tr>
	<td class="forumpostleft">
      <div class="author">
         <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$post->poster}" alt="" class="center">
         <div class="poster"><a href="{$WWWROOT}user/view.php?id={$post->poster}"{if in_array($post->poster, $groupadmins)} class="groupadmin"{elseif $post->moderator} class="moderator"{/if}>{$post->poster|display_name}</a></div>
         {if $post->postcount}<div class="postcount">{$post->postcount}</div>{/if}
      </div>
    </td>
	<td class="postedits">
	  <div class="posttime">{$post->ctime}</div>{$post->body|clean_html|safe}
{if $post->edit}
        <h5>{str tag="editstothispost" section="interaction.forum"}</h5>
        <ul>
            {foreach from=$post->edit item=edit}
            <li>
                <a href="{$WWWROOT}user/view.php?id={$edit.editor}"
                {if $edit.editor == $groupowner} class="groupowner"
                {elseif $edit.moderator} class="moderator"
                {/if}
                >
                <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$edit.editor}" alt="">
                {$edit.editor|display_name}
                </a>
                {$edit.edittime}
            </li>
            {/foreach}
        </ul>
{/if}
    </td>
</tr>
</table>
