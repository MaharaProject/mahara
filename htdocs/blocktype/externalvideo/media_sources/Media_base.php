<?php

interface MediaBase {
    public function process_url($input, $width=0, $height=0);
    public function validate_url($input);
    public function get_base_url();
    public function enabled();
}
