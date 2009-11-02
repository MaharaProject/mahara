{include file="header.tpl"}

{if $GROUP}<h2>{str tag=groupviews section=view}</h2>
{/if}
            <div class="rbuttons{if $GROUP} pagetabs{/if}">
                {$createviewform}
                <form method="post" action="{$WWWROOT}view/choosetemplate.php">
                    <input type="submit" class="submit" value="{str tag="copyaview" section="view"}">
{if $GROUP}
                    <input type="hidden" name="group" value="{$GROUP->id}" />
{elseif $institution}
                    <input type="hidden" name="institution" value="{$institution}">
{/if}
                </form>
            </div>
{if $institution}                {$institutionselector}{/if}

{if $views}
            <table id="myviews" class="fullwidth listing">
                <tbody>
{foreach from=$views item=view}
                    <tr class="{cycle values='r0,r1'}">
                        <td><div class="rel">
                            <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></h3>
{if $view.submittedto}
                            <div class="vi submitted-viewitem">{$view.submittedto}</div>
{else}
                            {if $view.removable}<div class="rbuttons"><a href="{$WWWROOT}view/delete.php?id={$view.id}" class="btn-del">{str tag="deletethisview" section="view"}</a></div>{/if}
                            <div class="vi">
                                <h4><a href="{$WWWROOT}view/edit.php?id={$view.id}" id="editviewdetails">{str tag="editviewnameanddescription" section="view"}</a></h4>
{if $view.description}
                                <div class="videsc">{$view.description}</div>
{/if}
{if $view.tags}
                                <div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>
{/if}
                            </div>
                            <div class="vi">
                                <h4><a href="{$WWWROOT}view/blocks.php?id={$view.id}" id="editthisview">{str tag ="editthisview" section="view"}</a></h4>
{if $view.artefacts}
                                <div class="videsc">{str tag="artefacts" section="view"}:
                                {foreach from=$view.artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$view.id}" id="link-artefacts">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}{/foreach}</div>
{/if}
                            </div>
{/if}
                            <div class="vi">
                                <h4><a href="{$WWWROOT}view/access.php?id={$view.id}" id="editviewaccess">{str tag="editviewaccess" section="view"}</a></h4>
{if $view.access}
                               <div class="videsc">{$view.access}</div>
{/if}
{if $view.accessgroups}
                                <div class="videsc">{str tag="whocanseethisview" section="view"}:
{foreach from=$view.accessgroups item=accessgroup name=artefacts}{strip}
{if $accessgroup.accesstype == 'loggedin'}
    {str tag="loggedinlower" section="view"}
{elseif $accessgroup.accesstype == 'public'}
    {str tag="publiclower" section="view"}
{elseif $accessgroup.accesstype == 'friends'}
    <a href="{$WWWROOT}user/myfriends.php" id="link-myfriends">{str tag="friendslower" section="view"}</a>
{elseif $accessgroup.accesstype == 'group'}
    <a href="{$WWWROOT}group/view.php?id={$accessgroup.id}">{$accessgroup.name|escape}</a>{if $accessgroup.role} ({$accessgroup.roledisplay}){/if}
{elseif $accessgroup.accesstype == 'user'}
    <a href="{$WWWROOT}user/view.php?id={$accessgroup.id}">{$accessgroup.id|display_name|escape}</a>
{/if}{/strip}{if !$smarty.foreach.artefacts.last}, {/if}
{/foreach}
{if $view.template}<br>{str tag=thisviewmaybecopied section=view}{/if}
                                </div>
{else}
                                <div class="videsc">{str tag="nobodycanseethisview2" section="view"}</div>
{/if}
                            </div>
{if $view.submitto}
                            <div class="vi submit-viewitem">{$view.submitto}</div>
{/if}
                        </div></td>
                    </tr>
{/foreach}
                </tbody>
            </table>
{$pagination}
{else}
            <div class="message">{if $GROUP}{str tag="noviewstosee" section="group"}{elseif $institution}{str tag="noviews" section="view"}{else}{str tag="youhavenoviews" section="view"}{/if}</div>
{/if}
{include file="footer.tpl"}

