    <div class="modal modal-docked modal-docked-right modal-shown closed" id="objection-review" tabindex="-1" role="dialog" aria-labelledby="#objection-review-label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="deletebutton close" name="action_objectionreview" data-dismiss="modal-docked" aria-label="Close">
                        <span class="times">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title blockinstance-header text-inline objection-review-title" id="objection-review-label">{str tag="objectionreview"}</h4>
                </div>

                <div class="modal-body">
                    {$stillrudeform|safe}
                </div>
            </div>
        </div>
    </div>
