
function signIn() {
  var provider = new firebase.auth.GoogleAuthProvider();
  firebase.auth().signInWithPopup(provider);
}


function signOut() {
  firebase.auth().signOut();
}

function initFirebaseAuth() {
  firebase.auth().onAuthStateChanged(authStateObserver);
}

function getProfilePicUrl() {
  return firebase.auth().currentUser.photoURL || '/images/profile_placeholder.png';

}

function getUserName() {
  return firebase.auth().currentUser.displayName;
}

function isUserSignedIn() {
  return !!firebase.auth().currentUser;
}



function authStateObserver(user) {
  if (user) {
    var userName = getUserName();


    userNameElement.textContent = userName;

    userNameElement.removeAttribute('hidden');
    signOutButtonElement.removeAttribute('hidden');

    signInButtonElement.setAttribute('hidden', 'true');


  } else {
    userNameElement.setAttribute('hidden', 'true');
    signOutButtonElement.setAttribute('hidden', 'true');

    signInButtonElement.removeAttribute('hidden');
  }
}

function checkSignedInWithMessage() {
  if (isUserSignedIn()) {
    return true;
  }

  var data = {
    message: 'You must sign-in first',
    timeout: 2000
  };
  signInSnackbarElement.MaterialSnackbar.showSnackbar(data);
  return false;
}



function checkSetup() {
  if (!window.firebase || !(firebase.app instanceof Function) || !firebase.app().options) {
    window.alert('You have not configured and imported the Firebase SDK. ' +
      'Make sure you go through the codelab setup instructions and make ' +
      'sure you are running the codelab using `firebase serve`');
  }
}

checkSetup();

var userNameElement = document.getElementById('user-name');
var signInButtonElement = document.getElementById('sign-in');
var signOutButtonElement = document.getElementById('sign-out');
var signInSnackbarElement = document.getElementById('must-signin-snackbar');
signOutButtonElement.addEventListener('click', signOut);
signInButtonElement.addEventListener('click', signIn);

initFirebaseAuth();