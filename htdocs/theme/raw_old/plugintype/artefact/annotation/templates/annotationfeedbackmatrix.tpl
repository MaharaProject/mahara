<div id="annotationfeedback_{$blockid}" class="annotation-feedback matrix">
    <div class="modal-header modal-section">
        {str tag="Annotationfeedback" section="artefact.annotation"}
    </div>
    {if $addannotationfeedbackform}
        <div id="addfeedbackmatrix">
            {str tag="placeannotationfeedback" section="artefact.annotation"}
            {$addannotationfeedbackform|safe}
        </div>
    {/if}
    <div id="matrixfeedbacklist">
        {if $annotationfeedbackcount}
        <ul id="annotationfeedbacktable_{$blockid}" class="annotationfeedbacktable list-group list-group-lite list-unstyled">
            {$annotationfeedback->tablerows|safe}
        </ul>
        {else}
        <div class="form-group"><span class="description">{str tag='nofeedback' section='artefact.annotation'}</span></div>
        {/if}
    </div>
</div>

