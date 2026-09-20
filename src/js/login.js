async function loginValidation (){
    const userBox = document.getElementById("login-email-box");
    const passwordBox = document.getElementById("login-password-box");

    const email = userBox.value.trim();
    const password = passwordBox.value;

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

    const data = await response.json();

    if(data.error === ""){
        window.location.href = "homePage.html";
    }
    else{
        //data error
        userBox.style.borderColor = "red";
        userBox.style.backgroundColor = "pink";

        passwordBox.style.borderColor = "red";
        passwordBox.style.backgroundColor = "pink";

        const alert = document.getElementById("incorrect-login-alert");
        alert.style.display = "block";
    }


}
