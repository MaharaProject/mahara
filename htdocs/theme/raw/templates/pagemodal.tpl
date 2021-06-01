<div class="modal fade page-modal js-page-modal" id="{if $prefix}{$prefix}_{/if}page-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" {if !$keepParentModalOpen}data-bs-dismiss="modal"{/if}>{str tag=Close}</button>
            </div>
        </div>
    </div>
</div>
