/* Menu builder: drag and drop with SortableJS, saved through the wmenu.* JSON routes. No jQuery needed. */
(function () {
  'use strict';

  var root = document.getElementById('wmenu');
  var config = window.wmenuConfig;
  if (!root || !config) {
    return;
  }

  var strings = config.strings || {};
  var menuId = root.getAttribute('data-menu-id');
  var tree = root.querySelector('.wmenu-root');
  var dirty = false;

  function t(key) {
    return strings[key] || key;
  }

  function url(name, item) {
    return config.routes[name]
      .replace('__MENU__', encodeURIComponent(menuId))
      .replace('__ITEM__', encodeURIComponent(item));
  }

  // --- requests -------------------------------------------------------------

  function request(method, target, data) {
    return fetch(target, {
      method: method,
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': config.csrf
      },
      body: data ? JSON.stringify(data) : undefined
    }).then(function (response) {
      if (response.status === 204) {
        return null;
      }
      return response.json().catch(function () {
        return {};
      }).then(function (body) {
        if (!response.ok) {
          throw body;
        }
        return body;
      });
    });
  }

  function errorMessage(body) {
    if (body && body.errors) {
      return Object.keys(body.errors).map(function (key) {
        return body.errors[key].join(' ');
      }).join('\n');
    }
    return (body && body.message) || t('error');
  }

  // --- feedback ---------------------------------------------------------------

  function toast(message, type) {
    var box = root.querySelector('[data-toasts]');
    var el = document.createElement('div');
    el.className = 'wmenu-toast' + (type === 'error' ? ' wmenu-toast-error' : '');
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.textContent = message;
    box.appendChild(el);
    setTimeout(function () {
      el.remove();
    }, type === 'error' ? 8000 : 3000);
  }

  function fail(body) {
    toast(errorMessage(body), 'error');
  }

  // open the items whose fields were refused (errors keyed "items.N.field") and mark the fields
  function markInvalid(body) {
    if (!body || !body.errors) {
      return;
    }
    var items = tree.querySelectorAll('.wmenu-item');
    var first = null;
    Object.keys(body.errors).forEach(function (key) {
      var match = /^items\.(\d+)\.(\w+)$/.exec(key);
      var item = match && items[Number(match[1])];
      var input = item && field(item, match[2]);
      if (!input) {
        return;
      }
      input.setAttribute('aria-invalid', 'true');
      if (item.querySelector(':scope > .wmenu-item-settings').hidden) {
        toggle(item);
      }
      first = first || input;
    });
    if (first) {
      first.focus();
    }
  }

  function setDirty(value) {
    dirty = value;
    var status = root.querySelector('[data-status]');
    if (status) {
      status.textContent = value ? t('unsaved') : '';
    }
  }

  window.addEventListener('beforeunload', function (event) {
    if (dirty) {
      event.preventDefault();
      event.returnValue = '';
    }
  });

  // --- tree -------------------------------------------------------------------

  function sortable(list) {
    if (!window.Sortable) {
      return;
    }
    window.Sortable.create(list, {
      group: 'wmenu',
      handle: '.wmenu-handle',
      // mouse/touch events instead of native drag and drop: same behaviour in every browser and on touch screens
      forceFallback: true,
      animation: 150,
      fallbackOnBody: true,
      swapThreshold: 0.65,
      onStart: function () {
        root.classList.add('wmenu-dragging');
      },
      onEnd: function (event) {
        root.classList.remove('wmenu-dragging');
        if (event.from !== event.to || event.oldIndex !== event.newIndex) {
          setDirty(true);
        }
      }
    });
  }

  function itemOf(el) {
    return el.closest('.wmenu-item');
  }

  function field(item, name) {
    return item.querySelector(':scope > .wmenu-item-settings [data-field="' + name + '"]');
  }

  function children(item) {
    return item.querySelector(':scope > .wmenu-list');
  }

  // items in display order with their parent, ready for PUT menus/{menu}
  function serialize() {
    var items = [];
    (function walk(list, parent) {
      Array.prototype.forEach.call(list.children, function (item) {
        var id = item.getAttribute('data-id');
        var data = {
          id: id,
          parent: parent,
          sort: items.length,
          label: field(item, 'label').value,
          link: field(item, 'link').value,
          class: field(item, 'class').value,
          target: field(item, 'target').value
        };
        var role = field(item, 'role_id');
        if (role) {
          data.role_id = role.value;
        }
        items.push(data);
        walk(children(item), id);
      });
    })(tree, 0);
    return items;
  }

  function refreshEmpty() {
    var empty = root.querySelector('[data-empty]');
    if (empty) {
      empty.hidden = tree.children.length > 0;
    }
  }

  function addItemToTree(data) {
    var html = document.getElementById('wmenu-item-template').innerHTML.replace(/__ID__/g, String(Number(data.id)));
    var holder = document.createElement('ul');
    holder.innerHTML = html.trim();
    var item = holder.firstElementChild;

    item.querySelector('.wmenu-item-title').textContent = data.label;
    item.querySelector('.wmenu-item-link').textContent = data.link || '';
    item.querySelector('.wmenu-toggle').setAttribute('aria-label', t('edit').replace(':label', data.label));
    field(item, 'label').value = data.label;
    field(item, 'link').value = data.link || '';
    field(item, 'class').value = data.class || '';
    field(item, 'target').value = data.target || '';
    var role = field(item, 'role_id');
    if (role) {
      role.value = String(data.role_id || 0);
    }

    tree.appendChild(item);
    sortable(children(item));
    refreshEmpty();
  }

  function move(item, action) {
    var list = item.parentElement;
    if (action === 'up' && item.previousElementSibling) {
      list.insertBefore(item, item.previousElementSibling);
    } else if (action === 'down' && item.nextElementSibling) {
      list.insertBefore(item.nextElementSibling, item);
    } else if (action === 'in' && item.previousElementSibling) {
      children(item.previousElementSibling).appendChild(item);
    } else if (action === 'out' && list !== tree) {
      var parent = itemOf(list);
      parent.parentElement.insertBefore(item, parent.nextElementSibling);
    } else {
      return;
    }
    setDirty(true);
  }

  function toggle(item) {
    var settings = item.querySelector(':scope > .wmenu-item-settings');
    var button = item.querySelector(':scope > .wmenu-item-bar .wmenu-toggle');
    settings.hidden = !settings.hidden;
    button.setAttribute('aria-expanded', String(!settings.hidden));
    item.classList.toggle('wmenu-item-open', !settings.hidden);
    if (!settings.hidden) {
      field(item, 'label').focus();
    } else {
      button.focus();
    }
  }

  function deleteItem(item) {
    if (!window.confirm(t('confirm_delete_item'))) {
      return;
    }
    request('DELETE', url('itemsDestroy', item.getAttribute('data-id'))).then(function () {
      // the server moves the sub items one level up, do the same here
      var list = children(item);
      while (list.firstElementChild) {
        item.parentElement.insertBefore(list.firstElementChild, item);
      }
      item.remove();
      refreshEmpty();
      toast(t('item_deleted'));
    }).catch(fail);
  }

  // --- events -----------------------------------------------------------------

  root.addEventListener('click', function (event) {
    var button = event.target.closest('[data-action]');
    if (!button || !root.contains(button)) {
      return;
    }
    var action = button.getAttribute('data-action');
    var item = itemOf(button);

    if (action === 'delete-menu') {
      deleteMenu();
    } else if (!item) {
      return;
    } else if (action === 'toggle') {
      toggle(item);
    } else if (action === 'delete') {
      deleteItem(item);
    } else {
      move(item, action);
      button.focus();
    }
  });

  root.addEventListener('input', function (event) {
    event.target.removeAttribute('aria-invalid');
    var item = itemOf(event.target);
    if (event.target.getAttribute('data-field') === 'label' && item) {
      item.querySelector(':scope > .wmenu-item-bar .wmenu-item-title').textContent = event.target.value;
    }
    if (event.target.getAttribute('data-field') === 'link' && item) {
      item.querySelector(':scope > .wmenu-item-bar .wmenu-item-link').textContent = event.target.value;
    }
    if (event.target.closest('[data-form="save"]')) {
      setDirty(true);
    }
  });

  root.addEventListener('change', function (event) {
    if (event.target.closest('[data-form="save"]')) {
      setDirty(true);
    }
  });

  root.addEventListener('submit', function (event) {
    var form = event.target;
    var type = form.getAttribute('data-form');
    if (!type) {
      return;
    }
    event.preventDefault();

    if (type === 'create') {
      request('POST', config.routes.menusStore, { name: form.elements.name.value }).then(function (body) {
        window.location = config.currentUrl + '?menu=' + encodeURIComponent(body.data.id);
      }).catch(fail);
    } else if (type === 'add') {
      var data = {
        label: form.elements.label.value,
        link: form.elements.link.value,
        target: form.elements.target.value
      };
      if (form.elements.role_id) {
        data.role_id = form.elements.role_id.value;
      }
      request('POST', url('itemsStore'), data).then(function (body) {
        addItemToTree(body.data);
        form.reset();
        form.elements.label.focus();
        toast(t('item_added'));
      }).catch(fail);
    } else if (type === 'save') {
      save(form);
    }
  });

  function save(form) {
    var button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = t('saving');
    request('PUT', url('menusUpdate'), { name: form.elements.name.value, items: serialize() }).then(function () {
      setDirty(false);
      toast(t('saved'));
    }).catch(function (body) {
      fail(body);
      markInvalid(body);
    }).then(function () {
      button.disabled = false;
      button.textContent = t('save');
    });
  }

  function deleteMenu() {
    if (!window.confirm(t('confirm_delete_menu'))) {
      return;
    }
    request('DELETE', url('menusDestroy')).then(function () {
      setDirty(false);
      window.location = config.currentUrl;
    }).catch(fail);
  }

  if (tree) {
    sortable(tree);
    Array.prototype.forEach.call(tree.querySelectorAll('.wmenu-list'), sortable);
  }
})();
