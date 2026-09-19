const deleteButton = document.getElementById('deleteButton');
const deleteModal = document.getElementById('deleteModal');
const cancelDelete = document.getElementById('cancelDelete');
const confirmDelete = document.getElementById('confirmDelete');
const saveButton = document.getElementById('saveButton');
const validationMessage = document.getElementById('validationMessage');
const requiredFields = [
  document.getElementById('firstNameInput'),
  document.getElementById('lastNameInput'),
  document.getElementById('numberInput'),
  document.getElementById('emailInput')
];

function showValidationMessage() {
  if (!validationMessage) return;
  validationMessage.textContent = 'Required fields are missing. *';
  validationMessage.classList.add('visible');

  requiredFields.forEach((field) => {
    if (!field) return;
    field.classList.toggle('input-invalid', field.value.trim() === '');
  });
}

function clearValidationMessage() {
  if (!validationMessage) return;
  validationMessage.textContent = '';
  validationMessage.classList.remove('visible');

  requiredFields.forEach((field) => {
    if (!field) return;
    field.classList.remove('input-invalid');
  });
}

function hasRequiredValues() {
  return requiredFields.every((field) => field && field.value.trim() !== '');
}

function updateFieldValidity() {
  requiredFields.forEach((field) => {
    if (!field) return;
    const isEmpty = field.value.trim() === '';
    field.classList.toggle('input-invalid', isEmpty);
  });
}

if (deleteButton && deleteModal && cancelDelete && confirmDelete) {
  deleteButton.addEventListener('click', () => {
    deleteModal.classList.remove('hidden');
  });

  cancelDelete.addEventListener('click', () => {
    deleteModal.classList.add('hidden');
  });

  confirmDelete.addEventListener('click', () => {
    deleteModal.classList.add('hidden');
    window.location.href = 'homePage.html';
  });
}

if (saveButton) {
  saveButton.addEventListener('click', () => {
    if (!hasRequiredValues()) {
      showValidationMessage();
      updateFieldValidity();
      return;
    }

    clearValidationMessage();
    window.location.href = 'contactPage.html';
  });
}

requiredFields.forEach((field) => {
  if (!field) return;

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