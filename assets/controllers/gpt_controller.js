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

        const loader = document.getElementById('loader');
        loader.classList.remove('hidden');


        button.disabled = true;
        
        fetch('/posts/gpt/gpt-generate-paragraph', {
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
            loader.classList.add('hidden');

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
            button.disabled = false; 
        });
    }

    async handleChatGptPosts(event) {
        event.preventDefault();

        
        const button = event.currentTarget;
        const subtitle = document.getElementById('posts_heading');

        const loader = document.getElementById('loader');
        loader.classList.remove('hidden');

        button.disabled = true;
        
        fetch('/posts/gpt/gpt-generate-posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                subtitle: subtitle.value,
            }),
        })
        .then(response => response.json()) 
        .then(data => {
            loader.classList.add('hidden');
            if (data.message === true) {
            loader.style.display = 'none';
                window.location.href =  window.location.href
            }
            // const modal = document.getElementById('gptModal');
            // const gptMessage = document.getElementById('gptMessage');
            // const acceptBtn = document.getElementById('acceptBtn');
            // const cancelBtn = document.getElementById('cancelBtn');            
            
            // const tempDiv = document.createElement('div');
            // tempDiv.innerHTML = data.message;
            // gptMessage.textContent = tempDiv.textContent;
            // modal.classList.remove('hidden');
            
            // acceptBtn.onclick = () => {
            //     const textarea = document.querySelector(`#posts_contents .ck-editor__editable_inline`);
            //     const domEditableElement = document.querySelector( '.ck-editor__editable_inline '   );
            //     const editorInstance = textarea.ckeditorInstance;
            //     editorInstance.setData(data.message);

            //     fetch('/posts/gpt/save-data/posts', {
            //         method: 'POST',
            //         headers: {
            //             'enctype': 'multipart/form-data',
            //         },
            //         body: formData,
            //     });
                
            //     modal.classList.add('hidden'); 
            // };

            // cancelBtn.onclick = () => {
            //     modal.classList.add('hidden'); 
            // };
        })
        .catch(error => {
            console.error('Erreur lors de la récupération de la réponse de GPT:', error);
        })
        .finally(() => {
            button.disabled = false; 
        });
    }
}
