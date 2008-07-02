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
                    {if $strgroupviews}<li>{$strgroupviews}</li>{/if}
                    {if $strcontent}<li>{$strcontent}</li>{/if}
                </ul>

                <table>
                    <tr>
                        <td>
                            <h3>{str tag=Members section=group}</h3>
                            <form action="{$WWWROOT}group/view.php" method="post">
                                <input type="hidden" id="groupid" name="id" value="{$group->id|escape}">
                                <div class="searchform center" style="margin-bottom: .5em;">
                                    <label>{str tag='Query' section='admin'}:
                                        <input type="text" name="query" id="query" value="{$query|escape}">
                                    </label>
                                    <button id="query-button" type="submit">{str tag="go"}</button>
                                </div>
                                <div id="results">
                                    <table id="membersearchresults" class="tablerenderer">
                                        <tbody>
                                        {$results}
                                        </tbody>
                                    </table>
                                </div>
                                {$pagination}
                                <script type="text/javascript">{$pagination_js}</script>
                            </form>
                        </td>
                        <td>
                            <h3>{str tag=latestforumposts section=interaction.forum}</h3>
                            {if $foruminfo}
                            {foreach from=$foruminfo item=postinfo}
                            <div>
                              <h4><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}#post{$postinfo->id|escape}">{$postinfo->topicname|escape}</a></h4>
                              <div><a href="{$WWWROOT}user/view.php?id={$postinfo->poster|escape}">{$postinfo->poster|display_name|escape}</a></div>
                              <p>{$postinfo->body|str_shorten:100:true}</p>
                            </div>
                            {/foreach}
                            {else}
                            <p>{str tag=noforumpostsyet section=interaction.forum}</p>
                            {/if}
                            <p><a href="{$WWWROOT}interaction/forum/?group={$group->id|escape}">{str tag=gotoforums section=interaction.forum} &raquo;</a></p>
                        </td>
                    </tr>
                </table>

{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
