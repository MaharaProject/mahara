{if $post->subject}<h4>{$post->subject|escape}</h4>{/if}
{$post->ctime}
<h5>{$post->poster|display_name|escape}</h5>
<h5>{str tag="posts" section=interaction.forum} {$post->count|escape}</h5>
<div><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$post->poster}" alt=""></div>
{$post->body}