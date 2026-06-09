// Livewire 4 embarque déjà Alpine.js (avec le plugin `navigate`).
// Ne PAS importer ni démarrer Alpine ici : cela créerait une 2ᵉ instance
// et casserait wire:navigate (« Alpine.navigate is not a function »).
// En revanche on peut enrichir l'Alpine fourni par Livewire via `alpine:init`.

import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

// ─── Tooltips Tippy.js (remplacent le `title` natif, moche, sur toute la plateforme) ───
// Tout élément portant un attribut `title` est automatiquement « enrichi » :
// on retire le `title` (sinon le tooltip natif s'affiche aussi) et on branche Tippy.
// Un MutationObserver couvre le contenu injecté dynamiquement (morphs Livewire, slide-overs…).
const tippyOptions = {
    theme: 'pwc',
    delay: [250, 0],
    duration: [150, 100],
    allowHTML: false,
};

function enhanceTooltip(el) {
    const content = el.getAttribute('title');
    if (! content) return;
    el.removeAttribute('title'); // tue le tooltip natif
    if (el._tippy) el._tippy.destroy(); // évite les doublons sur re-render
    tippy(el, { content, ...tippyOptions });
}

function scanTooltips(root) {
    if (! root || root.nodeType !== 1) return;
    if (root.hasAttribute('title')) enhanceTooltip(root);
    root.querySelectorAll?.('[title]').forEach(enhanceTooltip);
}

document.addEventListener('DOMContentLoaded', () => scanTooltips(document.body));
document.addEventListener('livewire:navigated', () => scanTooltips(document.body));

new MutationObserver((mutations) => {
    for (const m of mutations) {
        if (m.type === 'childList') {
            m.addedNodes.forEach(scanTooltips);
        } else if (m.type === 'attributes' && m.target.getAttribute('title')) {
            enhanceTooltip(m.target); // Livewire a (re)posé un title après un morph
        }
    }
}).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['title'],
});

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
