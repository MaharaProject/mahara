<ul>
{foreach from=$mostrecent item=post}
    <li>
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&amp;view={$view}">{$post->title}</a>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&amp;view={$view}">{$post->parenttitle}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        {$post->displaydate}
    </li>
{/foreach}
</ul>
