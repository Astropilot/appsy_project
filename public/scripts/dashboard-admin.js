function searchMember(search) {
  $('#wait-search-member').show();
  $('#member-list').empty();

  $.ajax({
    type: 'POST',
    url: `/api/contacts/search`,
    data: {search: search},
    dataType: 'json',
    success: function(data) {
      var modalEditConfirm = $('#modal-confirm-edit');
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

        member_template.find('button').data('id', member.id);

        $('#member-list').append(
          member_template
        );
      });
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
