import {
	ClassicEditor,
	AccessibilityHelp,
	Autoformat,
	AutoImage,
	Autosave,
	BlockQuote,
	Bold,
	Essentials,
	Heading,
	ImageBlock,
	ImageCaption,
	ImageInline,
	ImageInsert,
	ImageInsertViaUrl,
	ImageResize,
	ImageStyle,
	ImageTextAlternative,
	ImageToolbar,
	ImageUpload,
	Indent,
	IndentBlock,
	Italic,
	Link,
	LinkImage,
	List,
	ListProperties,
	Paragraph,
	SelectAll,
	SimpleUploadAdapter,
	Table,
	TableCaption,
	TableCellProperties,
	TableColumnResize,
	TableProperties,
	TableToolbar,
	TextTransformation,
	TodoList,
	Underline,
	Undo,
	CodeBlock
} from './ckeditor5.js';

import translations from './translations/fr.js';

const postId = document.querySelector('#postId')?.value;

const editorConfig = {

	toolbar: {
		items: [
			'undo',
			'redo',
			'|',
			'heading',
			'|',
			'bold',
			'italic',
			'underline',
			'|',
			'link',
			'insertImage',
			'insertTable',
			'blockQuote',
			'|',
			'bulletedList',
			'numberedList',
			'todoList',
			'outdent',
			'indent',
			'CodeBlock'
		],
		shouldNotGroupWhenFull: false
	},
	plugins: [
		AccessibilityHelp,
		Autoformat,
		AutoImage,
		Autosave,
		BlockQuote,
		Bold,
		Essentials,
		Heading,
		ImageBlock,
		ImageCaption,
		ImageInline,
		ImageInsert,
		ImageInsertViaUrl,
		ImageResize,
		ImageStyle,
		ImageTextAlternative,
		ImageToolbar,
		ImageUpload,
		Indent,
		IndentBlock,
		Italic,
		Link,
		LinkImage,
		List,
		ListProperties,
		Paragraph,
		SelectAll,
		SimpleUploadAdapter,
		Table,
		TableCaption,
		TableCellProperties,
		TableColumnResize,
		TableProperties,
		TableToolbar,
		TextTransformation,
		TodoList,
		Underline,
		Undo,
		CodeBlock
	],
	heading: {
		options: [
			{
				model: 'paragraph',
				title: 'Paragraph',
				class: 'ck-heading_paragraph'
			},
			{
				model: 'heading1',
				view: 'h1',
				title: 'Heading 1',
				class: 'ck-heading_heading1'
			},
			{
				model: 'heading2',
				view: 'h2',
				title: 'Heading 2',
				class: 'ck-heading_heading2'
			},
			{
				model: 'heading3',
				view: 'h3',
				title: 'Heading 3',
				class: 'ck-heading_heading3'
			},
			{
				model: 'heading4',
				view: 'h4',
				title: 'Heading 4',
				class: 'ck-heading_heading4'
			},
			{
				model: 'heading5',
				view: 'h5',
				title: 'Heading 5',
				class: 'ck-heading_heading5'
			},
			{
				model: 'heading6',
				view: 'h6',
				title: 'Heading 6',
				class: 'ck-heading_heading6'
			}
		]
	},
	image: {
		toolbar: [
			'toggleImageCaption',
			'imageTextAlternative',
			'|',
			'imageStyle:inline',
			'imageStyle:wrapText',
			'imageStyle:breakText',
			'|',
			'resizeImage'
		]
	},
	link: {
		addTargetToExternalLinks: true,
		defaultProtocol: 'https://',
		decorators: {
			toggleDownloadable: {
				mode: 'manual',
				label: 'Downloadable',
				attributes: {
					download: 'file'
				}
			}
		}
	},
	placeholder: 'Entrez ici votre article',
	simpleUpload: {
		uploadUrl: `/uploadImg?id=${postId}`,
		withCredentials: true,
	},
	list: {
		properties: {
			styles: true,
			startIndex: true,
			reversed: true
		}
	},
	table: {
		contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
	},
		translations: [translations]

};


// ClassicEditor.create(document.querySelector('#posts_contents'), editorConfig);
document.addEventListener('DOMContentLoaded', () => {
    const initializeEditor = (textarea) => {
        if (textarea.dataset.editorInitialized === 'true') {
            return;
        }

        ClassicEditor.create(textarea, editorConfig)
            .then(() => {
                textarea.dataset.editorInitialized = 'true';
            })
            .catch(error => {
                console.error(`Erreur lors de l'initialisation de l'éditeur pour : ${textarea.id}`, error);
            });
    };

    document.querySelectorAll('textarea').forEach(textarea => {
        initializeEditor(textarea);
    });
});

setupAddParagraphButton()

function setupAddParagraphButton() {
    const addParagraphButton = document.querySelector('.button_paragraph');
    if (addParagraphButton) {
        addParagraphButton.addEventListener('click', function() {
            let collectionHolder = document.querySelector('.paragraph');
            if (!collectionHolder) return; // Si collectionHolder n'existe pas, on sort de la fonction
			
            const index = collectionHolder.dataset.index;
            let newParagraphHtml = collectionHolder.dataset.prototype.replace(/__name__/g, index);
            // On crée un nouvel élément <li> et on y ajoute le paragraphe
             let newParagraphLi = document.createElement('li');
            newParagraphLi.classList.add('h-auto', 'mb-8', 'border', 'border-gray-200');
            newParagraphLi.innerHTML = newParagraphHtml;
            newParagraphLi.setAttribute('id', `posts_paragraphPosts_${index}`); // Ajout de l'ID unique

            // On ajoute le nouveau paragraphe au conteneur
            collectionHolder.appendChild(newParagraphLi);
                
            collectionHolder.dataset.index = parseInt(index) + 1;

			let textarea = newParagraphLi.querySelector('textarea');
            ClassicEditor.create(textarea, editorConfig)
        });
    }
}

function setupRemoveParagraphButtons() {
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-paragraph-btn')) {
            const paragraphLi = event.target.closest('li'); // Trouve le <li> parent du bouton
            if (paragraphLi) {
                paragraphLi.remove(); // Supprime l'élément <li>
            }

            // Optionnel : Réinitialisez les indices si nécessaire
            const collectionHolder = document.querySelector('.paragraph');
            if (collectionHolder) {
                const items = collectionHolder.querySelectorAll('li');
                items.forEach((item, index) => {
                    item.setAttribute('id', `paragraph-${index}`);
                });

                // Met à jour l'index global
                collectionHolder.dataset.index = items.length;
            }
        }
    });
}

setupRemoveParagraphButtons();


