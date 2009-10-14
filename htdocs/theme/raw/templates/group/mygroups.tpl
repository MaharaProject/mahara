{include file="header.tpl"}
            <div class="rbuttons">
                <a href="{$WWWROOT}group/create.php" class="btn">{str tag="creategroup" section="group"}</a>
            </div>
{$form}
{if $groups}
{foreach from=$groups item=group}
            <div class="r{cycle values='0,1'} listing">
                <div class="fr">
                     {include file="group/groupuserstatus.tpl" group=$group returnto='find'}
                </div>
                <div>
                     {include file="group/group.tpl" group=$group returnto='mygroups'}
                </div>
            </div>
{/foreach}
{$pagination}
{else}
            <div class="message">{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}</div>
{/if}
{include file="footer.tpl"}
