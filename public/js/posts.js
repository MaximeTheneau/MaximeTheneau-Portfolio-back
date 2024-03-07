
toggleDivWithButton('.button__altImg', '.add__altImg');
toggleDivWithButton('.button__link', '.add__link');


function toggleDivWithButton(buttonId, divId) {
    const button = document.querySelector(buttonId);
    const div = document.querySelector(divId);
  
    button.addEventListener('click', () => {
        div.classList.toggle('hidden');
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const categoryRadios = document.getElementsByName('posts[category]');
    const selectedCategory = document.querySelector('input[name="posts[category]"]:checked').value;
    console.log(selectedCategory); // Affiche la valeur de la catégorie sélectionnée
    const githubDiv = document.querySelector('.add__github');
    const websiteDiv = document.querySelector('.add__website');

  if (selectedCategory === '3') {
      githubDiv.classList.remove('hidden'); // Afficher le champ "Github"
      websiteDiv.classList.remove('hidden'); // Afficher le champ "Site web"
  } else {
      githubDiv.classList.add('hidden'); // Masquer le champ "Github"
      websiteDiv.classList.add('hidden'); // Masquer le champ "Site web"
  }
});

const categoryRadios = document.getElementsByName('posts[category]');

  for (let i = 0; i < categoryRadios.length; i++) {
      categoryRadios[i].addEventListener('change', function() {
          const selectedCategory = this.value;
         

// Affiche la valeur de la catégorie sélectionnée
          const githubDiv = document.querySelector('.add__github');
          const websiteDiv = document.querySelector('.add__website');
  
          if (selectedCategory === '3') {
              githubDiv.classList.remove('hidden'); // Afficher le champ "Github"
              websiteDiv.classList.remove('hidden'); // Afficher le champ "Site web"
          } else {
              githubDiv.classList.add('hidden'); // Masquer le champ "Github"
              websiteDiv.classList.add('hidden'); // Masquer le champ "Site web"
          }
      });

  }