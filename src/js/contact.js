const apiBase = 'http://cop4431-jonathonf.online/backend/api';

function getQueryValue(name) {
  return new URLSearchParams(window.location.search).get(name);
}

function getUserId() {
  const storedUserId = localStorage.getItem('userId');
  const userId = getQueryValue('userId') || storedUserId;

  // Redirect to the login page if user is not logged in
  if (userId === null || userId === undefined || userId === '') {
    localStorage.removeItem('userId');

    if (!window.location.pathname.endsWith('loginPage.html')) {
      window.location.href = 'loginPage.html';
    }

    return null;
  }

  localStorage.setItem('userId', userId);
  return Number(userId);
}

// Load contact details when the page is loaded
async function loadContact() {
  const userId = getUserId();
  if (userId === null || userId === undefined || Number.isNaN(userId)) {
    return;
  }

const contactId = getQueryValue('contactId') || getQueryValue('id');
const isUserProfile = !contactId || contactId === 'null' || contactId === 'undefined';

  let url = new URL(`${apiBase}/viewContact.php`);
  if (isUserProfile) {
    url = new URL(`${apiBase}/userContact.php`);
  } else {
    url.searchParams.set('id', String(contactId));
  }

  url.searchParams.set('userId', String(userId));

  // Fetch contact details from view contact API
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error('Unable to load contact.');
    }

    const data = await response.json();

    if (data.error && data.error !== '') {
      throw new Error(data.error);
    }

    const contact = Array.isArray(data) ? data[0] : data;
    if (!contact) {
      throw new Error('Contact not found.');
    }

    const fullName = `${contact.FirstName || ''} ${contact.LastName || ''}`.trim();
    const contactName = document.getElementById('contactName');
    if (contactName) contactName.textContent = fullName || 'Unnamed contact';

    const numberField = document.getElementById('numberInput');
    if (numberField) numberField.textContent = contact.PhoneNumber || contact.Phone || 'N/A';

    const emailField = document.getElementById('emailInput');
    if (emailField) emailField.textContent = contact.Email || contact.Login || 'N/A';

    const dateField = document.getElementById('dateCreated');
    const dateBlock = document.querySelector('.date-block');
    if (dateField) {
      if (isUserProfile) {
        dateField.textContent = '';
        if (dateBlock) dateBlock.style.display = 'none';
      } else {
        dateField.textContent = contact.date_added || 'N/A';
        if (dateBlock) dateBlock.style.display = '';
      }
    }

    if (contactId) {
      localStorage.setItem('selectedContactId', String(contactId));
    } else {
      localStorage.removeItem('selectedContactId');
    }

    // Set the edit link to include the userId and contactId in the query parameters
    // If the contactId is not available, only include the userId in the edit link
    const editLink = document.querySelector('.edit-button');
    if (editLink) {
      const editUrl = contactId ? `editPage.html?userId=${userId}&id=${contactId}` : `editPage.html?userId=${userId}`;
      editLink.href = editUrl;
    }

    // Set the back button to return to the homepage with the userId in the query parameters
    const backLink = document.getElementById('backButton');
    if (backLink) {
      backLink.href = `homePage.html?userId=${userId}`;
    }
  } catch (error) {
    const contactName = document.getElementById('contactName');
    if (contactName) {
      contactName.textContent = 'Unable to load contact';
      contactName.setAttribute('title', error.message || 'Something went wrong');
    }
  }
}

document.addEventListener('DOMContentLoaded', loadContact);
