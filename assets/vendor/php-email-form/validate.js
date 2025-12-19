/**
* PHP Email Form Validation - v3.9
* URL: https://bootstrapmade.com/php-email-form/
* Author: BootstrapMade.com
*/
(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach( function(e) {
    e.addEventListener('submit', function(event) {
      event.preventDefault();

      let thisForm = this;

      let action = thisForm.getAttribute('action');
      let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');
      
      if( ! action ) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }
      thisForm.querySelector('.loading').classList.add('d-block');
      thisForm.querySelector('.error-message').classList.remove('d-block');
      thisForm.querySelector('.sent-message').classList.remove('d-block');

      let formData = new FormData( thisForm );

      if ( recaptcha ) {
        if(typeof grecaptcha !== "undefined" ) {
          grecaptcha.ready(function() {
            try {
              grecaptcha.execute(recaptcha, {action: 'php_email_form_submit'})
              .then(token => {
                formData.set('recaptcha-response', token);
                php_email_form_submit(thisForm, action, formData);
              })
            } catch(error) {
              displayError(thisForm, error);
            }
          });
        } else {
          displayError(thisForm, 'The reCaptcha javascript API url is not loaded!')
        }
      } else {
        php_email_form_submit(thisForm, action, formData);
      }
    });
  });

  function php_email_form_submit(thisForm, action, formData) {
    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
      return response.text().then(text => {
        if( response.ok ) {
          return text;
        } else {
          // Retourner le message d'erreur du serveur si disponible
          throw new Error(text || `${response.status} ${response.statusText}`); 
        }
      });
    })
    .then(data => {
      thisForm.querySelector('.loading').classList.remove('d-block');
      if (data.trim() == 'OK') {
        thisForm.querySelector('.sent-message').classList.add('d-block');
        thisForm.reset(); 
      } else {
        throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action); 
      }
    })
    .catch((error) => {
      displayError(thisForm, error);
    });
  }

  function displayError(thisForm, error) {
    thisForm.querySelector('.loading').classList.remove('d-block');
    let errorText = error;
    
    // Améliorer les messages d'erreur
    if (error instanceof Error) {
      errorText = error.message;
    }
    
    // Messages d'erreur plus conviviaux
    if (errorText.includes('Failed to fetch') || errorText.includes('NetworkError')) {
      errorText = 'Network error. Please check your internet connection and try again. If the problem persists, the server may be temporarily unavailable.';
    } else if (errorText.includes('404')) {
      errorText = 'Contact form script not found. Please contact the website administrator.';
    } else if (errorText.includes('400') || errorText.includes('Bad Request')) {
      // Garder le message d'erreur du serveur pour les erreurs de validation
      if (!errorText.includes('Name is required') && !errorText.includes('Email is required') && 
          !errorText.includes('Subject is required') && !errorText.includes('Message is required')) {
        errorText = 'Please fill in all required fields correctly. ' + errorText;
      }
    } else if (errorText.includes('500')) {
      errorText = 'Server error. Please try again later or contact directly at kella.douzoune@gmail.com';
    }
    
    thisForm.querySelector('.error-message').innerHTML = errorText;
    thisForm.querySelector('.error-message').classList.add('d-block');
  }

})();
