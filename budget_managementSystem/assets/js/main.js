/*=============== SHOW HIDDEN - PASSWORD ===============*/
document.addEventListener('DOMContentLoaded', function () {
   const showHiddenPass = (loginPass, loginEye) => {
      const input = document.getElementById(loginPass);
      const iconEye = document.getElementById(loginEye);
  
      // Add click event to the eye icon
      iconEye.addEventListener('click', () => {
          if (input.type === 'password') {
              input.type = 'text'; // Show password as text
              iconEye.classList.add('ri-eye-line'); // Change to eye-open icon
              iconEye.classList.remove('ri-eye-off-line');
          } else {
              input.type = 'password'; // Hide password
              iconEye.classList.remove('ri-eye-line'); // Change to eye-closed icon
              iconEye.classList.add('ri-eye-off-line');
          }
      });
  };
  

   // Initialize the show password functionality
   showHiddenPass('login-pass', 'login-eye');
});
$("#loginForm").on("submit", function(e) {
   e.preventDefault();
   Swal.fire({
       position: "center",
       icon: "success",
       title: "Login Complete",
       text: "OTP will be sent to your email. Please wait...",
       showConfirmButton: false,
       timer: 1500
   }).then(() => {
       Swal.fire({
           title: 'Sending OTP...',
           allowOutsideClick: false,
           showConfirmButton: false,
           didOpen: () => {
               Swal.showLoading();
           }
       });
   });
});