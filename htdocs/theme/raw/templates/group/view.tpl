{auto_escape off}
{include file="header.tpl"}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description}</div>
{/if}
{if $isadmin}
    <div class="right"><a href="{$WWWROOT}view/blocks.php?id={$viewid}" class="btn-edit right">{str tag ="editcontentandlayout" section="view"}</a></div>
{/if}
{$viewcontent}

{include file="footer.tpl"}
{/auto_escape}
