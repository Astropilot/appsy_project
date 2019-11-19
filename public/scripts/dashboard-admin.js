function searchMember(search) {
  $('#wait-search-member').show();
  $('#member-list').empty();

  $.ajax({
    type: 'POST',
    url: `/api/contacts/search`,
    data: {search: search},
    dataType: 'json',
    success: function(data) {
      $('#wait-search-member').hide();
      if (data.r) {
        data.contacts.forEach(function(member) {
          var member_template = $('#member-template tr').clone();

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
          ).find('option[value="' + member.role + '"]').attr('selected', '');
          member_template.find('.member-banned').append(
            `<form class="form">
              <select class="form-field" style="width: 100% !important">
                <option value="0"></option>
                <option value="1">Banni</option>
               </select>
             </form>`
          ).find('option[value="' + member.banned + '"]').attr('selected', '');
          member_template.find('button').data('id', member.id);

          $('#member-list').append(
            member_template
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

function inviteMember(email, firstname, lastname, role) {
  $('#wait-invite-member').show();

  $.ajax({
    type: 'POST',
    url: `/admin/api/users`,
    data: {email: email, firstname: firstname, lastname: lastname, role: role},
    dataType: 'json',
    success: function(data) {
      $('#wait-invite-member').hide();
      if (data.r) {
        new Noty({
          theme: 'metroui',
          type: 'success',
          layout: 'centerRight',
          timeout: 4000,
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
