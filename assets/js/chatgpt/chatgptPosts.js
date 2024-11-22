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

