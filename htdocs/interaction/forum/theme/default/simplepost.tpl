<div>
{if $post->subject && !$nosubject}<h4>{$post->subject|escape}</h4>{/if}
{$post->ctime}
<h5><a href="{$WWWROOT}user/view.php?id={$post->poster}">{$post->poster|display_name|escape}</a></h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
<h5>{str tag="postsvariable" section=interaction.forum args=$post->count}</h5>
{$post->body}
</div>