if (typeof jQuery.fn.prop != 'function') {
  jQuery.fn.prop = jQuery.fn.attr;
}

if (typeof localStorage !== 'undefined') {
  try {
      localStorage.setItem('localStorage', 1);
      localStorage.removeItem('localStorage');
  } catch (e) {
      Storage.prototype._setItem = Storage.prototype.setItem;
      Storage.prototype.setItem = function() {};
  }
}

function luminance(hex) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

  if (!Array.isArray(result) || result.length !== 4) {
    return 0;
  }

  var rgb = result.slice(1, 4).map(function(val) {
    val = parseInt(val, 16) / 255;

    if (val <= 0.03928 ) {
			return val / 12.92
		} else {
			return Math.pow((val + 0.055) / 1.055, 2.4);
		}
  });

  return 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2];
} 

function switch_tab(tab_name) {
  jQuery('.tab-page').hide();
  jQuery(`.tab-page.${tab_name}_page`).show();
  jQuery('.nav-tab-wrapper .nav-tab-active').removeClass('nav-tab-active');
  jQuery(`.nav-tab-wrapper .${tab_name}_tab`).addClass('nav-tab-active');

  sessionStorage.setItem('watsonconv_active_tab_' + page_data.hook_suffix, tab_name);
}

jQuery(document).ready(function($) {
  
  if (sessionStorage.getItem('watsonconv_active_tab_' + page_data.hook_suffix)) {
    switch_tab(sessionStorage.getItem('watsonconv_active_tab_' + page_data.hook_suffix));
  }

  $(document).tooltip({
    show: 200,
    hide: 200
  });

  $('#error_expand')
    .on('click', function(e) {
      e.preventDefault();

      $('#error_response').toggle(200);
    })

  // ---- Main Setup Section ----
  $('input[name="watsonconv_credentials[type]"]')
  .on('change', function() {
    if (this.value == 'basic') {
      $('.basic_cred').closest('tr,p').show();
      $('.iam_cred').closest('tr,p').hide();
    } else if (this.value == 'iam') {
      $('.basic_cred').closest('tr,p').hide();
      $('.iam_cred').closest('tr,p').show();
    }
  })
  .filter('input:checked')
  .trigger('change');


  $('#watsonconv_enabled')
    .on('change', function() {
      if (this.checked) {
        $('input.watsonconv_credentials').prop('disabled', false);
      } else {
        $('input.watsonconv_credentials').prop('disabled', true);
      }
    })
    .trigger('change');

  // ---- Voice Calling Section ----

  $('input[name="watsonconv_use_twilio"]')
    .on('change', function() {
      if (this.value == 'yes') {
        $('span.twilio_settings').parent().show();
        $('p.twilio_settings').show();

        $('input[id="watsonconv_twilio_sid"]').closest('table').show();
        $('input[id="watsonconv_call_tooltip"]').closest('table').show();

        $('input[id="watsonconv_twilio_sid"]').closest('table').find(':input').prop('disabled', false);
        $('input[id="watsonconv_twilio_sid"]').closest('table').find(':input').prop('disabled', false);
      } else {
        $('span.twilio_settings').parent().css('display', 'none');
        $('p.twilio_settings').css('display', 'none');

        $('input[id="watsonconv_twilio_sid"]').closest('table').css('display', 'none');
        $('input[id="watsonconv_call_tooltip"]').closest('table').css('display', 'none');

        $('input[id="watsonconv_twilio_sid"]').closest('table').find(':input').prop('disabled', true);
        $('input[id="watsonconv_twilio_sid"]').closest('table').find(':input').prop('disabled', true);
      }
    })
    .filter('input:checked')
    .trigger('change');


  // ---- Rate Limiting Section ----

  $('input[name="watsonconv_use_limit"]')
    .on('change', function() {
      $('#watsonconv_limit, #watsonconv_interval, #watsonconv_limit_message').prop('disabled', this.value == 'no');
    })
    .filter('input:checked')
    .trigger('change');

  $('input[name="watsonconv_use_client_limit"]')
    .on('change', function() {
      $('#watsonconv_client_limit, #watsonconv_client_interval, #watsonconv_client_limit_message').prop('disabled', this.value == 'no');
    })
    .filter('input:checked')
    .trigger('change');

  // ------ Behaviour section ------

  $('input[name="watsonconv_show_on"]')
    .on('change', function() {
      if (this.value === 'only') {
        $('span.show_on_only').show();
        $('fieldset.show_on_only').closest('tr').show();
      } else {
        $('span.show_on_only').css('display', 'none');
        $('fieldset.show_on_only').closest('tr').css('display', 'none');
      }
    })
    .filter('input:checked')
    .trigger('change');

  $('input[id="select_all_pages"]')
    .on('change', function() {
      $('input[name="watsonconv_pages[]"]').prop('checked', this.checked);
    })

  $('input[id="select_all_posts"]')
    .on('change', function() {
      $('input[name="watsonconv_posts[]"]').prop('checked', this.checked);
    })

  $('input[id="select_all_cats"]')
    .on('change', function() {
      $('input[name="watsonconv_categories[]"]').prop('checked', this.checked);
    })

  // ------ Appearance Section ------

  $('input[name="watsonconv_full_screen[mode]"]')
    .on('change', function() {
      if (this.value == 'mobile') {
        $('#watsonconv_full_screen_query').hide();
        $('#watsonconv_full_screen_max_width').show();
      } else if (this.value == 'custom') {
        $('#watsonconv_full_screen_query').show();
        $('#watsonconv_full_screen_max_width').hide();
      } else {
        $('#watsonconv_full_screen_query').hide();
        $('#watsonconv_full_screen_max_width').hide();
      }
    })
    .filter('input:checked')
    .trigger('change');

  $('#watsonconv_color')
    .wpColorPicker({
      palettes: true,
      change: function() {
        $('#watson-box #watson-header, #message-container #messages .watson-message, #watson-fab, #message-send')
          .css({
            'background-color': this.value,
            'color': luminance(this.value) > 0.5 ? 'black' : 'white'
          });

        $('#message-send svg')
        .css({
          'fill': luminance(this.value) > 0.5 ? 'black' : 'white'
        })
      }
    });

  $('#watsonconv_font_size')
    .on('change', function() {
      var size = $('input[name="watsonconv_size"]:checked').val();

      $('#watson-box .watson-font').css('font-size', this.value + 'pt');
      $('#watson-box').css('width', (0.825 * size + 4.2 * this.value) + 'pt');
    });

  $('input[name="watsonconv_size"]')
    .on('change', function() {
      var fontSize = $('#watsonconv_font_size').val();

      $('#watson-box').css('width', (0.825 * this.value + 4.2 * fontSize) + 'pt');
      $('#message-container').css('height', this.value + 'pt');
    });

  $('input[name="watsonconv_send_btn"]')
    .on('change', function() {
      if (this.value == 'no') {
        $('#message-send').hide();
      } else { 
        $('#message-send').show();
      }
    })
    .filter('input:checked')
    .trigger('change');

  $('#watsonconv_title')
    .on('input', function() {
      $('#watson-title').text(this.value)
    });

  $('#watsonconv_clear_text')
    .on('input', function() {
      $('#watson-clear-messages').text(this.value)
    });

  $('#watsonconv_message_prompt')
    .on('input', function() {
      $('#watson-message-input').attr('placeholder', this.value)
    });
  
  $('input[name="watsonconv_fab_icon_pos"]')
    .on('change', function() {
      if (this.value == 'left') {
        $('.fab-icon-left').show();
        $('.fab-icon-right').hide();
      } else if (this.value == 'right') {
        $('.fab-icon-left').hide();
        $('.fab-icon-right').show();
      } else {
        $('.fab-icon-left').hide();
        $('.fab-icon-right').hide();
      }
    })
    .filter('input:checked')
    .trigger('change');

  $('#watsonconv_fab_text')
    .on('input', function() {
      if (this.value) {
        $('#watson-fab-text').show().text(this.value)
      } else {
        $('#watson-fab-text').hide().text(this.value);
      }
    })
    .trigger('input');

  $('#watsonconv_fab_icon_size')
    .on('change', function() {
      $('#watson-fab-icon').css('font-size', this.value + 'pt');
    });

  $('#watsonconv_fab_text_size')
    .on('change', function() {
      $('#watson-fab-text').css('font-size', this.value + 'pt');
    });

  $('#watsonconv_credentials_link')
      .on('click', function () {
         $('#watsonconv_credentials_description').css('display', 'block');
         return false;
      });
});
