jQuery(document).ready(function($) {
    $('#watsonconv_color').wpColorPicker({
      palettes: true
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
});
