{include file="header.tpl"}

{if !$canedit}<p>{str tag=cantlistinstitutiontags}</p>{/if}
{if $tags && !$new}
  <p class="lead view-description">{str tag=institutiontagsdescription}</p>
  <div class="panel panel-default">
    <div id="institutiontags" class="list-group">
        <div id="institutiontags" class="list-group">
          {foreach $tags tag}
          <div class="list-group-item r0 ">
                <div class="row">
                    <div class="col-md-9">
                        <h3 class="title list-group-item-heading" title="{$tag->tag}">
                            {$tag->tag}
                        </h3>
                    </div>
                    <div class="col-md-3">
                      <div class="inner-link btn-action-list">
                        <div class="btn-top-right btn-group btn-group-top">
                        {if $tag->count <= 0}
                          <a href="{$WWWROOT}admin/users/institutiontags.php?delete={$tag->id}&institution={$institution}" title="{str tag=deleteinstitutiontag}" class="btn btn-default btn-xs">
                          <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                          <span class="sr-only">{str tag=deleteinstitutiontag}</span>
                          </a>
                        {else}
                            {str tag=timesused} {$tag->count}
                        {/if}
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
  </div>
  {$pagination|safe}
  {if $pagination_js}
    <script type="application/javascript">
    {$pagination_js|safe}
    </script>
  {/if}
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
