var ROLES = {0: 'Utilisateur', 1: 'Examinateur', 2: 'Administrateur'};

$(function() {
  checkIsLogged();

  $('#logout-nav').on('click', function() {
    disconnect();
  });
});

function checkIsLogged() {
  var user = localStorage.getItem('user');

  if (!user)
    window.location.replace('connexion');
  else {
    user = JSON.parse(user);
    $.ajax({
      type: 'GET',
      url: '/api/users/' + user.id,
      dataType: 'json',
      success: function(data) {
        if (data.r) {
          localStorage.setItem('user',  JSON.stringify(data.user));
          handleSideNavigation();
        } else {
          window.location.replace('/connexion');
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

  var url = window.location;
  var e = $('#home-nav .nav-element a').filter(function() {
    return this.href == url}).addClass('menu-active');
}

function disconnect() {
  $.ajax({
    type: 'GET',
    url: '/api/users/logoff',
    dataType: 'json',
    success: function(data) {
      if (data.r) {
        localStorage.clear();
        window.location.replace('/connexion');
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

function getUserContacts(contactPage, contactPageSize, paginatorContacts) {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: '/api/users/' + user.id + '/contacts?page=' + contactPage + '&pageSize=' + contactPageSize,
    dataType: 'json',
    success: function(data) {
      $('#contacts-wait').hide();
      if (data.r) {
        $('#contacts').empty();
        if (data.contacts.length == 0) {
          $('#nocontact').show();
          return;
        }
        data.contacts.forEach(function(contact) {
          $('#contacts').append(
            `<div class="contact row bg-grey" style="display: flex; align-items: center; padding: 10px; margin-bottom: 5px">
              <b>${contact.user.firstname} ${contact.user.lastname}</b> : <span style="text-color: gray">${contact.message}</span>
              <a href="/dashboard/chat/user/${contact.user.id}" class="btn btn-primary" style="margin-left: auto">
                Accéder aux messages
              </a>
             </div>`
          );
        });
        paginatorContacts.paginate(
          data.paginator.page,
          data.paginator.pageSize,
          data.paginator.total
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

function getUserContactMessages(contact_id) {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: `/api/users/${user.id}/${contact_id}/messages`,
    dataType: 'json',
    success: function(data) {
      $('#messages-wait').hide();
      if (data.r) {
        $('#messages').empty();
        $('#contact').empty();
        $('#contact').append(`${data.contact.firstname} ${data.contact.lastname}`);
        if (data.messages.length == 0) {
          $('#nomessage').show();
          return;
        }
        data.messages.forEach(function(message) {
          var message_class = (message.author.id == user.id) ? 'msgMe' : 'msgOthers';
          var offset_col = (message.author.id == user.id) ? 'offset-6' : '';
          $('#messages').append(
            `<div class="row">
              <div class="col-6 ${offset_col}">
                <div class="message ${message_class}">
                  <h6><b>${message.author.firstname} ${message.author.lastname}</b></h6>
                  <p>${message.message}</p>
                  <small>${message.created_at}</small>
               </div>
              </div>
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
    url: `/api/users/${user.id}/${contact_id}/messages`,
    data: {message: message},
    dataType: 'json',
    success: function(data) {
      $('#wait-send-message').hide();
      if (data.r) {
        var message = data.message;

        $('#text-message').val('');
        $('#messages').prepend(
          `<div class="row">
            <div class="col-6 offset-6">
              <div class="message msgMe">
                <h6><b>${message.author.firstname} ${message.author.lastname}</b></h6>
                <p>${message.message}</p>
                <small>${message.created_at}</small>
             </div>
            </div>
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

function searchContact(search) {
  //var user = JSON.parse(localStorage.getItem('user'));
  $('#wait-searching').show();

  $.ajax({
    type: 'POST',
    url: `/api/contacts/search`,
    data: {search: search},
    dataType: 'json',
    success: function(data) {
      $('#wait-searching').hide();
      if (data.r) {
        var message = data.message;

        $('#contact-list').empty();
        data.contacts.forEach(function(contact) {
          $('#contact-list').append(
            `<tr>
              <td>${contact.firstname} ${contact.lastname}</td>
              <td>${contact.email}</td>
              <td>${ROLES[contact.role]}</td>
              <td><a href="/dashboard/chat/user/${contact.id}" class="btn btn-primary">
                Envoyer un message
              </a></td>
             </tr>`
          );
        });
        $('#contact-list-row').show();
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

function getCategories() {
  $.ajax({
    type: 'GET',
    url: `/api/forum/categories`,
    dataType: 'json',
    success: function(data) {
      $('#forums-wait').hide();
      $('#noforums').hide();
      if (data.r) {
        $('#forums').empty();
        if (data.categories.length == 0) {
          $('#noforums').show();
          return;
        }
        data.categories.forEach(function(category) {
          $('#forums').append(
            `<div class="row forum-category">
              <h2 class="col-3"><b>${category.title}</b></h2>
              <h5 class="offset-5 col-2" style="align-self: end; margin-bottom: 0">Posts: ${category.count_posts}</h5>
              <h5 class="col-2" style="align-self: end; margin-bottom: 0"><b>${category.updated_at}</b></h5>
             </div>
             <hr>
             <div class="row">
               <a href="/dashboard/forum/category/${category.id}" class="btn btn-primary" style="margin-left: auto">
                 Accéder aux posts
               </a>
               <button class="btn btn-danger" style="margin-left: 5px">
                 <i class="fas fa-trash-alt"></i>
               </button>
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

function createCategory() {
  var category_name = $('#category-name').val();
  $('#wait-creating-category').show();

  $.ajax({
    type: 'POST',
    url: `/api/forum/categories`,
    data: {name: category_name},
    dataType: 'json',
    success: function(data) {
      $('#wait-creating-category').hide();
      if (data.r) {
        $('#modal-new-category').closeModal();
        $('#category-name').val('');
        getCategories();
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

function getPosts(category_id) {
  $.ajax({
    type: 'GET',
    url: `/api/forum/categories/${category_id}/posts`,
    dataType: 'json',
    success: function(data) {
      $('#posts-wait').hide();
      $('#nopost').hide();
      if (data.r) {
        $('#posts').empty();
        $('#category').empty();
        $('#category').append(data.category.title);
        //$('#navtitle').text(`Forum - ${data.category.title}`);
        document.title = `Forum - ${data.category.title}`;
        if (data.posts.length == 0) {
          $('#nopost').show();
          return;
        }
        data.posts.forEach(function(category) {
          $('#posts').append(
            `<div class="row forum-category">
              <h2 class="col-3"><b>${category.title}</b></h2>
              <h5 class="offset-5 col-2" style="align-self: end; margin-bottom: 0">Posts: ${category.count_posts}</h5>
              <h5 class="col-2" style="align-self: end; margin-bottom: 0"><b>${category.updated_at}</b></h5>
             </div>
             <hr>
             <div class="row">
               <a href="/dashboard/forum/category/${category.id}" class="btn btn-primary" style="margin-left: auto">
                 Accéder aux posts
               </a>
               <button class="btn btn-danger" style="margin-left: 5px">
                 <i class="fas fa-trash-alt"></i>
               </button>
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
