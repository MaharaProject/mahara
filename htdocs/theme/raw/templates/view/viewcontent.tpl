<h2>{$viewtitle}{if $ownername} {str tag=by section=view} {$ownername}{/if}</h2>

<p class="view-description">{$viewdescription|clean_html|safe}</p>

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
    <div class="tags"><label>{str tag=tags}:</label> {list_tags owner=0 tags=$tags}</div>
  </div>
{/if}
</div>
