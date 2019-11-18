(function( $ ) {

  $('.tablinks').on('click', function () {
    $('.tabcontent').hide();

    $('.tablinks').removeClass('active');
    $('#' + $(this).data('tab')).show();
    $(this).addClass('active');
  });

  $('.tablinks').first().addClass('active');
  $('.tabcontent').first().show();

}( jQuery ));
