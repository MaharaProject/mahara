<div id="recentforumpostsblock" class="forumposts recentforumpostsblock">
    {if $foruminfo}
        <ul class="list-unstyled list-group">
            {foreach from=$foruminfo item=postinfo}
            <li class="list-group-item flush">
                <div class="usericon-heading clearfix">
                    <a href="{profile_url($postinfo->author)}" class="user-icon small-icon">
                        <img src="{profile_icon_url user=$postinfo->author maxheight=40 maxwidth=40}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}" class="pull-left">
                    </a>
                    <h4 class="title list-group-item-heading">
                        <a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic}&post={$postinfo->id}">
                            {$postinfo->topicname}
                            <span class="metadata text-small">
                                - {$postinfo->author|display_name}</span>
                        </a>
                    </h4>
                </div>
                <p class="content-text">{$postinfo->body|str_shorten_html:100:true:true:false|safe}</p>
            </li>
        {/foreach}
        <ul>
    {else}
    <p class="no-results text-small">
        {str tag=noforumpostsyet section=interaction.forum}
    </p>
    {/if}
</div>
<a class="morelink panel-footer text-small" href="{$WWWROOT}interaction/forum/index.php?group={$group->id}">
    {str tag=gotoforums section=interaction.forum}
    <span class="icon icon-arrow-circle-right right pull-right" role="presentation" aria-hidden="true"></span>
</a>
