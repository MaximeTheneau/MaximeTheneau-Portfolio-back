
   
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
            plugins: ['code', 'lists', 'link', 'table', 'image', 'codesample'],
            toolbar: 'bold italic underline | numlist bullist | image | code | codesample',
            allow_unsafe_link_target: true,
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


attachChatGptButtons();

function setupAddParagraphButton() {
    const addParagraphButton = document.querySelector('.button_paragraph');
    if (addParagraphButton) {
        addParagraphButton.addEventListener('click', function() {
            const collectionHolder = document.querySelector('.paragraph');
            if (!collectionHolder) return; // Si collectionHolder n'existe pas, on sort de la fonction

            // On initialise ou récupère l'index actuel
            let index = parseInt(collectionHolder.dataset.index) || 0;

            // On récupère le prototype de formulaire
            let newParagraph = collectionHolder.dataset.prototype.replace(/__name__/g, index);

            // On crée un nouvel élément <li> et on y ajoute le paragraphe
            let newParagraphLi = document.createElement('li');
            newParagraphLi.classList.add('h-auto', 'mb-8', 'border', 'border-gray-200');
            newParagraphLi.innerHTML = newParagraph;

            // On ajoute le nouveau paragraphe au conteneur
            collectionHolder.appendChild(newParagraphLi);

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
