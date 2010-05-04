{auto_escape off}
<ul>
{foreach from=$mostrecent item=post}
    <li>
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->id|escape}&amp;view={$view|escape}">{$post->title|escape}</a>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent|escape}&amp;view={$view|escape}">{$post->parenttitle|escape}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        {$post->displaydate}
    </li>
{/foreach}
</ul>
{/auto_escape}
