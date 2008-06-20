{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

                <div style="border: 1px solid #006600;">
                
                {if $group->description} <p>{$group->description}</p> {/if}

                <ul>
                    <li>{str tag=groupadmins section=group}: {foreach name=admins from=$group->admins item=id}
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$id|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$smarty.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    <li>{str tag=created section=group}: {$group->ctime}</li>
                    {if $strgroupviews}<li>{$strgroupviews}</li>{/if}
                    {if $strcontent}<li>{$strcontent}</li>{/if}
                </ul>

                <table>
                    <tr>
                        <td>
                            <h3>{str tag=Members section=group}</h3>
                            <form action="{$WWWROOT}group/view.php" method="post">
                                <input type="hidden" name="id" value="{$group->id|escape}">
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

                            <input type="submit" class="submit" value="Update Membership">
                        </td>
                        <td>
                            <h3>Latest Forum Posts</h3>
                            <table>
                                <tr>
                                    <th>Thread/Thread Starter</th>
                                    <th>Latest Post</th>
                                </tr>
                                <tr>
                                    <td><a href="">Whatever</a><br><a href="">Mike</a></td>
                                    <td>This is some content of some post...</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                </div>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
