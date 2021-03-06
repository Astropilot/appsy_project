function searchMember(search, page, pageSize, paginator) {
  $('#wait-search-member').show();
  $('#member-list').empty();

  $.ajax({
    type: 'POST',
    url: '/api/contacts/search',
    data: {search: search, page: page, pageSize: pageSize},
    dataType: 'json',
    success: function(data) {
      var modalEditConfirm = $('#modal-confirm-edit');
      var modalEditUser = $('#modal-edit-user');
      var modalDeleteConfirm = $('#modal-confirm-delete');

      data.contacts.forEach(function(member) {
        var member_template = $('#member-template tr').clone().attr('data-id', member.id);

        member_template.find('.member-name').text(`${member.firstname} ${member.lastname}`);
        member_template.find('.member-email').text(member.email);
        member_template.find('.member-role').append(
          `<form class="form">
            <select class="form-field" style="width: 100% !important">
              <option value="0">Utilisateur</option>
              <option value="1">Examinateur</option>
              <option value="2">Administrateur</option>
             </select>
           </form>`
        ) .find('select').attr('data-default', member.role)
          .find('option[value="' + member.role + '"]').prop('selected', true);

        member_template.find('.member-role').find('select').on('change', function() {
          modalEditConfirm
            .data('id', member.id)
            .data('role', this.value)
            .removeData('banned')
            .showModal();
        });

        member_template.find('.member-banned').append(
          `<form class="form">
            <select class="form-field" style="width: 100% !important">
              <option value="0">Non</option>
              <option value="1">Oui</option>
             </select>
           </form>`
        ) .find('select').attr('data-default', member.banned)
          .find('option[value="' + member.banned + '"]').prop('selected', true);

        member_template.find('.member-banned').find('select').on('change', function() {
          modalEditConfirm
            .data('id', member.id)
            .data('banned', this.value)
            .removeData('role')
            .showModal();
        });

        member_template.find('.btn-modify-user').on('click', function() {
          modalEditUser.data('id', member.id);
          modalEditUser.find('#user-email').val(member.email);
          modalEditUser.find('#user-firstname').val(member.firstname);
          modalEditUser.find('#user-lastname').val(member.lastname);
          modalEditUser.showModal();
        });

        member_template.find('.btn-delete-user').on('click', function() {
          modalDeleteConfirm
            .data('id', member.id)
            .showModal();
        });

        $('#member-list').append(
          member_template
        );
      });
      paginator.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      $('#wait-search-member').hide();
    }
  });
}

function fastEditUser(member_id, member_role, member_banned) {
  $.ajax({
    type: 'PUT',
    url: `/admin/api/users/${member_id}`,
    data: {role: member_role, banned: member_banned},
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

function completeEditUser(member_id, email, firstname, lastname) {
  $('#wait-edit-user').show();

  $.ajax({
    type: 'PUT',
    url: `/admin/api/users/${member_id}`,
    data: {email: email, firstname: firstname, lastname: lastname},
    dataType: 'json',
    success: function(data) {
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 4000,
        text: data.message
      }).show();
      $('#modal-edit-user').closeModal();
      searchMember($('#member-search').val(), 1, contactPageSize, paginatorContacts);
    },
    complete: function() {
      $('#wait-edit-user').hide();
    }
  });
}

function deleteUser(user_id) {
  $.ajax({
    type: 'DELETE',
    url: `/api/users/${user_id}`,
    dataType: 'json',
    success: function() {
      searchMember($('#member-search').val(), 1, contactPageSize, paginatorContacts);
    }
  });
}

function inviteMember(email, firstname, lastname, role, lang) {
  $('#wait-invite-member').show();

  $.ajax({
    type: 'POST',
    url: `/admin/api/users`,
    data: {email: email, firstname: firstname, lastname: lastname, role: role, lang: lang},
    dataType: 'json',
    success: function(data) {
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 4000,
        text: data.message
      }).show();
    },
    complete: function() {
      $('#wait-invite-member').hide();
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
        second_cat_template.find('.btn-delete-category').data('id', category.id).on('click', function() {
          deleteCategory($(this).data('id'));
        });
        second_cat_template.find('.btn-move-up').data('id', category.id).on('click', function() {
          moveCategory($(this).data('id'),'up');
        });
        second_cat_template.find('.btn-move-down').data('id', category.id).on('click', function() {
          moveCategory($(this).data('id'), 'down');
        });

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

function createCategory(category_name, category_description) {
  $('#wait-creating-category').show();

  $.ajax({
    type: 'POST',
    url: `/api/forum/categories`,
    data: {name: category_name, description: category_description},
    dataType: 'json',
    success: function(data) {
      $('#modal-new-category').closeModal();
      $('#category-name').val('');
      getCategories();
    },
    complete: function() {
      $('#wait-creating-category').hide();
    }
  });
}

function moveCategory(category_id, direction) {
  $.ajax({
    type: 'POST',
    url: `/api/forum/categories/${category_id}/reorder`,
    data: {direction: direction},
    dataType: 'json',
    success: function() {
      getCategories();
    }
  });
}

function deleteCategory(category_id) {
  $.ajax({
    type: 'DELETE',
    url: `/api/forum/categories/${category_id}`,
    dataType: 'json',
    success: function() {
      getCategories();
    }
  });
}

function getFAQ() {
  $('#nofaq').hide();

  $.ajax({
    type: 'GET',
    url: '/api/faq/questions',
    dataType: 'json',
    success: function(data) {
      $('#faq-questions').empty();
      if (data.faq.length == 0) {
        $('#nofaq').show();
        return;
      }
      data.faq.forEach(function(faq) {
        var faq_template = $('#faq-template').clone().removeClass('d-none');

        faq_template.find('h3').text(faq.question);
        faq_template.find('p').text(faq.answer);

        faq_template.find('.btn-delete-faq').data('id', faq.id).on('click', function() {
          deleteFAQ($(this).data('id'));
        });

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

function createFAQ(question, answer) {
  $('#wait-creating-faq').show();

  $.ajax({
    type: 'POST',
    url: `/api/faq/questions`,
    data: {question: question, answer: answer},
    dataType: 'json',
    success: function(data) {
      $('#modal-new-faq').closeModal();
      getFAQ();
    },
    complete: function() {
      $('#wait-creating-faq').hide();
    }
  });
}

function deleteFAQ(faq_id) {
  $.ajax({
    type: 'DELETE',
    url: `/api/faq/questions/${faq_id}`,
    dataType: 'json',
    success: function() {
      getFAQ();
    }
  });
}

function getAdminTickets(search, page, pageSize, paginator) {
  var user = JSON.parse(localStorage.getItem('user'));
  var modalViewTicket = $('#modal-view-ticket');
  var modalUpdateTicket = $('#modal-confirm-update-ticket');

  $('#tickets-wait').show();
  $.ajax({
    type: 'POST',
    url: '/admin/api/tickets',
    data: {search: search, page: page, pageSize: pageSize},
    dataType: 'json',
    success: function(data) {
      $('#tickets-list').empty();
      if (data.tickets.length == 0) {
        $('#noticket').show();
        return;
      }
      data.tickets.forEach(function(ticket) {
        var ticket_template = $('#ticket-template tr').clone().removeClass('d-none').attr('data-id', ticket.id);

        ticket_template.addClass('status-' + ticket.status);
        ticket_template.find('.ticket-title').text(ticket.title);
        ticket_template.find('.ticket-author').text(`${ticket.author.firstname} ${ticket.author.lastname}`);
        ticket_template.find('.ticket-status').append(
          `<form class="form">
             <select class="form-field" style="width: 100% !important">
               <option value="0">Envoyé</option>
               <option value="1">En cours d'examination</option>
               <option value="2">Terminé</option>
               <option value="3">Refusé</option>
              </select>
           </form>`
        ) .find('select').attr('data-default', ticket.status)
          .find('option[value="' + ticket.status + '"]').prop('selected', true);

        ticket_template.find('.ticket-status').find('select').on('change', function() {
          modalUpdateTicket
            .data('id', ticket.id)
            .data('status', this.value)
            .showModal();
        });


        ticket_template.find('.ticket-created').text(ticket.created_at);
        ticket_template.find('.ticket-updated').text(ticket.updated_at);

        ticket_template.find('.btn-view-ticket').on('click', function() {
          modalViewTicket.data('id', ticket.id);
          modalViewTicket.find('#ticket-content').html($.parseHTML(ticket.content));
          modalViewTicket.find('#ticket-title').text(ticket.title);
          modalViewTicket.showModal();
          getTicketComments(ticket.id, 1, commentPageSize, paginatorComments);
        });

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
  var modalViewTicket = $('#modal-view-ticket');

  modalViewTicket.find('#comments-wait').show();
  $.ajax({
    type: 'GET',
    url: '/api/tickets/' + ticket_id + '/comments?page=' + page + '&pageSize=' + pageSize,
    dataType: 'json',
    success: function(data) {
      modalViewTicket.find('#comments-list').empty();
      modalViewTicket.find('#comments-list-row').hide();
      modalViewTicket.find('#nocomment').hide();
      if (data.comments.length == 0) {
        modalViewTicket.find('#nocomment').show();
        return;
      }
      data.comments.forEach(function(comment) {
        var comment_template = $('#comment-template tr').clone().removeClass('d-none');

        comment_template.find('.comment-author').text(`${comment.author.firstname} ${comment.author.lastname}`);
        comment_template.find('.comment-content').html($.parseHTML(comment.content));
        comment_template.find('.comment-created').text(comment.created_at);

        modalViewTicket.find('#comments-list').append(
          comment_template
        );
      });
      modalViewTicket.find('#comments-list-row').show();
      paginator.paginate(
        data.paginator.page,
        data.paginator.pageSize,
        data.paginator.total
      );
    },
    complete: function() {
      modalViewTicket.find('#comments-wait').hide();
    }
  });
}

function updateTicketStatus(ticket_id, ticket_status) {
  $.ajax({
    type: 'PUT',
    url: `/admin/api/tickets/${ticket_id}`,
    data: {status: ticket_status},
    dataType: 'json',
    success: function(data) {
      new Noty({
        theme: 'metroui',
        type: 'success',
        layout: 'centerRight',
        timeout: 4000,
        text: data.message
      }).show();
      getAdminTickets($('#ticket-search').val(), 1, ticketPageSize, paginatorTickets);
    }
  });
}

function createTicketComment(ticket_id, content) {
  var user = JSON.parse(localStorage.getItem('user'));
  $('#wait-creating-comment').show();

  $.ajax({
    type: 'POST',
    url: `/admin/api/tickets/${ticket_id}/comments`,
    data: {author: user.id, content: content},
    dataType: 'json',
    success: function(data) {
      $('.wysiwyg .editor').html('');
      getTicketComments(ticket_id, 1, commentPageSize, paginatorComments);
    },
    complete: function() {
      $('#wait-creating-comment').hide();
    }
  });
}
