$(function() {
  checkIsLogged();

  $('#logout-nav').on('click', function() {
    disconnect();
  });
});

function checkIsLogged() {
  var user = localStorage.getItem('user');

  if (!user)
    window.location.replace('/appsy_project/connexion');
  else {
    user = JSON.parse(user);
    $.ajax({
      type: 'GET',
      url: 'api/users/' + user.id,
      dataType: 'json',
      success: function(data) {
        if (data.r) {
          localStorage.setItem('user',  JSON.stringify(data.user));
          handleSideNavigation();
        } else {
          window.location.replace('/appsy_project/connexion');
        }
      }
    });
  }
}

function handleSideNavigation() {
  var user = JSON.parse(localStorage.getItem('user'));

  $('#wait-nav').hide();
  if (user.role >= 0)
    $('#tests-nav').show();
  if (user.role >= 1)
    $('#subjects-nav').show();
  if (user.role >= 2)
    $('#examiners-nav').show();
}

function disconnect() {
  $.ajax({
    type: 'GET',
    url: 'api/users/logoff',
    dataType: 'json',
    success: function(data) {
      if (data.r) {
        localStorage.clear();
        window.location.replace('/appsy_project/connexion');
      } else {
        new Noty({
          theme: 'metroui',
          type: 'error',
          layout: 'centerRight',
          timeout: 4000,
          text: 'Une erreur est survenue pendant la deconnexion !'
        }).show();
      }
    }
  });
}
