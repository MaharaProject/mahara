<a name="post{$post->id}"></a>
<div>


<table id="forumpost">
{if $post->subject && !$nosubject}
<tr>
	<th colspan="2"><h4>{$post->subject|escape}</h4></th>
</tr>
{/if}
<tr>
	<td class="forumpostleft">
	<div class="posttime">{$post->ctime}</div>
	<h5><a href="{$WWWROOT}user/view.php?id={$post->poster}"
{if in_array($post->poster, $groupadmins)} class="groupadmin"
{elseif $post->moderator} class="moderator"
{/if}
>
{$post->poster|display_name|escape}</a></h5>
	<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
	<h5>{$post->postcount}</h5></td>
	<td>{$post->body}</td>
</tr>
</table>
