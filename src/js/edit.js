const deleteButton = document.getElementById('deleteButton');
const deleteModal = document.getElementById('deleteModal');
const cancelDelete = document.getElementById('cancelDelete');
const confirmDelete = document.getElementById('confirmDelete');
const deleteTitle = document.getElementById('deleteTitle');

const saveButton = document.getElementById('saveButton');
const backButton = document.getElementById('backButton');

const validationMessage = document.getElementById('validationMessage');

const requiredFields = [
  document.getElementById('firstNameInput'),
  document.getElementById('lastNameInput'),
  document.getElementById('numberInput'),
  document.getElementById('emailInput')
];

const apiBase = 'http://cop4431-jonathonf.online/backend/api';

function getQueryValue(name) {
  return new URLSearchParams(window.location.search).get(name);
}

function setupBackButton() {
  if (!backButton) return;

  const userId = getUserId();
  const contactId = getQueryValue('id');

  // If editing existing contact, back button goes to contact page
  if (contactId) {
    backButton.href = `contactPage.html?userId=${userId}&id=${contactId}`;
    backButton.setAttribute('aria-label', 'Back to contact');
    if (deleteButton) deleteButton.classList.remove('hidden');
    return;
  }

  // If new contact, back button goes to home page
  backButton.href = `homePage.html?userId=${userId}`;
  backButton.setAttribute('aria-label', 'Back to home');
  if (deleteButton) deleteButton.classList.add('hidden');
}

// Get the userId from the query string or Local Storage
function getUserId() {
  const storedUserId = localStorage.getItem('userId');
  const userId = getQueryValue('userId') || storedUserId;

  // If the user is not logged in, redirect to login page
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

// Shows validation message and highlights missing fields
function showValidationMessage() {
  if (!validationMessage) return;
  validationMessage.textContent = 'Required fields are missing. *';
  validationMessage.classList.add('visible');

  requiredFields.forEach((field) => {
    if (!field) return;
    field.classList.toggle('input-invalid', field.value.trim() === '');
  });
}

// Clears validation message and removes highlight from fields
function clearValidationMessage() {
  if (!validationMessage) return;
  validationMessage.textContent = '';
  validationMessage.classList.remove('visible');

  requiredFields.forEach((field) => {
    if (!field) return;
    field.classList.remove('input-invalid');
  });
}

// Checks that all required fields have values
function hasRequiredValues() {
  return requiredFields.every((field) => field && field.value.trim() !== '');
}

// Checks if fields are empty or not
function updateFieldValidity() {
  requiredFields.forEach((field) => {
    if (!field) return;
    const isEmpty = field.value.trim() === '';
    field.classList.toggle('input-invalid', isEmpty);
  });
}

// Get the current date and format it to match date in database for date created field
function getTodayDateLabel() {
  const today = new Date();
  const month = String(today.getMonth() + 1).padStart(2, '0'); //Months start at 0 so add 1 for current month

  // Uses getDate() instead of getDay() to get number of the day in the month
  const day = String(today.getDate()).padStart(2, '0'); 
  const year = today.getFullYear();

  return `${year}-${month}-${day}`;
}

// Load the contact data into form for editing if contact exists 
function setFormValues(contact) {
  const firstName = document.getElementById('firstNameInput');
  const lastName = document.getElementById('lastNameInput');
  const phone = document.getElementById('numberInput');
  const email = document.getElementById('emailInput');
  const date = document.getElementById('dateCreated');

  if (firstName) firstName.value = contact.FirstName || '';
  if (lastName) lastName.value = contact.LastName || '';
  if (phone) phone.value = contact.Phone || '';
  if (email) email.value = contact.Email || '';
  if (date) date.textContent = contact.date_added ? contact.date_added : getTodayDateLabel();
}

// Add the current date to the date created field if it's empty
function initializeDateField() {
  const date = document.getElementById('dateCreated');
  if (!date) return;

  if (date.textContent === 'Loading...' || date.textContent.trim() === '') {
    date.textContent = getTodayDateLabel();
  }
}

// Load the contact data for editing if an id is present in the query string
async function loadContactForEdit() {
  const userId = getUserId();
  const contactId = getQueryValue('id');

  if (!contactId) {
    initializeDateField();
    return;
  }

  // Get the contact data from the backend API
  const url = `${apiBase}/viewContact.php?userId=${userId}&id=${contactId}`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error('Unable to load contact for edit.');
    }

    const payload = await response.json();

    if (payload.error && payload.error !== '') {
      throw new Error(payload.error);
    }

    // If the contact exists load the data into the form, otherwise initialize date field for new contact
    const contact = Array.isArray(payload) ? payload[0] : payload;
    if (contact) {
      setFormValues(contact);
    }
  } catch (error) {
    console.error(error);
    initializeDateField();
  }
}

function handleSave() {
  if (!hasRequiredValues()) {
    showValidationMessage();
    updateFieldValidity();
    return;
  }

  const userId = getUserId();
  const contactId = getQueryValue('id');
  const payload = contactId
    ? {
        // if editing an existing contact include contactId 
        firstName: document.getElementById('firstNameInput')?.value.trim() || '',
        lastName: document.getElementById('lastNameInput')?.value.trim() || '',
        phoneNumber: document.getElementById('numberInput')?.value.trim() || '',
        email: document.getElementById('emailInput')?.value.trim() || '',
        userId,
        id: Number(contactId)
      }
    : {
        // if adding a new contact don't include contactId
        firstName: document.getElementById('firstNameInput')?.value.trim() || '',
        lastName: document.getElementById('lastNameInput')?.value.trim() || '',
        phoneNumber: document.getElementById('numberInput')?.value.trim() || '',
        email: document.getElementById('emailInput')?.value.trim() || '',
        userId
      };

  // Determine if the contact is being added or edited and call the apropriate API
  const endpoint = `${apiBase}/${contactId ? 'editContact.php' : 'addContact.php'}`;

  // Save the contact data to the backend API
  const saveContact = async () => {
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const text = await response.text();
      let result = {};

      try {
        result = text ? JSON.parse(text) : {};
      } catch (error) {
        console.error('Non-JSON backend response:', text);
        throw new Error('The server returned an invalid response.');
      }

      if (!response.ok || result.error) {
        throw new Error(result.error || 'Unable to save contact.');
      }

      // Redirect to contact page with the new or updated contact data
      clearValidationMessage();
      const nextId = result.id || contactId;
      localStorage.setItem('selectedContactId', String(nextId));
      localStorage.setItem('lastSavedContactId', String(nextId));
      window.location.href = `contactPage.html?userId=${userId}&id=${nextId}`;
    } catch (error) {
      showValidationMessage();
      validationMessage.textContent = error.message || 'Unable to save contact.';
    }
  };

  saveContact();
}


if (deleteButton && deleteModal && cancelDelete && confirmDelete) {
  // Show the delete popup when delete button is clicked
  deleteButton.addEventListener('click', () => {
    if (deleteTitle) {
      deleteTitle.textContent = 'Are you sure you want to delete this contact?';
    }
    deleteModal.classList.remove('hidden');
  });

  // Hide the delete popup when cancel is clicked
  cancelDelete.addEventListener('click', () => {
    if (deleteTitle) {
      deleteTitle.textContent = 'Are you sure you want to delete this contact?';
    }
    deleteModal.classList.add('hidden');
  });

  // If confirm is clicked return the user to the homepage and delete contact
  confirmDelete.addEventListener('click', async () => {
    const userId = getUserId();
    const contactId = getQueryValue('id');

    if (!contactId) {
      deleteModal.classList.add('hidden');
      window.location.href = `homePage.html?userId=${userId}`;
      return;
    }

    try {
      // Call delete contact API
      const response = await fetch(`${apiBase}/deleteContact.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          userId,
          id: Number(contactId)
        })
      });

      const text = await response.text();
      let result = {};

      try {
        result = text ? JSON.parse(text) : {};
      } catch (error) {
        console.error('Non-JSON delete response:', text);
        throw new Error('The delete request returned an invalid response.');
      }

      if (!response.ok || (result.error && result.error !== '')) {
        throw new Error(result.error || 'Unable to delete contact.');
      }

      deleteModal.classList.add('hidden');
      window.location.href = `homePage.html?userId=${userId}`;
    } catch (error) {
      if (deleteTitle) {
        deleteTitle.textContent = error.message || 'Unable to delete contact.';
      }
      console.error('Delete contact failed:', error);
    }
  });
}

// Call handleSave when save button is clicked
if (saveButton) {
  saveButton.addEventListener('click', handleSave);
}

// Call setupBackButton to set the back button based on whether editing or adding a contact
setupBackButton();

requiredFields.forEach((field) => {
  if (!field) return;
  
  // Check for input changes and update the validation message and field highlighting
  field.addEventListener('input', () => {
    if (!validationMessage || !validationMessage.classList.contains('visible')) {
      return;
    }

    if (hasRequiredValues()) {
      clearValidationMessage();
      return;
    }

    requiredFields.forEach((item) => {
      if (!item) return;
      item.classList.toggle('input-invalid', item.value.trim() === '');
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  initializeDateField();
  loadContactForEdit();
});