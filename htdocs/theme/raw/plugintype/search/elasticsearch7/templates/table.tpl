{*
Presents the Index Reset summary on the ES7 Plugin form.
Expects the following
$table[
  [
    type => string,
    itemsToQueue => int,
    itemsInIndex => int,
  ]
]
*}
<table>
  <thead>
    <tr>
      <th style="padding-right:1em;">{str tag=resettype section=search.elasticsearch7}</th>
      <th style="padding-right:1em;">{str tag=resetitemsinqueue section=search.elasticsearch7}</th>
      <th style="padding-right:1em;">{str tag=resetitemsinindex section=search.elasticsearch7}</th>
      <th style="padding-right:1em;"></th>
    </tr>
  </thead>
  <tbody>
  {if $table}
    {foreach from=$table item=row}
      <tr>
        <td style="padding-right:1em;">{$row['type']}</td>
        <td id="es7-queue-count-{$row['type']|replace:'_':''}" style="text-align: right; padding-right:1em;">{$row['itemsToQueue']}</td>
        <td id="es7-index-count-{$row['type']|replace:'_':''}" style="text-align: right; padding-right:1em;">{$row['itemsInIndex']}</td>
        <td style="text-align: right; padding-right:1em;">
          <div class="input-group mb-3" id="es7-requeue-wrapper-{$row['type']|replace:'_':''}">
            <input type="text" id="es7-requeue-{$row['type']|replace:'_':''}" class="form-control" placeholder="Requeue all items" aria-label="Requeue all or enter the IDs to queue" aria-describedby="basic-addon-{$row['type']|replace:'_':''}">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" onclick="es7requeueitems('{$row['type']}');" type="button" id="basic-addon-{$row['type']|replace:'_':''}">Requeue</button>
            </div>
          </div>
        </td>
      </tr>
    {/foreach}
  {else}
      <tr>
        <td colspan="4">{str tag=notypesfound section=search.elasticsearch7}</td>
      </tr>
  {/if}

  </tbody>
</table>