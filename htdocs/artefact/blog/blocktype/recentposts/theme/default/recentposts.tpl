<ul>
{foreach from=$mostrecent item=post}
    <li>
        <a href="{$WWWROOT}view/view.php?view={$view}&artefact={$post->id}">{$post->title|escape}</a>
        {str tag='postedin' section='blocktype.blog/recentposts'} 
        <a href="{$WWWROOT}view/view.php?view={$view}&artefact={$post->parent}">{$post->parenttitle|escape}</a>
        {str tag='postedon' section='blocktype.blog/recentposts'}
        {$post->displaydate}
    </li>
{/foreach}
</ul>
