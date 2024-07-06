const form = document.querySelector('form');
const fullName = document.querySelector('name');
const email = document.querySelector('email');
const message = document.querySelector('message');

function sendEmail() {
    const bodyMessage = `Full Name: ${fullName.value}<br>) Email:${email.value}  Message:${message.value} `;

    Email.send({
        Host: "smtp.elasticemail.com",
        Username: "skvanter@gmail.com",
        Password: "E4A84136D0F50B9D128B323F3654F9512686",
        To: 'skvanter@gmail.com',
        From: "skvanter@gmail.com",
        Subject: subject.value,
        Body: bodyMessage
    }).then(
        message => alert(message)
    );
}

form.addEventListener("submit", (e) => {
    e.preventDefault();
    sendEmail();
});