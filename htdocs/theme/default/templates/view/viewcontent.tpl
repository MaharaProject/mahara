<h2>{$viewtitle}{if $ownername} {str tag=by section=view} {$ownername}{/if}</h2>

<p class="view-description">{$viewdescription}</p>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
</div>
