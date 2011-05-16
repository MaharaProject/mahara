{include file="header.tpl"}

{if $institution}                {$institutionselector|safe}{/if}

{if !$accesslists.views && !$accesslists.collections}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}

{if $accesslists.collections}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th class="cv">{str tag=collections section=collection}</th>
      <th class="al">{str tag=accesslist section=view}</th>
      <th class="al-edit">{str tag=editaccess section=view}</th>
      <th class="secreturls">{str tag=secreturls section=view}</th>
    </tr>
  </thead>
{foreach from=$accesslists.collections item=collection}
  {include file="view/accesslistrow.tpl" item=$collection}
{/foreach}
  </tbody>
</table>
{/if}

{if $accesslists.views}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th class="cv">{str tag=Views section=view}</th>
    {if $accesslists.collections}
      <th></th>
      <th></th>
      <th></th>
    {else}
      <th class="al">{str tag=accesslist section=view}</th>
      <th class="al-edit">{str tag=editaccess section=view}</th>
      <th class="secreturls">{str tag=secreturls section=view}</th>
    {/if}
    </tr>
  </thead>
  <tbody>
{foreach from=$accesslists.views item=view}
  {include file="view/accesslistrow.tpl" item=$view}
{/foreach}
  </tbody>
</table>
{/if}

{/if}
{include file="footer.tpl"}
