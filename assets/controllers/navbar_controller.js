import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["submenu"];

    toggle(event) {
        console.log(event)
        const submenu = event.target.closest('li').querySelector('[data-target="navbar.submenu"]');
        
        if (submenu) {
        submenu.classList.toggle("hidden");
        }
    }
}