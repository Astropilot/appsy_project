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
    window.location.replace('/connexion');
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
          text: data.errors[0]
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
          var contact_template = $('#contact-template').clone().removeClass('d-none');

          contact_template.find('b').text(`${contact.user.firstname} ${contact.user.lastname}`);
          contact_template.find('span').text(contact.message);
          contact_template.find('a').attr('href', `/dashboard/chat/user/${contact.user.id}`);

          $('#contacts').append(
            contact_template
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
          var contact_template = $('#contactsearch-template tr').clone().removeClass('d-none');

          contact_template.find('.contact-name').text(`${contact.firstname} ${contact.lastname}`);
          contact_template.find('.contact-email').text(contact.email);
          contact_template.find('.contact-role').text(ROLES[contact.role]);
          contact_template.find('a').attr('href', `/dashboard/chat/user/${contact.id}`);

          $('#contact-list').append(
            contact_template
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
          var first_cat_template = $('#category-first-template').clone().removeClass('d-none');
          var second_cat_template = $('#category-second-template').clone().removeClass('d-none');

          first_cat_template.find('.category-title').text(category.title);
          first_cat_template.find('.category-posts').text(category.count_posts);
          first_cat_template.find('.category-date').text(category.updated_at);

          second_cat_template.find('a').attr('href', `/dashboard/forum/category/${category.id}`);

          $('#forums').append(first_cat_template);
          $('#forums').append('<hr>');
          $('#forums').append(second_cat_template);
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

function getPosts(category_id, postPage, postPageSize, paginatorPosts) {
  $.ajax({
    type: 'GET',
    url: `/api/forum/categories/${category_id}/posts?page=${postPage}&pageSize=${postPageSize}`,
    dataType: 'json',
    success: function(data) {
      $('#posts-wait').hide();
      $('#nopost').hide();
      $('#post-header').hide();
      if (data.r) {
        $('#post-list').empty();
        $('#category').empty();
        $('#category').append(data.category.title);
        document.title = `Forum - ${data.category.title}`;
        if (data.posts.length == 0) {
          $('#nopost').show();
          return;
        }
        //$('#post-header').show();
        data.posts.forEach(function(post) {
          var post_template = $('#post-template tr').clone().removeClass('d-none');

          post_template.find('.post-title').append(
            `<a href="/dashboard/forum/post/${post.id}">${post.title}</a>`
          ).attr('width', '70%');
          post_template.find('.post-updated').text(post.updated_at);
          post_template.find('.post-count').text(post.count_responses);

          $('#post-list').append(post_template);
        });
        $('#post-list-row').show();
        paginatorPosts.paginate(
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
