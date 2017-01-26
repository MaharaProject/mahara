<div id="imagebrowser" role="dialog" class="modal modal-shown modal-tinymce">
    <div class="modal-dialog">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton close" name="action_removeimagebrowser">
                    <span class="times">×</span>
                    <span class="sr-only">{str tag=Close}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline" id="addblock-heading">
                    {$title|default:"[$strnotitle]"}
                </h4>
            </div>
            <div class="modal-body blockinstance-content">
                <p class="lead text-small">{$description|default:""}</p>
                {$content|safe}
                <div id="filebrowserupdatetarget"></div>
            </div>
        </div>
    </div>
</div>
