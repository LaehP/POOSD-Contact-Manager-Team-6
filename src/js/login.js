async function loginValidation (){
    //pulls required data from input boxes on login page
    const userBox = document.getElementById("login-email-box");
    const passwordBox = document.getElementById("login-password-box");

    const email = userBox.value.trim();
    const password = passwordBox.value;

    //POST request sends user credentials to database for verification, if successful, logs user in and redirects to home page
    const response = await fetch("http://cop4431-jonathonf.online/backend/api/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            login: email,
            password: password
        })
    });

    const data = await response.json(); //waiting for process to complete

    //if verification successful, logs user in. If not, displays error message
    if(data.error === ""){
        window.location.href = "homePage.html";
    }
    else{
        //data error
        userBox.style.borderColor = "red";
        userBox.style.backgroundColor = "#fff0f0";

        passwordBox.style.borderColor = "red";
        passwordBox.style.backgroundColor = "#fff0f0";

        const alert = document.getElementById("incorrect-login-alert");
        alert.style.display = "block";
    }

}
