{include file="header.tpl"}

{include file="columnfullstart.tpl"}
        <div id="view">
            <div id="bottom-pane">
                <div id="column-container">
                {$artefact}
                </div>
            </div>
        </div>

        <div id="publicfeedback">
            <table id="feedbacktable" class="fullwidth">
                <thead>
                    <tr><th>{str tag="feedback" section="view"}</th></tr>
                </thead>
            </table>
        </div>
        <div id="viewmenu"></div>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
