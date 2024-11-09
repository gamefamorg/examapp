/*
 * SimpleModal Basic Modal Dialog
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2010 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: basic.js 254 2010-07-23 05:14:44Z emartin24 $
 */

jQuery(function ($) {
	// Load dialog on page load
	//$('#basic-modal-content').modal();

	// Load dialog on click
	$('.basic-modal .basic').click(function (e) {
		$('#basic1').modal();

		return false;
	});
	$('.basic-modal2 .basic2').click(function (e) {
		$('#basic2').modal();

		return false;
	});
		$('.basic-modal3 .basic3').click(function (e) {
		$('#basic3').modal();

		return false;
	});
		$('.basic-modal4 .basic4').click(function (e) {
		$('#basic4').modal();

		return false;
	});
		$('.basic-modal5 .basic5').click(function (e) {
		$('#basic5').modal();

		return false;
	});
		$('.basic-modal6 .basic6').click(function (e) {
		$('#basic6').modal();

		return false;
	});
});