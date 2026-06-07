// Livewire 4 embarque déjà Alpine.js (avec le plugin `navigate`).
// Ne PAS importer ni démarrer Alpine ici : cela créerait une 2ᵉ instance
// et casserait wire:navigate (« Alpine.navigate is not a function »).
// En revanche on peut enrichir l'Alpine fourni par Livewire via `alpine:init`.

import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

// Sélecteur de date (Flatpickr). Thème dans resources/css/app.css.
// Usage : <x-date-input model="date_debut" /> (cf. composant Blade).
document.addEventListener('alpine:init', () => {
    window.Alpine.data('datePicker', (model, classes) => ({
        fp: null,
        init() {
            this.fp = flatpickr(this.$refs.input, {
                locale: French,
                dateFormat: 'Y-m-d',    // valeur stockée (compatible base de données)
                altInput: true,
                altFormat: 'j M Y',     // affichage lisible (ex. « 7 juin 2026 »)
                altInputClass: classes, // mêmes styles que nos champs maison
                allowInput: false,      // on force le clic → ouvre le calendrier partout
                disableMobile: true,    // garde le calendrier Flatpickr (pas le natif) sur mobile
                defaultDate: this.$wire.get(model) || null,
                onChange: (dates, str) => this.$wire.set(model, str),
            });
        },
        destroy() {
            this.fp?.destroy();
        },
    }));
});
