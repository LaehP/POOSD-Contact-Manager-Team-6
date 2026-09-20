async function loginValidation (){
    const email = document.getElementById("login-email-box").value.trim();
    const password = document.getElementById("login-password-box").value;

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
        window.alert("Incorrect Username or Password");
    }


}
