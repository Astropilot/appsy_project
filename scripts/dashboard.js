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
      url: '/appsy_project/api/users/' + user.id,
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
    url: '/appsy_project/api/users/logoff',
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

function getUserContacts() {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: '/appsy_project/api/users/' + user.id + '/contacts',
    dataType: 'json',
    success: function(data) {
      $('#contacts-wait').hide();
      if (data.r) {
        if (data.contacts.length == 0) {
          $('#nocontact').show();
          return;
        }
        data.contacts.forEach(function(contact) {
          $('#contacts').append(
            `<div class="contact row bg-grey" style="display: flex; align-items: center; padding: 10px; margin-bottom: 5px">
              <b>${contact.user.firstname} ${contact.user.lastname}</b> : <span style="text-color: gray">${contact.message}</span>
              <a href="/appsy_project/dashboard/chat_user?id=${contact.user.id}" class="btn btn-primary" style="margin-left: auto">
                Accéder aux messages
              </a>
             </div>`
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

function getUserContactMessages(contact_id) {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: `/appsy_project/api/users/${user.id}/${contact_id}/messages`,
    dataType: 'json',
    success: function(data) {
      $('#messages-wait').hide();
      if (data.r) {
        $('#messages').empty();
        if (data.messages.length == 0) {
          $('#nomessage').show();
          return;
        }
        $('#contact').empty();
        $('#contact').append(`${data.contact.firstname} ${data.contact.lastname}`);
        data.messages.forEach(function(message) {
          var message_class = (message.author.id == user.id) ? 'msgMe' : 'msgOthers';
          $('#messages').append(
            `<div class="message ${message_class}">
              <h6><b>${message.author.firstname} ${message.author.lastname}</b></h6>
              <p>${message.message}</p>
              <small>${message.created_at}</small>
             </div>`
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

function sendMessageTo(contact_id, message) {
  var user = JSON.parse(localStorage.getItem('user'));
  $('#wait-send-message').show();

  $.ajax({
    type: 'POST',
    url: `/appsy_project/api/users/${user.id}/${contact_id}/messages`,
    data: {message: message},
    dataType: 'json',
    success: function(data) {
      $('#wait-send-message').hide();
      if (data.r) {
        var message = data.message;

        $('#text-message').val('');
        $('#messages').prepend(
          `<div class="message msgMe">
            <h6><b>${message.author.firstname} ${message.author.lastname}</b></h6>
            <p>${message.message}</p>
            <small>${message.created_at}</small>
           </div>`
        );
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
