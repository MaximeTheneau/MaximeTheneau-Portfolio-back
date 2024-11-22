import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button'];

    connect() {
        console.log('ChatGPT Stimulus Controller connected');
    }

    handleChatGptFill(event) {
        event.preventDefault();

        const button = event.currentTarget;
        const subtitle = button.dataset.subtitle;
        const paragraphId = button.dataset.id;
        const id =  button.dataset.id;
        console.log(` Subtitle: ${subtitle}, Paragraph ID: ${paragraphId}`);

        const loader = document.getElementById(id + '_loader');
        loader.style.display = 'block';

        button.disabled = true;
        
        fetch('/api/posts/gpt-generate', {
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
            const modal = document.getElementById('gptModal');
            const gptMessage = document.getElementById('gptMessage');
            const acceptBtn = document.getElementById('acceptBtn');
            const cancelBtn = document.getElementById('cancelBtn');            
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.message;
            gptMessage.textContent = tempDiv.textContent;
            modal.classList.remove('hidden');
            
            acceptBtn.onclick = () => {
                const textarea = document.querySelector(`#${paragraphId} .ck-editor__editable_inline`);
                const domEditableElement = document.querySelector( '.ck-editor__editable_inline '   );
                console.log(button.dataset.paragraphId)
                const editorInstance = textarea.ckeditorInstance;
                editorInstance.setData(data.message);

                fetch('/posts/gpt/save-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'paragraph-id': button.dataset.paragraphId,
                        'paragraph': data.message
                    }),
                });
                
                modal.classList.add('hidden'); 
            };

            cancelBtn.onclick = () => {
                modal.classList.add('hidden'); 
            };
        })
        .catch(error => {
            console.error('Erreur lors de la récupération de la réponse de GPT:', error);
        })
        .finally(() => {
            loader.style.display = 'none';
            button.disabled = false; 
        });
    }

    async handleChatGptPosts(event) {
        event.preventDefault();

        const form = document.querySelector('form');

        const formData = new FormData(form);

        // try {
        //     const response = await fetch('/posts/gpt/save-data/posts', {
        //         method: 'POST',
        //         body: formData
        //     });

        //     if (response.ok) {
        //         const result = await response.json();
        //         console.log('Données enregistrées avec succès:', result);
        //     } else {
        //         console.error('Erreur lors de l\'enregistrement:', response.statusText);
        //     }
        //     } catch (error) {
        //     console.error('Erreur réseau:', error);
        //     }
        const button = event.currentTarget;
        const subtitle = document.getElementById('posts_heading');

        const id =  button.dataset.id;

        const loader = document.getElementById('posts_loader');
        loader.style.display = 'block';

        button.disabled = true;
        
        fetch('/posts/gpt/gpt-generate', {
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
            const modal = document.getElementById('gptModal');
            const gptMessage = document.getElementById('gptMessage');
            const acceptBtn = document.getElementById('acceptBtn');
            const cancelBtn = document.getElementById('cancelBtn');            
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.message;
            gptMessage.textContent = tempDiv.textContent;
            modal.classList.remove('hidden');
            
            acceptBtn.onclick = () => {
                // const textarea = document.querySelector(`#${paragraphId} .ck-editor__editable_inline`);
                const domEditableElement = document.querySelector( '.ck-editor__editable_inline '   );
                // console.log(button.dataset.paragraphId)
                // const editorInstance = textarea.ckeditorInstance;
                // editorInstance.setData(data.message);

                fetch('/posts/gpt/save-data/posts', {
                    method: 'POST',
                    headers: {
                        'enctype': 'multipart/form-data',
                    },
                    body: formData,
                });
                
                modal.classList.add('hidden'); 
            };

            cancelBtn.onclick = () => {
                modal.classList.add('hidden'); 
            };
        })
        .catch(error => {
            console.error('Erreur lors de la récupération de la réponse de GPT:', error);
        })
        .finally(() => {
            loader.style.display = 'none';
            button.disabled = false; 
        });
    }
}
