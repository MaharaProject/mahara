{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

{include file="group/tabstart.tpl" current="info"}

                {if $group->description}<p id="group-description">{$group->description}</p> {/if}

                <ul id="group-info">
                    <li><strong>{str tag=groupadmins section=group}:</strong> {foreach name=admins from=$group->admins item=id}
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$id|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$smarty.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    <li><strong>{str tag=Created section=group}:</strong> {$group->ctime}</li>
                    <li><strong>{str tag=Members section=group}:</strong> {$membercount}&nbsp;
                        <strong>{str tag=Views section=view}:</strong> {$viewcount}&nbsp;
                        <strong>{str tag=Files section=artefact.file}:</strong> {$filecount}&nbsp;
                        <strong>{str tag=Folders section=artefact.file}:</strong> {$foldercount}</li>
                </ul>
                <ul id="group-controls">
                    {include file="group/groupuserstatus.tpl" group=$group returnto='view'}
                </ul><br style="clear: left;"/>

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

{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
