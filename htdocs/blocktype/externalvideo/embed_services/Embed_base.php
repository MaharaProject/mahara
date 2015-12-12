<?php

interface EmbedBase {

    /*
     *  Returns if this embed service is enabled or not.
     */
    public function enabled();

    /*
     *  Function that process the URL and generates HTML
     *  needed for embedding the URL.
     *  @param string $input  url to be processed
     *  @param int $width     width of the embedded content
     *  @param int $height    height of the embedded content
     *  @return string        html code to embed content within page
     */
    public function process_url($input, $width=0, $height=0);

    /*
     *  Function that checks if the URL is valid, meaning that
     *  embed service is able to generate embed code for this URL.
     *  @param string $input  url to be validated
     *  @return boolean       if url is valid or not
     */
    public function validate_url($input);

    /*
     *  Function that process entered embed/iframe code and
     *  prepares it for embedding into Mahara page.
     *  @param string $input  embed/iframe code to be processed
     *  @return array         values for sanitized embed code
     */
    public function process_content($input);

    /*
     *  Function that builds embed/iframe code to be
     *  embedded into Mahara page.
     *  @param array $input   values for sanitized embed code
     *  @return string        sanitized embed code for Mahara page
     */
    public function embed_content($input);

    /*
     *  Function that returns embed service base URL.
     *  @return string  returns embed service base url
     */
    public function get_base_url();

}
