{if $configerror}
    {str tag='configerror' section='blocktype.blog/taggedposts'}
{elseif $badtag}
    {str tag='notags' section='blocktype.blog/taggedposts' arg1=$badtag}
{else}
    {str tag='blockheading' section='blocktype.blog/taggedposts'}
    {if $viewowner}
    {$viewowner = $viewowner[0]}
        <strong>{$tag}</strong> by <strong><a href="{$WWWROOT}user/view.php?id={$viewowner->id}">{$viewowner->firstname} {$viewowner->lastname}</a></strong>
    {else}
        <strong><a href="{$WWWROOT}tags.php?tag={$tag}&sort=name&type=text">{$tag}</a></strong>
    {/if}
    <ul class="taggedposts">
    {foreach from=$results item=post}
        <li>
            <strong><a href="{$WWWROOT}view/artefact.php?artefact={$post->id}&view={$view}">{$post->title}</a></strong>
            {str tag='postedin' section='blocktype.blog/taggedposts'}
            {if $viewowner}
                {$post->parenttitle}
            {else}
                <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&view={$view}">{$post->parenttitle}</a>
            {/if}
        </li>
    {/foreach}
    </ul>
{/if}
