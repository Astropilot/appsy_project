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

function getInvite(token, email) {
  $.ajax({
    type: 'GET',
    url: `/api/users/invite?token=${token}&email=${email}`,
    success: function(data) {
      $('#invite-wait').hide();
      if (data.r) {
        $('#member-email').val(data.invite.email);
        $('#member-firstname').val(data.invite.firstname);
        $('#member-lastname').val(data.invite.lastname);
        $('#invite-form').show();
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
    }
  });
}

function registerMember(token, email, firstname, lastname, password, password_check) {
  $('#wait-register').show();

  $.ajax({
    type: 'POST',
    url: `/api/users`,
    data: {
      token: token,
      email: email,
      firstname: firstname,
      lastname: lastname,
      password: password,
      password_check: password_check
    },
    dataType: 'json',
    success: function(data) {
      $('#wait-register').hide();
      if (data.r) {
        new Noty({
          theme: 'metroui',
          type: 'success',
          layout: 'centerRight',
          timeout: 6000,
          text: data.message
        }).show();
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
    }
  });
}

function getFAQ() {
  $.ajax({
    type: 'GET',
    url: '/api/faq/questions',
    dataType: 'json',
    success: function(data) {
      $('#faq-wait').hide();
      if (data.r) {
        if (data.faq.length == 0) {
          $('#nofaq').show();
          return;
        }
        data.faq.forEach(function(faq) {
          var faq_template = $('#faq-template').clone().removeClass('d-none');

          faq_template.find('h3').text(faq.question);
          faq_template.find('p').text(faq.answer);

          $('#faq-questions').append(
            faq_template
          );
        });
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
    }
  });
}
