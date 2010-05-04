{auto_escape off}
{include file="header.tpl"}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description}</div>
{/if}

<div class="group-info">
  <div class="fr">
  {include file="group/groupuserstatus.tpl" group=$group returnto='view'}
  </div>
  {include file="group/info.tpl"}
</div>

{if $group->public || $role}
    <h3>{str tag=latestforumposts section=interaction.forum}</h3>
    {if $foruminfo}
        <table class="fullwidth s" id="groupforumtable">
        {foreach from=$foruminfo item=postinfo}
            <tr class="{cycle values='r0,r1'}">
                <td><strong><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}#post{$postinfo->id|escape}">{$postinfo->topicname|escape}</a></strong></td>
                <td>{$postinfo->body|str_shorten_html:100:true}</td>
                <td><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$postinfo->poster|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$postinfo->poster|escape}">{$postinfo->poster|display_name|escape}</a>
                </td>
            </tr>
        {/foreach}
        </table>
    {else}
        <p>{str tag=noforumpostsyet section=interaction.forum}</p>
    {/if}
    <div class="right"><a href="{$WWWROOT}interaction/forum/?group={$group->id|escape}"><strong>{str tag=gotoforums section=interaction.forum} &raquo;</strong></a></div>
{/if}

{if $sharedviews}
    <h3>{str tag="viewssharedtogroupbyothers" section="view"}</h3>
    <p>
    <table class="fullwidth">
    {foreach from=$sharedviews item=view}
        <tr class="{cycle values='r0,r1'}">
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
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
                {if $view.template}
                <div><a href="">{str tag=copythisview section=view}</a></div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}

{if $mysubmittedviews || $group_view_submission_form}
    <h3>{str tag="submitaviewtogroup" section="view"}</h3>
    {if $mysubmittedviews}
      {foreach from=$mysubmittedviews item=view}
        <div>{$view.strsubmitted}</div>
      {/foreach}
    {/if}
    {if $group_view_submission_form}
        <div>{$group_view_submission_form}</div>
    {/if}
{/if}

{if $allsubmittedviews}
    <h3>{str tag="viewssubmittedtogroup" section="view"}</h3>
    <p>
    <table class="fullwidth">
    {foreach from=$allsubmittedviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=view}
                    <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
                {/if}
                {if $view.submittedtime}
                    <span> ({str tag=timeofsubmission section=view}: {$view.submittedtime|format_date})</span>
                {/if}
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}

{include file="footer.tpl"}
{/auto_escape}
