/**
 * Copyright 2005-2009 HUBzero Foundation, LLC.
 * All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

//-----------------------------------------------------------
//  Ensure we have our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//----------------------------------------------------------
// Project Setup JS
//----------------------------------------------------------

if (!jq) {
	var jq = $;
}

HUB.ProjectSetup = {
	jQuery: jq,

	initialize: function()
	{
		var $ 				= this.jQuery;
		var hubfrm 			= $('#hubForm');
		var next_desc		= $('#next_desc');
		var next_step		= $('#next_step');

		// Setup
		var rest  = $('.restricted-opt');
		var resta = $('.restricted-answer');

		// Enable ajax upload of project image
		HUB.ProjectSetup.enableImageUpload();

		// Enable ajax delete of project image
		HUB.ProjectSetup.enableImageDelete();

		// Determine next step in setup process
		HUB.ProjectSetup.advance();

		// Restrictions
		if (rest.length > 0 && resta.length > 0)
		{
			HUB.ProjectSetup.enableButton();

			$('.restricted-opt').each(function(i, item) {
				$(item).off('click');
				HUB.ProjectSetup.showStopSigns(item);
				$(item).on('click', function(e) {
					$('.restricted-answer').each(function(ritem) {
						$(ritem).removeAttr("checked");
					});
					$('#restricted-yes').attr('checked', 'checked');
					HUB.ProjectSetup.showStopSigns(item);
					HUB.ProjectSetup.enableButton();
				});
			});

			$('#restricted-no').on('click', function(e) {
				$('.restricted-opt').each(function(i, item) {
					$(item).removeAttr("checked");
				});
			});

			// Check if can proceed
			if($('.option').length > 0)
			{
				$('.option').each(function(i, item)
				{
					$(item).on('click', function(e) {
						HUB.ProjectSetup.enableButton();
					});
				});
			}
		}

		// Setup pre-screen
		if ($('#f-restricted-no').length && $('#f-restricted-explain').length)
		{
			$('#f-restricted-no').on('click', function(e) {
				$('#f-restricted-explain').addClass('hidden');
			});
			if ($('#f-restricted-no').attr('checked') == 'checked')
			{
				$('#f-restricted-explain').addClass('hidden');
			}
		}
		if ($('#f-restricted-yes').length && $('#f-restricted-explain').length)
		{
			$('#f-restricted-yes').on('click', function(e) {
				$('#f-restricted-explain').removeClass('hidden');
			});
		}

		// Show/hide options to describe project
		if ($('#pid').length && ($('#pid').val() == '' || $('#pid').val() == 0))
		{
			// Show by default for those with JS enabled
			if ($('#moveon')) {
				$('#moveon').css('display', 'block');
			}
			// Hide by default for those with JS enabled
			if ($('#describearea')) {
				$('#describearea').css('display', 'none');
			}

			// Show description fields
			if ($('#next_desc')) {
				$('#next_desc').on('click', function(e) {
					e.preventDefault();
					$('#step').val(0);
					$(hubfrm).submit();
				});
			}

			// Go to next step
			if ($('#next_step')) {
				$('#next_step').on('click', function(e) {
					e.preventDefault();
					$('#step').val(1);
					$(hubfrm).submit();
				});
			}
		}

		// Verification
		if ($('.verifyme').length)
		{
			$('.verifyme').each(function(i, item) {
				var keyupTimer = '';
				var id = $(item).attr('id');
				var output = $(item).parent().find('.verification')[0];

				$(item).on('keydown', function(eventInstance) {
					if (keyupTimer) {
						clearTimeout(keyupTimer);
					}
					if (id == 'field-alias')
					{
						var eventInstance = eventInstance || window.event;
						var key = eventInstance.keyCode || eventInstance.which;

						// Disallow spaces
						if (key == 32 )
						{
						  	eventInstance.preventDefault();
						}
					}
				});

				$(item).on('keyup', function(eventInstance) {
					var result = '';

					// Clear status message
					if ($(output).length)
					{
						$(output).html('');
					}

					// Verify for alias name
					var keyupTimer1 = setTimeout((function()
					{
						if ($(item).val() == '')
						{
							$(item).css('background', '#fbe5e5');
						}
						else
						{
							$(item).css('background', '#f9fdf6');
						}

						if (id == 'field-alias')
						{
							// Via AJAX
							url = '/projects/verify/?no_html=1&ajax=1&text='
							+ $(item).val() + '&pid=' + $('#pid').val();
							$.post(url, {},
								function (response) {

								response = $.parseJSON(response);

								if (response.error)
								{
									$(output).html(response.error);
									$(output).css('color', 'red');
									$('#verified').val(0);
									$(item).css('background', '#fbe5e5');
									HUB.ProjectSetup.watchInput($('#describe'), $('#moveon'));
								}
								else if (response.message)
								{
									$(output).html(response.message);
									$(output).css('color', 'green');
									$('#verified').val(1);
									$(item).css('background', '#f9fdf6');
									HUB.ProjectSetup.watchInput($('#describe'), $('#moveon'));
								}

							});
						}
						else
						{
							HUB.ProjectSetup.suggestAlias();
						}

					}), 1000);
				});
			});
		}
	},

	suggestAlias: function()
	{
		if ($('#field-alias').length && $('#field-alias').val() == ''
		&& $('#field-title').length && $('#field-title').val().length > 15)
		{
			var output = $('#field-alias').parent().find('.verification')[0];
			// Via AJAX
			url = '/projects/suggestalias/?no_html=1&ajax=1&text=' + escape($('#field-title').val());
			$.post(url, {},
				function (response) {
					if (response)
					{
						$('#field-alias').val(response);
						$('#verified').val(1);
						$('#field-alias').css('background', '#f9fdf6');
						HUB.ProjectSetup.watchInput($('#describe'), $('#moveon'));
						// Clear status message
						if ($(output).length)
						{
							$(output).html('');
						}
					}
			});
		}
	},

	enableImageDelete: function()
	{
		if (!$('#deleteimg').length)
		{
			return false;
		}
		// Delete
		$('#deleteimg').on('click', function(e)
		{
			e.preventDefault();
			var url = $('#deleteimg').attr('href') + '?ajax=1&no_html=1';

			// Ajax call 
			$.post(url, {},
				function (response) {
				response = $.parseJSON(response);
				if (response.success)
				{
					// Reload image
					var d = new Date();
					var src = $("#project-image-content").attr('src');
					$("#project-image-content").attr('src', src + '?' + d.getTime());
				}
			});
		});
	},

	enableImageUpload: function()
	{
		if (!$('#ajax-upload').length)
		{
			return false;
		}

		var action = $("#ajax-upload").attr("data-action");
		var btn    = $('#upload-file');

		// Send data
		btn.on('click', function(e)
		{
			e.preventDefault();
			var formData = new FormData();
			formData.append("qqfile", $("#uploader")[0].files[0]);
			$('#status-box').html('');

			$.ajax({
		           type: "POST",
		           url: action,
		           data: formData,
				   contentType: false,
				   processData: false,
		           success: function(response)
		           {
						var success = 0;
						var error = 'There was a problem uploading file(s)';

						if (response)
						{
							try {
								response = $.parseJSON(response);
								if (response.error || response.error != false)
								{
									error = response.error;
								}
								if (response.success)
								{
									success = response.success;
								}
							}
							catch (e)
							{
								// problem
							}
						}
						// Success or error
						if (success)
						{
							// Reload image
							var d = new Date();
							var src = $("#project-image-content").attr('src');
							$("#project-image-content").attr('src', src + '?' + d.getTime());
						}
						else
						{
							$('#status-box').html(error);
						}
					}
			});
		});

	},

	advance: function()
	{
		if (!$('#insetup').length || $('#insetup').val() == 0)
		{
			return false;
		}

		if ($('#gonext').length)
		{
			$('#gonext').on('click', function(eventInstance) {
				eventInstance.preventDefault();
				var step = $('#step').val();
				var next = Number(step) + 1;
				$('#step').val(next);

				if ($('#hubForm')) {
					$('#hubForm').submit();
				}
			});
		}
	},

	watchInput: function(elshow, elhide)
	{
		if (!$('#pid').length || $('#pid').val() != 0)
		{
			return;
		}
		var passed = 1;
		$('.verifyme').each(function(i, item) {
			if ($(item).val() == '')
			{
				passed = 0;
			}
		});
		if ($('#verified').val() != 1)
		{
			passed = 0;
		}

		if (passed == 1) {
			elhide.css('display', 'none');
			elshow.css('display', 'block');
		} else {
			elhide.css('display', 'block');
			elshow.css('display', 'none');
		}
	},

	enableButton: function()
	{
		var $ = this.jQuery;
		var con = $('#btn-finalize');
		var passed = 1;

		if($('#export') && $('#export').attr('checked') == 'checked')
		{
			passed = 0;
		}
		if($('#hipaa') && $('#hipaa').attr('checked') == 'checked')
		{
			passed = 0;
		}
		if($('#irb') && $('#irb').attr('checked') == 'checked' && $('#agree_irb').attr('checked') != 'checked' )
		{
			passed = 0;
		}
		if($('#ferpa') && $('#ferpa').attr('checked') == 'checked' && $('#agree_ferpa').attr('checked') != 'checked' )
		{
			passed = 0;
		}

		if(passed == 1 && con.hasClass('disabled')) {
			con.removeClass('disabled');
		}
		if(passed == 0 && !con.hasClass('disabled')) {
			con.addClass('disabled');
		}

		con.off('click');
		con.on('click', function(e) {
			e.preventDefault();
			if (!con.hasClass('disabled')) {
				if ($('#hubForm')) {
					$('#hubForm').submit();
				}
			}
		});
	},

	showStopSigns: function(el)
	{
		var $ = this.jQuery;

		var oid = $(el).attr('id');
		var obox = '#stop-' + oid;

		if($(el).attr('checked') == 'checked' && $(obox).hasClass('hidden'))
		{
			$(obox).removeClass('hidden');
		}
		else
		{
			$(obox).addClass('hidden');
		}
	},

	enableButtonActivate: function()
	{
		var $ = this.jQuery;
		var con = $('#b-continue');

		if (con)
		{
			var passed = 1;

			if (($('#verified') && $('#verified').val() == 0) || ($('#new-alias') && $('#new-alias').val() == '')) {
				passed = 0;
			}

			if (passed == 1 && con.hasClass('disabled')) {
				con.removeClass('disabled');
			}
			if (passed == 0 && !con.hasClass('disabled')) {
				con.addClass('disabled');
			}

			con.off('click');
			con.on('click', function(e) {
				e.preventDefault();
				if (!con.hasClass('disabled')) {
					if ($('#activate-form')) {
						$('#activate-form').submit();
					}
				}
			});
		}
	}
}

jQuery(document).ready(function($){
	HUB.ProjectSetup.initialize();
});