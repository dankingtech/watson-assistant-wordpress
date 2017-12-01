function luminance(hex) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

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

if (typeof jQuery.fn.prop != 'function') {
  jQuery.fn.prop = jQuery.fn.attr;
}

jQuery(document).ready(function($) {
  // ---- Rate Limiting Section ----
  $('input[name="watsonconv_use_limit"]')
    .on('change', function() {
      $('#watsonconv_limit, #watsonconv_interval').attr('disabled', this.value == 'no');
    })
    .filter('input:checked')
    .trigger('change');

  $('input[name="watsonconv_use_client_limit"]')
    .on('change', function() {
      $('#watsonconv_client_limit, #watsonconv_client_interval').attr('disabled', this.value == 'no');
    })
    .filter('input:checked')
    .trigger('change');

  // ------ Behaviour section ------

  $('input[name="watsonconv_show_on"]')
    .on('change', function() {
      if (this.value === 'only') {
        $('span.show_on_only').show();
        $('fieldset.show_on_only').parent().parent().show();
      } else {
        $('span.show_on_only').css('display', 'none');
        $('fieldset.show_on_only').parent().parent().css('display', 'none');
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

  $('#watsonconv_color')
    .wpColorPicker({
      palettes: true,
      change: function() {
        $('#watson-box #watson-header, #message-container #messages .watson-message, #watson-fab')
          .css({
            'background-color': this.value,
            'color': luminance(this.value) > 0.5 ? 'black' : 'white'
          });
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

  $('#watsonconv_title')
    .on('input', function() {
      $('#title').text(this.value)
    });
});
