$(function() {
  $('a.translation').each(function (i) {
    var lang = $(this).attr('id-tr');

    var url = window.location.pathname;
    url = url.replace(/^\/+/, '');

    if (url.length === 2 || (url.length > 2 && url[2] === '/')) {
      url = url.substring(2);
    } else
      url = '/' + url;

    $(this).attr('href', '/' + lang + url);
  });

  $(document).ajaxError(function(event, request) {
    handleErrors(request);
  });
});

function handleErrors(xhrReponse) {
  if (xhrReponse.responseText != '') {
    var data = $.parseJSON(xhrReponse.responseText);

    data.errors.forEach(function(error) {
      new Noty({
        theme: 'metroui',
        type: 'error',
        layout: 'centerRight',
        timeout: 4000,
        text: error
      }).show();
    });
  }
}
