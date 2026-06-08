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

            // Le slide-over reste dans le DOM (wire:ignore) : on resynchronise le
            // calendrier quand la propriété Livewire change côté serveur
            // (valeur par défaut à la création, pré-remplissage en édition, reset…).
            this.$wire.$watch(model, (value) => {
                const current = this.fp?.input?.value || '';
                if ((value || '') !== current) {
                    this.fp?.setDate(value || null, false);
                }
            });
        },
        destroy() {
            this.fp?.destroy();
        },
    }));

    // Drag & drop des membres d'équipe (Gestion ▸ Équipes).
    // `memberIds` est entanglé avec Livewire ; `admins` = [{id, name, initials}].
    // On réassigne toujours le tableau (pas de push/splice) pour déclencher la synchro entangle.
    window.Alpine.data('teamMembers', (memberIds, admins) => ({
        memberIds,
        admins,
        dragId: null,
        get members() {
            return this.admins.filter((a) => this.memberIds.includes(a.id));
        },
        get available() {
            return this.admins.filter((a) => ! this.memberIds.includes(a.id));
        },
        add(id) {
            if (! this.memberIds.includes(id)) this.memberIds = [...this.memberIds, id];
        },
        remove(id) {
            this.memberIds = this.memberIds.filter((i) => i !== id);
        },
        toggle(id) {
            this.memberIds.includes(id) ? this.remove(id) : this.add(id);
        },
        start(id) {
            this.dragId = id;
        },
        dropMembers() {
            if (this.dragId !== null) this.add(this.dragId);
            this.dragId = null;
        },
        dropAvailable() {
            if (this.dragId !== null) this.remove(this.dragId);
            this.dragId = null;
        },
    }));
});
