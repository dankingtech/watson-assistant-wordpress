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

jQuery(document).ready(function($) {
    $('#watsonconv_color').wpColorPicker({
      palettes: true,
      change: function() {
        $('.popup-box .popup-head, .message-container .messages .watson-message')
          .css({
            'background-color': this.value,
            'color': luminance(this.value) > 0.5 ? 'black' : 'white'
          });
      }
    });

    $('input[name="watsonconv_use_limit"]')
      .on('change', function() {
        $('#watsonconv_limit, #watsonconv_interval').attr('disabled', this.value == 'no');
      })
      .trigger('change');

    $('input[name="watsonconv_use_client_limit"]')
      .on('change', function() {
        $('#watsonconv_client_limit, #watsonconv_client_interval').attr('disabled', this.value == 'no');
      })
      .trigger('change');

    $('#watsonconv_font_size').on('change', function() {
      var size = $('input[name="watsonconv_size"]:checked').val();

      $('body .popup-box, .message-form .message-input').css('font-size', this.value + 'pt');
      $('.popup-box').css('width', (0.825 * size + 4.2 * this.value) + 'pt');
    });

    $('input[name="watsonconv_size"]').on('change', function() {
      var fontSize = $('#watsonconv_font_size').val();

      $('.popup-box').css('width', (0.825 * this.value + 4.2 * fontSize) + 'pt');
      $('.message-container').css('height', this.value + 'pt');
    });

    $('#watsonconv_title').on('input', function() {
      $('.popup-head-left').text(this.value)
    });
});
