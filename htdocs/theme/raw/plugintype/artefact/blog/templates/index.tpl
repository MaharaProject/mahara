{include file="header.tpl"}
{if !$group || $canedit}
<div class="btn-top-right btn-group btn-group-top">
    <a class="btn btn-secondary settings" href="{$WWWROOT}artefact/blog/new/index.php{if $institutionname}?institution={$institutionname}{/if}{if $group}?group={$group}{/if}">
        <span class="icon icon-lg icon-plus left" role="presentation" aria-hidden="true"></span>
        {str section="artefact.blog" tag="addblog"}
    </a>
</div>
{/if}
{if !$blogs->data}
  <p class="no-results">
  {if $group}
    {if $canedit}
      {str tag=youhavenogroupblogsaddone section=artefact.blog arg1=$grouphref}
    {else}
      {str tag=youhavenogroupblogs section=artefact.blog arg1=$grouphref}
    {/if}
  {elseif $institutionname == 'mahara'}
    {str tag=youhavenositeblogs1 section=artefact.blog arg1=$sitehref}
  {elseif $institutionname}
    {str tag=youhavenoinstitutionblogs1 section=artefact.blog arg1=$institutionhref}
  {else}
    {str tag=youhavenoblogsaddone section=artefact.blog arg1=$userhref}
  {/if}
  </p>
{else}
<div class="rel view-container">
    <div class="card-items">
        <div id="bloglist">
            {$blogs->tablerows|safe}
        </div>
        <div class="card-pagination">
            {$blogs->pagination|safe}
        </div>
    </div>
</div>
{/if}
{include file="footer.tpl"}
