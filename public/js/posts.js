
   
    const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.withCredentials = false; // Mettre true si nécessaire pour les cookies ou les sessions
        xhr.open('POST', '/uploadImg'); // URL de votre contrôleur Symfony
      
        // Mise à jour de la progression du téléchargement
        xhr.upload.onprogress = (e) => {
          progress(e.loaded / e.total * 100);
        };
      
        // Gestion de la réponse du serveur
        xhr.onload = () => {
          if (xhr.status === 403) {
            reject({ message: 'Erreur HTTP : ' + xhr.status, remove: true });
            return;
          }
      
          if (xhr.status < 200 || xhr.status >= 300) {
            reject('Erreur HTTP : ' + xhr.status);
            return;
          }
      
          const json = JSON.parse(xhr.responseText);
      
          // Vérification de la réponse JSON
          if (!json || typeof json.location !== 'string') {
            reject('JSON invalide : ' + xhr.responseText);
            return;
          }
      
          // Résoudre avec l'URL de l'image
          resolve(json.location);
        };
      
        // Gestion des erreurs
        xhr.onerror = () => {
          reject('Le téléchargement de l\'image a échoué en raison d\'une erreur de transport XHR. Code : ' + xhr.status);
        };
      
        // Création du FormData pour le fichier image
        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
      
        // Envoi des données
        xhr.send(formData);
      });
      function initializeTinyMCE() {

        tinymce.init({
            selector: 'textarea',
            plugins: ['lists', 'link', 'table', 'image'],
            toolbar: 'bold italic underline | numlist bullist | image',
            menubar: false,
            branding: false,
            images_upload_url: '/uploadImg',
            automatic_uploads: true,
            images_upload_handler: example_image_upload_handler,
            setup: (editor) => {
                editor.on('SetContent', (e) => {
                    const images = editor.getDoc().querySelectorAll('img');
                    images.forEach((img) => {
                        img.setAttribute('loading', 'lazy');
                    });
                });
            },
        });
    }

    initializeTinyMCE();

    const paragraphContainer = document.querySelector('.paragraph');
    const observer = new MutationObserver(() => {
        tinymce.remove();
        initializeTinyMCE();
    });

    observer.observe(paragraphContainer, { childList: true });


document.addEventListener('DOMContentLoaded', function () {
    initializeTinyMCE();
    setupEventListeners();
    attachChatGptButtons();
});

function setupEventListeners() {
    toggleDivWithButton('.button__altImg', '.add__altImg');
    toggleDivWithButton('.button__link', '.add__link');
    setupTextareaListener();
    setupAddParagraphButton();
    setupAddListButton();

}

function toggleDivWithButton(buttonId, divId) {
    const button = document.querySelector(buttonId);
    const div = document.querySelector(divId);

    if (button && div) {
        button.addEventListener('click', () => {
            div.classList.toggle('hidden');
        });
    } else {
        console.error('Élément non trouvé :', buttonId, divId);
    }
  }

  function setupTextareaListener() {
    const textareaContents = document.querySelector('#posts_contents');
    if (textareaContents) {
        textareaContents.addEventListener('input', (e) => {
            const length = e.target.value.length;
            const textarea = e.target;

            textarea.style.background = length < 135 ? '#ff5e5e2e' : '#f5f5f5';
            console.log(length);
        });
    }
}
attachChatGptButtons();

function setupAddParagraphButton() {
    const addParagraphButton = document.querySelector('.button_paragraph');
    attachChatGptButtons();

    if (addParagraphButton) {
        addParagraphButton.addEventListener('click', function() {
            let collectionHolder = document.querySelector('.paragraph');
            if (!collectionHolder) return; // If collectionHolder doesn't exist, exit function

            let index = collectionHolder.dataset.index;
            let newParagraph = collectionHolder.dataset.prototype.replace(/__name__/g, index);
            let newParagraphLi = document.createElement('li');
            newParagraphLi.classList.add('h-auto', 'mb-8', 'border', 'border-gray-200');
            newParagraphLi.innerHTML = newParagraph;

            collectionHolder.appendChild(newParagraphLi);
            collectionHolder.dataset.index = parseInt(index) + 1;

        });
    }
}

function setupAddListButton() {
    const addListButton = document.querySelector('.button__list');
    if (addListButton) {
        addListButton.addEventListener('click', function() {
            let collectionHolder = document.querySelector('.list');
            if (!collectionHolder) return; // If collectionHolder doesn't exist, exit function

            let index = collectionHolder.dataset.index;
            let newList = collectionHolder.dataset.prototype.replace(/__name__/g, index);
            let newListLi = document.createElement('li');
            newListLi.innerHTML = newList;

            collectionHolder.appendChild(newListLi);
            collectionHolder.dataset.index = parseInt(index) + 1;
        });
    }
}


function setupTextareaObserver() {
    const container = document.querySelector('.paragraph'); // Ou un autre conteneur contenant vos textareas

    const observer = new MutationObserver(() => {
        // Chaque fois qu'un nouveau textarea est ajouté, on initialise TinyMCE dessus
        const newTextarea = container.querySelector('textarea');
        if (newTextarea && !tinymce.get(newTextarea.id)) {
            tinymce.init({
                selector: `textarea#${newTextarea.id}`,
                plugins: ['lists', 'link', 'table', 'image'],
                toolbar: 'bold italic underline | numlist bullist | image',
                menubar: false,
                branding: false,
                images_upload_url: '/uploadImg',
                automatic_uploads: true,
                images_upload_handler: example_image_upload_handler,
            });
        }
    });

    observer.observe(container, { childList: true });
}

setupTextareaObserver();

document.querySelector('form').addEventListener('submit', function(event) {
    const textarea = document.querySelector('textarea#posts_contents');

    if (textarea && textarea.style.display === 'none') {
        textarea.style.display = 'block';
    }

    const editor = tinymce.get(textarea.id);
    if (editor) {
        textarea.value = editor.getContent();
    }
});

function attachChatGptButtons() {
    const buttons = document.querySelectorAll('.button__chatGpt');

    buttons.forEach(button => {
        button.removeEventListener('click', handleChatGptClick); 
        button.addEventListener('click', handleChatGptClick);
    });
}

function handleChatGptClick(event) {
    const subtitle = this.getAttribute('data-subtitle');
    const id = this.getAttribute('data-id');

    submitChatGptForm(event,  subtitle, id);
}
function submitChatGptForm(event, subtitle, id) {
    event.preventDefault();

    const loader = document.getElementById(id + '_loader');
    loader.style.display = 'block';

    const button = document.getElementById(id + '_chatGptButton');
    button.disabled = true;


    
    fetch('/generate-content', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            subtitle: subtitle,
        }),
    })
    .then(response => response.json()) 
    .then(data => {
        if (data.content === 'Plus de points') {
            const solde = document.getElementById(id + '_solde');
            solde.style.display = 'block';
        }
        if (data.content) {
            const formattedContent = data.content.replace(/\\n/g, '\n'); 
            const editorId = id + '_paragraph';
            const editor = tinymce.get(editorId);

            editor.setContent(formattedContent);
            document.getElementById(id + '_paragraph').value = 'test';
            console.log(document.getElementById(id + '_paragraph').value);
        } else {
            console.error('No content returned');
        }
    })
    .catch(error => {
        console.error('Erreur lors de la récupération de la réponse de GPT:', error);
    })
    .finally(() => {
        loader.style.display = 'none';
        button.disabled = false; 
    });
}



// // Add list 
// const addTagLink = document.querySelector('');
// const collectionHolder = document.querySelector('.');
// const prototype = collectionHolder.dataset.prototype;
// let index = collectionHolder.dataset.index;

// addTagLink.addEventListener('click', function(e) {
// e.preventDefault();
// const button = document.querySelector('.tags');
// addTagLink.textContent = 'Ajouter un element à la liste';
// console.log(addTagLink.textContent);
// const ul = document.querySelector('.tags');
// ul.classList.remove('none');
// const newForm = prototype.replace(/__name__/g, index);
// index++;
// const newLi = document.createElement('li');
// newLi.innerHTML = newForm;
// collectionHolder.appendChild(newLi);
// });