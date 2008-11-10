{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                {if $GROUP->description}<p id="group-description">{$GROUP->description}</p> {/if}

                {include file="group/info.tpl"}
                <ul id="group-controls">
                    {include file="group/groupuserstatus.tpl" group=$group returnto='view'}
                </ul><br style="clear: left;"/>

                {if $group->public || $role}
                <div class="group-info-para">
                <h3>{str tag=latestforumposts section=interaction.forum}</h3>
                {if $foruminfo}
                {foreach from=$foruminfo item=postinfo}
                <div>
                  <h4><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}#post{$postinfo->id|escape}">{$postinfo->topicname|escape}</a></h4>
                  <div>
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$postinfo->poster|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$postinfo->poster|escape}">{$postinfo->poster|display_name|escape}</a>
                  </div>
                  <p>{$postinfo->body|str_shorten:100:true}</p>
                </div>
                {/foreach}
                {else}
                <p>{str tag=noforumpostsyet section=interaction.forum}</p>
                {/if}
                <p><a href="{$WWWROOT}interaction/forum/?group={$group->id|escape}">{str tag=gotoforums section=interaction.forum} &raquo;</a></p>
                </div>
                {/if}

{if $sharedviews}
    <div class="group-info-para">
    <h3>{str tag="viewssharedtogroupbyothers" section="view"}</h3>
    <table class="groupviews">
    {foreach from=$sharedviews item=view}
        <tr class="r{cycle values=0,1}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=view}
                    {if $view.group}
                        <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}
                <div>{$view.shortdescription}</div>
                {if $view.template}
                <div><a href="">{str tag=copythisview section=view}</a></div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
    <div class="center">{$pagination}</div>
    </div>
{/if}

{if $submittedviews}
    <div class="group-info-para">
    <h3>{str tag="viewssubmittedtogroup" section="view"}</h3>
    <table class="groupviews">
    {foreach from=$submittedviews item=view}
        <tr class="r{cycle values=0,1}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=view}
                    {if $view.group}
                        <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}
                <div>{$view.shortdescription}</div>
            </td>
        </tr>
    {/foreach}
    </table>
    <div class="center">{$pagination}</div>
    </div>
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
