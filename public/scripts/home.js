function connexion(email, password) {
  localStorage.clear();
  $('#wait-login').show();

  $.ajax({
    type: 'POST',
    url: '/api/users/login',
    data: {email: email, password: password},
    dataType: 'json',
    success: function(data) {
      $('#wait-login').hide();
      if (data.r) {
        localStorage.setItem('user',  JSON.stringify(data.user));
        window.location.replace('/dashboard');
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
  $('#email,#password').keypress(function (e) {
    if (e.which == 13) {
      connexion($('#email').val(), $('#password').val());
      return false;
    }
  });
  $('#btn-login').on('click', function() {
    connexion($('#email').val(), $('#password').val());
  });
});
