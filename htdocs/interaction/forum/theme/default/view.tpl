{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$groupname|escape} - {$forum->title|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
<div>
{$forum->description}
</div>
<div class="fr"><span class="addicon">
<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}">{str tag="newtopic" section="interaction.forum}</a>
</span></div>
{if $admin}
<div>
<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag="edittitle" section="interaction.forum"}</a>
 | <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag="deleteforum" section="interaction.forum"}</a>
</div>
{/if}
{$forum->subscribe}
<form action="" method="post">
{if !$forum->subscribed && ($stickytopics || $regulartopics)}
<input type="submit" name="subscribe" value="{str tag="subscribe" section="interaction.forum"}" class="submit">
{if $moderator}
<input type="submit" name="sticky" value="{str tag="sticky" section="interaction.forum"}" class="submit">
<input type="submit" name="closed" value="{str tag="closed" section="interaction.forum"}" class="submit">
{/if}
{/if}
{if $stickytopics}
<h4>{str tag="stickytopics" section="interaction.forum"}</h4>
{include file="interaction:forum:topics.tpl" topics=$stickytopics moderator=$moderator forum=$forum}
{/if}
{if $regulartopics}
<h4>{str tag="regulartopics" section="interaction.forum"}</h4>
{include file="interaction:forum:topics.tpl" topics=$regulartopics moderator=$moderator forum=$forum}
<span class="center">{$pagination}</span>
{else}
<h4>{str tag="noregulartopics" section="interaction.forum"}</h4>
{/if}
{if !$forum->subscribed && ($stickytopics || $regulartopics)}
<input type="submit" name="subscribe" value="{str tag="subscribe" section="interaction.forum"}" class="submit">
{if $moderator}
<input type="submit" name="sticky" value="{str tag="sticky" section="interaction.forum"}" class="submit">
<input type="submit" name="closed" value="{str tag="closed" section="interaction.forum"}" class="submit">
{/if}
{/if}
</form>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
