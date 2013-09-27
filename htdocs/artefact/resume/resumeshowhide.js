/**
 * Javascript for the resume artefact
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * TODO

 * move javascript into the resume plugin?
 * test in safari and opera
 * implement for employment history
 */
$j = jQuery;
$j(document).ready(function() {
	$j(".expandable-body").hide();
	$j(".toggle").addClass('expandable');
	$j(".expandable-head").click(function(event) {
		$j(this).next('.expandable-body').toggle();
		$j(this).children(".toggle.expandable").toggleClass('expanded');
	});
});