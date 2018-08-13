    <h2>
        {$viewtitle}
        {if $ownername}
        {str tag=by section=view}
        {$ownername}
        {/if}
    </h2>
    <p class="view-description">
        {$viewdescription|clean_html|safe}
    </p>
    <p class="view-instructions">
        {$viewinstructions|clean_html|safe}
    </p>
    <div id="view" class="view-container">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent|safe}
           </div>
        </div>
        {if $tags}
        <div class="viewfooter">
            <div class="tags">
            <strong>{str tag=tags}:</strong>
            {list_tags owner=0 tags=$tags}
        </div>
    </div>
    {/if}
</div>
