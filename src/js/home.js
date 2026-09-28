//AI-assisted: Initial implementation suggested by Google Gemini and Cursor (reviewed and modified)
const apiBase = 'http://cop4431-jonathonf.online/backend/api';

let allContacts = [];

//Returns URL parameter
function getQueryValue(name) {
  return new URLSearchParams(window.location.search).get(name);
}

//Saves valid user ID; Otherwise, redirects to login page, if possible
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

// standardizes contact data for consistent formatting
function standardizeContact(contact) {
  return {
    id: contact.id ?? contact.ID,
    firstName: contact.firstName ?? contact.FirstName ?? '',
    lastName: contact.lastName ?? contact.LastName ?? '',
    phone: contact.phoneNumber ?? contact.Phone ?? ''
  };
}

//Returns user's name, if saved in a cookie
function readLoginCookie() {
  const name = 'user=';
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

//Searches elsewhere for and returns user's name, if found
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

  // Check JSON
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

//Updates the profile banner with the user's name
function setupProfileBanner() {
  const { firstName, lastName } = getStoredUserData();
  const displayName = `${firstName} ${lastName}`.trim();

  const userNameEl = document.getElementById('userNameDisplay');
  if (userNameEl && displayName) {
    userNameEl.textContent = displayName;
  }
}

//Updates the profile arrow link and banner with user's information
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

    const contact = standardizeContact(rawContact);

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
    } else {
      profileArrow.href = `contactPage.html?userId=${userId}`;
    }
  } catch (error) {
    userNameEl.textContent = 'Your Name';
  }
}

//Displays contacts names with arrow link
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
    const contact = standardizeContact(rawContact);

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
    allContacts = rawList.map(standardizeContact);
    renderContacts(allContacts);
    
    updateProfileArrowTarget();
  } catch (error) {
    const list = document.getElementById('contactList');
    if (list) {
      list.innerHTML = '<div class="contact-item"><span class="contact-name">Unable to load contacts</span></div>';
    }
  }
}

//searches for contacts using searchContact.php
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

    if (data.error === 'No Records Found' || !data.results) {
      renderContacts([]);
      return;
    }

    if (data.error && data.error !== '') {
      renderContacts([]);
      return;
    }

    renderContacts(data.results.map(standardizeContact));
  } catch (error) {
    renderContacts([]);
  }
}

//Links edit page to the add contact button
function setupNavigation() {
  const userId = getUserId();
  if (!userId) return;

  const addBtn = document.getElementById('addContactBtn');
  if (addBtn) {
    addBtn.href = `editPage.html?userId=${userId}`;
  }
}

//Displays all contacts if search bar is empty; Otherwise, displays partially matched results
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

//Sets up page functionality after HTML is loaded
document.addEventListener('DOMContentLoaded', async () => {
  setupNavigation();
  setupProfileBanner();
  updateProfileArrowTarget();
  setupSearch();
  await loadContacts();
});