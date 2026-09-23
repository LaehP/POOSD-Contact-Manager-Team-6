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
  const contactId = getQueryValue('id') || localStorage.getItem('selectedContactId') || localStorage.getItem('lastSavedContactId');

  // If contactId does not exist display message
  if (!contactId) {
    const contactName = document.getElementById('contactName');
    if (contactName) contactName.textContent = 'No contact selected';
    return;
  }

  const url = `${apiBase}/viewContact.php?userId=${userId}&id=${contactId}`;

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

    // Display contact details on page and show N/A for missing values
    const fullName = `${contact.FirstName || ''} ${contact.LastName || ''}`.trim();
    const contactName = document.getElementById('contactName');
    if (contactName) contactName.textContent = fullName || 'Unnamed contact';

    const numberField = document.getElementById('numberInput');
    if (numberField) numberField.textContent = contact.Phone || 'N/A';

    const emailField = document.getElementById('emailInput');
    if (emailField) emailField.textContent = contact.Email || 'N/A';

    const dateField = document.getElementById('dateCreated');
    if (dateField) dateField.textContent = contact.date_added || 'N/A';

    localStorage.setItem('selectedContactId', contactId);

    // Set the edit link to include the userId and contactId in the query parameters
    const editLink = document.querySelector('.edit-button');
    if (editLink) {
      editLink.href = `editPage.html?userId=${userId}&id=${contactId}`;
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
