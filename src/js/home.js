const apiBase = 'http://cop4431-jonathonf.online/backend/api';

let allContacts = [];

function getQueryValue(name) {
  return new URLSearchParams(window.location.search).get(name);
}

function getUserId() {
  const userId = getQueryValue('ID') || localStorage.getItem('ID');

  if (!userId || Number.isNaN(Number(userId))) {
    localStorage.removeItem('ID');

    if (!window.location.pathname.endsWith('loginPage.html')) {
      window.location.href = 'loginPage.html';
    }

    return null;
  }

  localStorage.setItem('ID', String(userId));
  return Number(userId);
}

// Normalizes records between viewContacts.php (PascalCase) and searchContact.php (camelCase)
function normalizeContact(contact) {
  return {
    id: contact.id ?? contact.ID,
    firstName: contact.firstName ?? contact.FirstName ?? '',
    lastName: contact.lastName ?? contact.LastName ?? '',
    phone: contact.phoneNumber ?? contact.Phone ?? ''
  };
}

function readLoginCookie() {
  const name = "user=";
  const decodedCookie = decodeURIComponent(document.cookie);
  const ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i].trim();
    if (c.indexOf(name) === 0) {
      try {
        return JSON.parse(c.substring(name.length, c.length));
      } catch (_) {}
    }
  }

  const getCookieVal = (k) => {
    const match = document.cookie.match(new RegExp('(^| )' + k + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : '';
  };

  return {
    firstName: getCookieVal('firstName') || getCookieVal('FirstName'),
    lastName: getCookieVal('lastName') || getCookieVal('LastName')
  };
}

function getStoredUserData() {
  let first = localStorage.getItem('firstName') || localStorage.getItem('FirstName') || localStorage.getItem('first_name') || '';
  let last = localStorage.getItem('lastName') || localStorage.getItem('LastName') || localStorage.getItem('last_name') || '';

  // Check URL params
  if (!first) first = getQueryValue('firstName') || getQueryValue('first') || '';
  if (!last) last = getQueryValue('lastName') || getQueryValue('last') || '';

  // Check cookies
  if (!first || !last) {
    const cookieData = readLoginCookie();
    if (!first && cookieData.firstName) first = cookieData.firstName;
    if (!last && cookieData.lastName) last = cookieData.lastName;
  }

  // Check JSON user blobs in localStorage
  const jsonKeys = ['user', 'userData', 'currentUser', 'userInfo', 'loginData'];
  for (const key of jsonKeys) {
    if (first && last) break;
    const item = localStorage.getItem(key);
    if (item) {
      try {
        const parsed = JSON.parse(item);
        first = first || parsed.firstName || parsed.FirstName || parsed.first_name || '';
        last = last || parsed.lastName || parsed.LastName || parsed.last_name || '';
      } catch (_) {}
    }
  }

  return { firstName: first, lastName: last };
}

function setupProfileBanner() {
  const { firstName, lastName } = getStoredUserData();
  const displayName = `${firstName} ${lastName}`.trim();

  const userNameEl = document.getElementById('userNameDisplay');
  if (userNameEl && displayName) {
    userNameEl.textContent = displayName;
  }
}

async function updateProfileArrowTarget() {
  const userId = getUserId();
  const profileArrow = document.querySelector('.profile-banner .arrow-btn');
  const userNameEl = document.getElementById('userNameDisplay');

  if (!profileArrow || !userId) return;

  try {
    const response = await fetch(`${apiBase}/userContact.php?userId=${userId}`);
    const data = await response.json();

    if (!response.ok || data.error) {
      throw new Error(data.error || 'Unable to load user contact.');
    }

    const rawContact = Array.isArray(data)
      ? data[0]
      : data.contact || data.result || data.results?.[0] || data;

    const contact = normalizeContact(rawContact);

    const displayName = `${contact.firstName} ${contact.lastName}`.trim();
    if (userNameEl && displayName) {
      userNameEl.textContent = displayName;
    }

    if (contact.id) {
      profileArrow.href =
        `contactPage.html?userId=${userId}&id=${contact.id}&contactId=${contact.id}`;

      profileArrow.onclick = () => {
        localStorage.setItem('selectedContactId', String(contact.id));
        localStorage.setItem('contactId', String(contact.id));
      };
    }
    else{
      profileArrow.href = `contactPage.html?userId=${userId}`;
    }
  } catch (error) {
    userNameEl.textContent = 'Your Name';
  }
}

function renderContacts(contacts) {
  const list = document.getElementById('contactList');
  const userId = getUserId();
  if (!list) return;

  list.innerHTML = '';

  if (!contacts || contacts.length === 0) {
    list.innerHTML = '<div class="contact-item"><span class="contact-name">No contacts found</span></div>';
    return;
  }

  contacts.forEach((rawContact) => {
    const contact = normalizeContact(rawContact);

    const row = document.createElement('div');
    row.className = 'contact-item';

    const name = document.createElement('span');
    name.className = 'contact-name';
    name.textContent = `${contact.firstName} ${contact.lastName}`.trim();

    const arrowLink = document.createElement('a');
    arrowLink.className = 'arrow-btn';
    arrowLink.textContent = '>';
    arrowLink.href = `contactPage.html?userId=${userId}&id=${contact.id}&contactId=${contact.id}`;
    arrowLink.setAttribute('aria-label', `View details for ${contact.firstName} ${contact.lastName}`);

    arrowLink.addEventListener('click', () => {
      localStorage.setItem('selectedContactId', String(contact.id));
      localStorage.setItem('contactId', String(contact.id));
    });

    row.appendChild(name);
    row.appendChild(arrowLink);

    list.appendChild(row);
  });
}

// Fetches all contacts using viewContacts.php
async function loadContacts() {
  const userId = getUserId();
  if (!userId) return;

  const url = `${apiBase}/viewContacts.php?userId=${userId}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Unable to load contacts.');

    const data = await response.json();
    if (data.error && data.error !== '') throw new Error(data.error);

    const rawList = Array.isArray(data) ? data : (data.results || data.contacts || []);
    allContacts = rawList.map(normalizeContact);
    renderContacts(allContacts);

    // Update banner links and profile name once contacts are loaded
    updateProfileArrowTarget();
  } catch (error) {
    const list = document.getElementById('contactList');
    if (list) {
      list.innerHTML = '<div class="contact-item"><span class="contact-name">Unable to load contacts</span></div>';
    }
  }
}

// Queries searchContact.php with partial matching
async function searchServerContacts(searchTerm) {
  const userId = getUserId();
  if (!userId) return;

  try {
    const response = await fetch(`${apiBase}/searchContact.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ search: searchTerm, userId: userId })
    });

    const data = await response.json();

    if (data.error === "No Records Found" || !data.results) {
      renderContacts([]);
      return;
    }

    if (data.error && data.error !== "") {
      renderContacts([]);
      return;
    }

    renderContacts(data.results.map(normalizeContact));
  } catch (error) {
    renderContacts([]);
  }
}

function setupNavigation() {
  const userId = getUserId();
  if (!userId) return;

  const addBtn = document.getElementById('addContactBtn');
  if (addBtn) {
    addBtn.href = `editPage.html?userId=${userId}`;
  }
}

function setupSearch() {
  const searchInput = document.getElementById('searchInput');
  if (!searchInput) return;

  searchInput.addEventListener('input', (event) => {
    const query = event.target.value.trim();
    if (query === '') {
      renderContacts(allContacts);
    } else {
      searchServerContacts(query);
    }
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  setupNavigation();
  setupProfileBanner();
  updateProfileArrowTarget();
  setupSearch();
  await loadContacts();
});