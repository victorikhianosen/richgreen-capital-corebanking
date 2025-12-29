const nextbtn = document.querySelectorAll(".next-step");
const prevbtn = document.querySelectorAll(".prev-step");
const formsteps = document.querySelectorAll(".form-step");
const interntpg = document.getElementById("intercont");
const internttxt = document.getElementById("intemsg");

let formstepsnum = 0;
nextbtn.forEach((nxtbtn) => {
   nxtbtn.addEventListener('click',() => {
       formstepsnum++;
       updatformsteps();
   });
});
prevbtn.forEach((prvbtn) => {
   prvbtn.addEventListener('click',() => {
       formstepsnum--;
       updatformsteps();
   });
});

 function firstpg() {
 formsteps.forEach((formstep) => {
   formstep.classList.contains('form-active') && formstep.classList.remove('form-active');
});
 formsteps[0].classList.add('form-active');
}

 function secondpg() {
 formsteps.forEach((formstep) => {
   formstep.classList.contains('form-active') && formstep.classList.remove('form-active');
});
 formsteps[1].classList.add('form-active');
}


function updatformsteps(){
   formsteps.forEach((formstep) => {
       formstep.classList.contains('form-active') && formstep.classList.remove('form-active');
   });
   formsteps[formstepsnum].classList.add('form-active');
}


function myFunction() {
   // Declare variables
   var input, filter, ul, li, a, i;
   input = document.getElementById("mySearch");
   filter = input.value.toUpperCase();
   ul = document.getElementById("menu");
   li = ul.getElementsByTagName("li");
 
 // Loop through all list items, and hide those who don't match the search query
 for (i = 0; i < li.length; i++) {
     a = li[i].getElementsByTagName("a")[0];
     if (a.innerHTML.indexOf(filter) > -1) {
       li[i].style.display = "";
     } else {
       li[i].style.display = "none";
     }
   }
 }
 
 function updateConnectionStatus() {  
  if(navigator.onLine) {
    interntpg.style.visibility = 'hidden';                      
  } else {
    interntpg.style.visibility = 'visible';  
    internttxt.textContent="Not Connected. Please Check your Network";        
  }
}
// Attaching event handler for the load event
window.addEventListener("load", updateConnectionStatus);

// Attaching event handler for the online event
window.addEventListener("online", function(e) {
  updateConnectionStatus();
});

// Attaching event handler for the offline event
window.addEventListener("offline", function(e) {        
  updateConnectionStatus();
});