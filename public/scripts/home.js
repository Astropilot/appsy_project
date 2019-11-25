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
      localStorage.setItem('user',  JSON.stringify(data.user));
      window.location.replace('/dashboard');
    },
    complete: function() {
      $('#wait-login').hide();
    }
  });
}

function getInvite(token, email) {
  $.ajax({
    type: 'GET',
    url: `/api/users/invite?token=${token}&email=${email}`,
    success: function(data) {
      $('#member-email').val(data.invite.email);
      $('#member-firstname').val(data.invite.firstname);
      $('#member-lastname').val(data.invite.lastname);
      $('#invite-form').show();
    },
    complete: function() {
      $('#invite-wait').hide();
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
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 6000,
        text: data.message
      }).show();
    },
    complete: function() {
      $('#wait-register').hide();
    }
  });
}

function getFAQ() {
  $.ajax({
    type: 'GET',
    url: '/api/faq/questions',
    dataType: 'json',
    success: function(data) {
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
    },
    complete: function() {
      $('#faq-wait').hide();
    }
  });
}

function sendContact(name, email, message) {
  $('#wait-contact').show();

  $.ajax({
    type: 'POST',
    url: `/api/contact`,
    data: {
      name: name,
      email: email,
      message: message
    },
    dataType: 'json',
    success: function(data) {
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 6000,
        text: data.message
      }).show();
    },
    complete: function() {
      $('#wait-contact').hide();
    }
  });
}
