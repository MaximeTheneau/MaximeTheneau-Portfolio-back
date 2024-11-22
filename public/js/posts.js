
   
    // const example_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
    //     const xhr = new XMLHttpRequest();
    //     xhr.withCredentials = false; // Mettre true si nécessaire pour les cookies ou les sessions
    //     xhr.open('POST', '/uploadImg'); // URL de votre contrôleur Symfony
      
    //     // Mise à jour de la progression du téléchargement
    //     xhr.upload.onprogress = (e) => {
    //       progress(e.loaded / e.total * 100);
    //     };
      
    //     // Gestion de la réponse du serveur
    //     xhr.onload = () => {
    //       if (xhr.status === 403) {
    //         reject({ message: 'Erreur HTTP : ' + xhr.status, remove: true });
    //         return;
    //       }
      
    //       if (xhr.status < 200 || xhr.status >= 300) {
    //         reject('Erreur HTTP : ' + xhr.status);
    //         return;
    //       }
      
    //       const json = JSON.parse(xhr.responseText);
      
    //       // Vérification de la réponse JSON
    //       if (!json || typeof json.location !== 'string') {
    //         reject('JSON invalide : ' + xhr.responseText);
    //         return;
    //       }
      
    //       // Résoudre avec l'URL de l'image
    //       resolve(json.location);
    //     };
      
    //     // Gestion des erreurs
    //     xhr.onerror = () => {
    //       reject('Le téléchargement de l\'image a échoué en raison d\'une erreur de transport XHR. Code : ' + xhr.status);
    //     };
      
    //     // Création du FormData pour le fichier image
    //     const formData = new FormData();
    //     formData.append('file', blobInfo.blob(), blobInfo.filename());
      
    //     // Envoi des données
    //     xhr.send(formData);
    //   });
    //   function initializeTinyMCE() {

    //     tinymce.init({
    //         selector: 'textarea',
    //         plugins: ['code', 'lists', 'link', 'table', 'image', 'codesample'],
    //         toolbar: 'bold italic underline | numlist bullist | image | code | codesample',
    //         allow_unsafe_link_target: true,
    //         menubar: false,
    //         branding: false,
    //         images_upload_url: '/uploadImg',
    //         automatic_uploads: true,
    //         images_upload_handler: example_image_upload_handler,
    //         setup: (editor) => {
    //             editor.on('SetContent', (e) => {
    //                 const images = editor.getDoc().querySelectorAll('img');
    //                 images.forEach((img) => {
    //                     img.setAttribute('loading', 'lazy');
    //                 });
    //             });
    //         },
    //     });
    // }

    // initializeTinyMCE();

    // const paragraphContainer = document.querySelector('.paragraph');
    // const observer = new MutationObserver(() => {
    //     tinymce.remove();
    //     initializeTinyMCE();

    // });
    // observer.observe(paragraphContainer, { childList: true });

document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();

});

function setupEventListeners() {
    toggleDivWithButton('.button__altImg', '.add__altImg');
    toggleDivWithButton('.button__link', '.add__link');
    // setupAddParagraphButton();
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




function handleChatGptClick(event) {
    const subtitle = this.getAttribute('data-subtitle');
    const id = this.getAttribute('data-id');

    submitChatGptForm(event,  subtitle, id);
}

