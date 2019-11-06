function connexion(email, password) {
  $.ajax({
    type: 'POST',
    url: 'api/users/login',
    data: {email: email, password: password},
    dataType: 'json',
    success: function(data) {
      $('#wait-login').hide();
      if (data.r) {
        console.log(data);
        window.location.replace('dashboard/index.html');
      } else {
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
    },
    error: function() {
      console.log('User login request failed.');
      $('#wait-login').hide();
    }
  });
}

$(function() {
  $('#btn-login').on('click', function() {
    $('#wait-login').show();
    connexion($('#email').val(), $('#password').val());
  });
});
