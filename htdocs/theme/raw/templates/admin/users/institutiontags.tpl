{include file="header.tpl"}

{if !$canedit}<p>{str tag=cantlistinstitutiontags}</p>{/if}
{if $tags && !$new}
<div class="card card-default view-container">
  <div class="table-responsive">
  <table class="fullwidth table table-striped">
      <thead>
          <tr>
              <th>{str tag="tag"}</th>
              <th>{str tag="timesused"}</th>
              <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
          </tr>
      </thead>
      <tbody>
      {foreach from=$tags item=tag}
          <tr>
              <td>{$tag->tag}</td>
              <td>{$tag->count}</td>
              <td class="center">
                <div class="float-right">
                  {if $tag->count <= 0}
                    <a href="{$WWWROOT}admin/users/institutiontags.php?delete={$tag->id}&institution={$institution}" title="{str tag=deleteinstitutiontag}" class="btn btn-secondary btn-sm">
                    <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=deleteinstitutiontagspecific arg1=$tag->tag}</span>
                    </a>
                  {else}
                      {str tag=usedtagscantbedeleted}
                  {/if}
                </div>
              </td>
          </tr>
      {/foreach}
      </tbody>
  </table>
  </div>
  {$pagination|safe}
  {if $pagination_js}
    <script>
    {$pagination_js|safe}
    </script>
  {/if}
</div>
{else}
  {if $new}
    {$form|safe}
  {else}
    <p class="lead view-description">{str tag=institutiontagsdescription}</p>
    <p class="no-results">
        {str tag=notags}{if $addonelink} <a href={$addonelink}>{str tag=addone}</a>{/if}
    </p>
  {/if}
{/if}

{include file="footer.tpl"}
