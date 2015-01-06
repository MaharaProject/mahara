<h2>{$collectiontitle}{if $ownername} {str tag=by section=collection} {$ownername}{/if}</h2>

<p class="collection-description">{$collectiondescription|clean_html|safe}</p>

<!-- include a modified navigation bar -->
    {include file=previewcollectionnav.tpl}

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent|safe}
                <div class="cb">
                </div>
            </div>
        </div>
{if $tags}
  <div class="viewfooter cb">
    <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$tags}</div>
  </div>
{/if}
</div>
