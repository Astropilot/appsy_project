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
});
