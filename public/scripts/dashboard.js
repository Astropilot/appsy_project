var ROLES = {0: 'Utilisateur', 1: 'Examinateur', 2: 'Administrateur'};
var TICKET_STATUS = {0: 'Envoyé', 1: 'En cours d\'examination', 2: 'Terminé', 3: 'Refusé'};

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
        localStorage.setItem('user',  JSON.stringify(data.user));
        handleSideNavigation();
      },
      error: function() {
        window.location.replace('/connexion');
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
  if (user.role >= 2) {
    $('#examiners-nav').show();
    $('#admin-nav').show();
  }
  if (user.role < 2)
    $('#tickets-nav').show();

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
      localStorage.clear();
      window.location.replace('/connexion');
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
    },
    complete: function() {
      $('#contacts-wait').hide();
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
    },
    complete: function() {
      $('#messages-wait').hide();
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
    },
    complete: function() {
      $('#wait-send-message').hide();
    }
  });
}

function searchContact(search, page, pageSize, paginator) {
  $('#wait-searching').show();

  $.ajax({
    type: 'POST',
    url: `/api/contacts/search`,
    data: {search: search, page: page, pageSize: pageSize},
    dataType: 'json',
    success: function(data) {
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
      paginator.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      $('#wait-searching').hide();
    }
  });
}

function getCategories() {
  $.ajax({
    type: 'GET',
    url: `/api/forum/categories`,
    dataType: 'json',
    success: function(data) {
      $('#noforums').hide();

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

        second_cat_template.find('.category-description').text(category.description);
        second_cat_template.find('a').attr('href', `/dashboard/forum/category/${category.id}`);

        $('#forums').append(first_cat_template);
        $('#forums').append('<hr>');
        $('#forums').append(second_cat_template);
      });
    },
    complete: function() {
      $('#forums-wait').hide();
    }
  });
}

function getPosts(category_id, postPage, postPageSize, paginatorPosts) {
  $.ajax({
    type: 'GET',
    url: `/api/forum/categories/${category_id}/posts?page=${postPage}&pageSize=${postPageSize}`,
    dataType: 'json',
    success: function(data) {
      $('#nopost').hide();
      $('#post-list').empty();
      $('#category').empty();
      $('#category').append(data.category.title);
      document.title = `Forum - ${data.category.title}`;
      if (data.posts.length == 0) {
        $('#nopost').show();
        return;
      }
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
    },
    complete: function() {
      $('#posts-wait').hide();
    }
  });
}

function getPostResponses(post_id, responsePage, responsePageSize, paginatorResponses) {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: `/api/forum/posts/${post_id}/responses?page=${responsePage}&pageSize=${responsePageSize}`,
    dataType: 'json',
    success: function(data) {
      $('#response-list').empty();
      $('#post-title').empty();
      $('#post-title').append(data.post.title);
      document.title = `Forum - ${data.post.title}`;

      if (user.id == data.post.author || user.role >= 2) {
        $('#btn-delete-post').removeClass('d-none');
      }

      data.responses.forEach(function(response) {
        var response_template = $('#response-template tr').clone().removeClass('d-none');

        response_template.find('.response-author').append(
          `${response.author.lastname} ${response.author.firstname}`
        );
        response_template.find('.response-content').append($.parseHTML(response.content)).attr('width', '60%');
        response_template.find('.response-created').text(response.created_at);
        response_template.find('.response-updated').text(response.updated_at);

        if (user.id == response.author.id || user.role >= 2) {
          response_template.find('.btn-edit-response').removeClass('d-none');
        }
        if ((user.id == response.author.id || user.role >= 2) && data.post.id != response.id) {
          response_template.find('.btn-delete-response').removeClass('d-none');
          response_template.find('.btn-delete-response').data('id', response.id);
        }

        $('#response-list').append(response_template);
      });
      $('.btn-delete-response').on('click', function() {
        deleteResponse(data.post.id, $(this).data('id'));
      });
      $('#responses-list-row').show();
      paginatorResponses.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      $('#responses-wait').hide();
    }
  });
}

function createPost(category_id, title, content) {
  $('#wait-creating-post').show();

  $.ajax({
    type: 'POST',
    url: `/api/forum/categories/${category_id}/posts`,
    data: {title: title, content: content},
    dataType: 'json',
    success: function(data) {
      $('#modal-new-post').closeModal();
      $('#post-title').val('');
      $('.wysiwyg .editor').html('');
      getPosts(category_id, 1, postPageSize, paginatorPosts);
    },
    complete: function() {
      $('#wait-creating-post').hide();
    }
  });
}

function getUserProfile() {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'GET',
    url: `/api/users/${user.id}`,
    dataType: 'json',
    success: function(data) {
      var member = data.user;
      $('#profile-name').text(`${member.firstname} ${member.lastname}`);

      $('#profile-email').val(member.email);
      $('#profile-firstname').val(member.firstname);
      $('#profile-lastname').val(member.lastname);

      $('#profile-name i').hide();
      $('#profile-edit').show();
    },
    complete: function() {
      $('#profile-wait').hide();
    }
  });
}

function updateUserProfile(email, firstname, lastname, password, passwordcheck) {
  var user = JSON.parse(localStorage.getItem('user'));

  $.ajax({
    type: 'PUT',
    url: `/api/users/${user.id}`,
    data: {email: email, firstname: firstname, lastname: lastname, password: password, passwordcheck: passwordcheck},
    dataType: 'json',
    success: function(data) {
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 4000,
        text: data.message
      }).show();
    }
  });
}

function createResponse(post_id, content) {
  $('#wait-creating-response').show();

  $.ajax({
    type: 'POST',
    url: `/api/forum/posts/${post_id}/responses`,
    data: {content: content},
    dataType: 'json',
    success: function(data) {
      $('.wysiwyg .editor').html('');
      getPostResponses(post_id, 1, responsePageSize, paginatorResponses);
    },
    complete: function() {
      $('#wait-creating-response').hide();
    }
  });
}

function deleteResponse(post_id, response_id) {
  $.ajax({
    type: 'DELETE',
    url: `/api/forum/posts/${post_id}/responses/${response_id}`,
    dataType: 'json',
    success: function(data) {
      getPostResponses(post_id, 1, responsePageSize, paginatorResponses);
    }
  });
}

function deletePost(post_id) {
  $.ajax({
    type: 'DELETE',
    url: `/api/forum/posts/${post_id}`,
    dataType: 'json',
    success: function(data) {
      window.location.replace('/dashboard/forum');
    }
  });
}

function getUserTickets(page, pageSize, paginator) {
  var user = JSON.parse(localStorage.getItem('user'));

  $('#tickets-wait').show();
  $.ajax({
    type: 'GET',
    url: '/api/users/' + user.id + '/tickets?page=' + page + '&pageSize=' + pageSize,
    dataType: 'json',
    success: function(data) {
      $('#tickets-list').empty();
      if (data.tickets.length == 0) {
        $('#noticket').show();
        return;
      }
      data.tickets.forEach(function(ticket) {
        var ticket_template = $('#ticket-template tr').clone().removeClass('d-none');

        ticket_template.addClass('status-' + ticket.status);
        ticket_template.find('.ticket-title').text(ticket.title);
        ticket_template.find('.ticket-status').text(TICKET_STATUS[ticket.status]);
        ticket_template.find('.ticket-created').text(ticket.created_at);
        ticket_template.find('.ticket-updated').text(ticket.updated_at);
        ticket_template.find('.btn-view-ticket').attr('href', `/dashboard/ticket/${ticket.id}`);

        $('#tickets-list').append(
          ticket_template
        );
      });
      $('#tickets-list-row').show();
      paginator.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      $('#tickets-wait').hide();
    }
  });
}

function getTicketComments(ticket_id, page, pageSize, paginator) {
  $('#comments-wait').show();
  $.ajax({
    type: 'GET',
    url: '/api/tickets/' + ticket_id + '/comments?page=' + page + '&pageSize=' + pageSize,
    dataType: 'json',
    success: function(data) {
      $('#comments-list').empty();
      $('#ticket-title').text(data.ticket.title).find('i').hide();
      $('#ticket-content').html($.parseHTML(data.ticket.content));
      if (data.comments.length == 0) {
        $('#nocomment').show();
        return;
      }
      data.comments.forEach(function(comment) {
        var comment_template = $('#comment-template tr').clone().removeClass('d-none');

        comment_template.find('.comment-author').text(`${comment.author.firstname} ${comment.author.lastname}`);
        comment_template.find('.comment-content').html($.parseHTML(comment.content));
        comment_template.find('.comment-created').text(comment.created_at);

        $('#comments-list').append(
          comment_template
        );
      });
      $('#comments-list-row').show();
      paginator.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      $('#comments-wait').hide();
    }
  });
}

function createTicket(title, content) {
  var user = JSON.parse(localStorage.getItem('user'));
  $('#wait-creating-ticket').show();

  $.ajax({
    type: 'POST',
    url: `/api/users/${user.id}/tickets`,
    data: {title: title, content: content},
    dataType: 'json',
    success: function(data) {
      $('#modal-new-ticket').closeModal();
      getUserTickets(1, ticketPageSize, paginatorTickets);
    },
    complete: function() {
      $('#wait-creating-ticket').hide();
    }
  });
}
