    <div class="modal modal-docked modal-docked-right modal-shown closed" id="objection-review" tabindex="-1" role="dialog" aria-labelledby="#objection-review-label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="deletebutton btn-close" name="action_objectionreview" data-bs-dismiss="modal-docked" aria-label="{str tag=Close}">
                        <span class="times">&times;</span>
                        <span class="visually-hidden">Close</span>
                    </button>
                    <h1 class="modal-title blockinstance-header text-inline objection-review-title" id="objection-review-label">{str tag="objectionreview"}</h1>
                </div>

                <div class="modal-body">
                    {$stillrudeform|safe}
                </div>
            </div>
        </div>
    </div>
