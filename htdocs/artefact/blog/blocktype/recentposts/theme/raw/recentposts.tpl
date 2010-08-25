<ul class="recentblogpost">
{foreach from=$mostrecent item=post}
    <li>
        <strong><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&amp;view={$view}">{$post->title}</a></strong>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&amp;view={$view}">{$post->parenttitle}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        <span class="description">{$post->displaydate}</span>
    </li>
{/foreach}
</ul>
