async function createAccount() {
    const fullName = document.getElementById("name-box").value.trim();

    const nameParts = fullName.split(" ");
    const firstName = nameParts[0];
    const lastName = nameParts.slice(1).join(" ");

    const phoneNum = document.getElementById("phoneNum-box").value.trim();
    const email = document.getElementById("signup-email-box").value.trim();
    const password = document.getElementById("signup-password-box").value;

    const response = await fetch("http://cop4431-jonathonf.online/backend/api/createAccount.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            firstName: firstName,
            lastName: lastName,
            login: email,
            password: password,
            phone: phoneNum
        })
    });

    const data = await response.json();

    const alert = document.getElementById("signup-alert");
    if(data.error === ""){
        alert.style.display = "block";
        alert.textContent = "Account created! Redirecting to login screen."
        alert.style.color = "lightgreen";

        setTimeout(function() {
            window.location.href = "loginPage.html";
        }, 3000);

    }
    else{
        alert.style.display = "block";

        const textInputs = document.querySelectorAll('input');
        textInputs.forEach(textInput => {
            if (textInput.value.trim() === ''){
                textInput.style.borderColor = 'red';
                textInput.style.backgroundColor = '#fff0f0';
            } else {
                textInput.style.borderColor = '#d9d9d9';
                textInput.style.backgroundColor = '#d9d9d9';
            }
        });


    }


}
