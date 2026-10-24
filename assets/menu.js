var arraydata = [];

function wmenuurl(name, menu, item) {
  return wmenuroutes[name]
    .replace('__MENU__', encodeURIComponent(menu))
    .replace('__ITEM__', encodeURIComponent(item));
}

function itemdepth(li) {
  var match = /menu-item-depth-(\d+)/.exec(li.className);
  return match ? parseInt(match[1], 10) : 0;
}

function getmenus() {
  arraydata = [];
  // last item seen at each depth, the parent of an item is the last one seen one level up
  var lastatdepth = [];

  $('#menu-to-edit li.menu-item').each(function(index) {
    var depth = itemdepth(this);
    var id = this.id.replace('menu-item-', '');
    var parent = depth > 0 && lastatdepth[depth - 1] ? lastatdepth[depth - 1] : 0;
    lastatdepth[depth] = id;
    lastatdepth.length = depth + 1;

    var settings = $(this).find('.menu-item-settings');
    arraydata.push({
      id: id,
      parent: parent,
      sort: index,
      label: settings.find('.edit-menu-item-title').val(),
      link: settings.find('.edit-menu-item-url').val(),
      class: settings.find('.edit-menu-item-classes').val(),
      role_id: settings.find('.edit-menu-item-role').val()
    });
  });
  // structure and item content are saved together in one request
  actualizarmenu();
}

function showerror(xhr) {
  var message = 'Something went wrong, please try again.';
  if (xhr.responseJSON && xhr.responseJSON.errors) {
    message = $.map(xhr.responseJSON.errors, function(messages) {
      return messages.join('\n');
    }).join('\n');
  } else if (xhr.responseJSON && xhr.responseJSON.message) {
    message = xhr.responseJSON.message;
  }
  alert(message);
}

function addcustommenu() {
  $('#spincustomu').show();

  $.ajax({
    dataType: 'json',
    data: {
      label: $('#custom-menu-item-name').val(),
      link: $('#custom-menu-item-url').val(),
      role_id: $('#custom-menu-item-role').val()
    },
    url: wmenuurl('itemsStore', $('#idmenu').val()),
    type: 'POST',
    success: function(response) {
      window.location.reload();
    },
    error: showerror,
    complete: function() {
      $('#spincustomu').hide();
    }
  });
}

function updateitem(id) {
  $.ajax({
    dataType: 'json',
    data: {
      _method: 'PUT',
      label: $('#idlabelmenu_' + id).val(),
      link: $('#url_menu_' + id).val(),
      class: $('#clases_menu_' + id).val(),
      role_id: $('#role_menu_' + id).val()
    },
    url: wmenuurl('itemsUpdate', $('#idmenu').val(), id),
    type: 'POST',
    beforeSend: function(xhr) {
      $('#spincustomu2').show();
    },
    error: showerror,
    complete: function() {
      $('#spincustomu2').hide();
    }
  });
}

function actualizarmenu() {
  $.ajax({
    dataType: 'json',
    data: {
      _method: 'PUT',
      items: arraydata,
      name: $('#menu-name').val()
    },
    url: wmenuurl('menusUpdate', $('#idmenu').val()),
    type: 'POST',
    beforeSend: function(xhr) {
      $('#spincustomu2').show();
    },
    error: showerror,
    complete: function() {
      $('#spincustomu2').hide();
    }
  });
}

function deleteitem(id) {
  $.ajax({
    data: {
      _method: 'DELETE'
    },
    url: wmenuurl('itemsDestroy', $('#idmenu').val(), id),
    type: 'POST',
    error: showerror
  });
}

function deletemenu() {
  var r = confirm('Do you want to delete this menu ?');
  if (r == true) {
    $.ajax({
      data: {
        _method: 'DELETE'
      },
      url: wmenuurl('menusDestroy', $('#idmenu').val()),
      type: 'POST',
      beforeSend: function(xhr) {
        $('#spincustomu2').show();
      },
      success: function() {
        window.location = menuwr;
      },
      error: showerror,
      complete: function() {
        $('#spincustomu2').hide();
      }
    });
  } else {
    return false;
  }
}

function createnewmenu() {
  if (!!$('#menu-name').val()) {
    $.ajax({
      dataType: 'json',
      data: {
        name: $('#menu-name').val()
      },
      url: wmenuroutes.menusStore,
      type: 'POST',
      success: function(response) {
        window.location = menuwr + '?menu=' + response.data.id;
      },
      error: showerror
    });
  } else {
    alert('Enter menu name!');
    $('#menu-name').focus();
    return false;
  }
}

function insertParam(key, value) {
  key = encodeURI(key);
  value = encodeURI(value);

  var kvp = document.location.search.substr(1).split('&');

  var i = kvp.length;
  var x;
  while (i--) {
    x = kvp[i].split('=');

    if (x[0] == key) {
      x[1] = value;
      kvp[i] = x.join('=');
      break;
    }
  }

  if (i < 0) {
    kvp[kvp.length] = [key, value].join('=');
  }

  //this will reload the page, it's likely better to store this until finished
  document.location.search = kvp.join('&');
}

wpNavMenu.registerChange = function() {
  getmenus();
};
