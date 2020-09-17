<div class="modal modal-shown modal-docked-right modal-docked closed blockinstance configure" id="modal_{$artefactid}" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton close" name="close_configuration">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=closeconfiguration section=view}</span>
                </button>
                <h1 class="modal-title text-inline">{$title}</h1>

            </div>
            <div class="modal-body blockinstance-content">
                {$content|safe}
            </div>
        </div>
    </div>
</div>
