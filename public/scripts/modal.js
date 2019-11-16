(function( $ ) {

  $.fn.showModal = function() {
    var that = this;
    $(that).find('.close').on('click.close', function () {
      that.removeClass('modal-active');
      $(this).off('click.close');
      $(window).off('click.window');
    });

    $(window).on('click.window', function (event) {
      if (event.target == that.get(0)) {
        that.removeClass('modal-active');
        $(this).off('click.window');
        $(that, '.close').off('click.close');
      }
    });

    return that.addClass('modal-active');
  };

  $.fn.closeModal = function() {
    var that = this;

    $(that).find('.close').off('click.close');
    $(window).off('click.window');
    return that.removeClass('modal-active');
  }

}( jQuery ));
