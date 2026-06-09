<div>
    <x-admin.page-header title="Récap mensuel — Actions"
        subtitle="Synthèse de facturation par contrat : crédit, temps passé et montant à facturer." icon="trending-up" />

    {{-- Barre d'outils : recherche + filtres mois/année --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-primary" />
            <x-text-input wire:model.live.debounce.300ms="search" placeholder="Rechercher un contrat…" class="!pl-11 !pr-11" />
            @if($search !== '')
                <button wire:click="$set('search', '')" type="button" title="Effacer"
                        class="absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition hover:text-zinc-600">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            @endif
        </div>

        <div class="w-full sm:w-44">
            <x-select wire:model.live="month">
                @foreach($this->monthsList as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>

        <div class="w-full sm:w-32">
            <x-select wire:model.live="year">
                @foreach($this->yearsList as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    {{-- Tableau récap par contrat (en-tête collant : suit le défilement de la page) --}}
    <div class="mt-4 rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white">
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 first:rounded-tl-2xl"></th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5">Contrat ({{ count($this->rows) }})</th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 text-center">Crédit au mois</th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 text-center">Temps passé au mois</th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 text-center">Crédit à l'année</th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 text-center">Temps passé à l'année</th>
                    <th class="sticky -top-6 z-10 bg-primary lg:-top-8 px-5 py-3.5 text-right last:rounded-tr-2xl">À facturer HT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($this->rows as $row)
                    @php
                        $c = $row['contrat'];
                        $statutMeta = match($row['statut']) {
                            'depassement' => ['bg' => 'bg-primary', 'icon' => 'euro', 'label' => 'Crédit dépassé (heures sup.)'],
                            'ok'          => ['bg' => 'bg-emerald-500', 'icon' => 'check', 'label' => 'Crédit respecté'],
                            'vide'        => ['bg' => 'bg-red-500', 'icon' => 'x', 'label' => 'Aucune action ce mois'],
                            default       => ['bg' => 'bg-red-500', 'icon' => 'x', 'label' => 'Crédit restant'],
                        };
                    @endphp
                    <tr wire:key="recap-{{ $c->id }}" class="transition odd:bg-white even:bg-primary/[0.04] hover:bg-secondary/10">
                        <td class="px-5 py-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-white {{ $statutMeta['bg'] }}"
                                  title="{{ $statutMeta['label'] }}">
                                <x-dynamic-component :component="'lucide-'.$statutMeta['icon']" class="h-3.5 w-3.5" />
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.contrats.show', $c) }}" wire:navigate
                               class="group inline-flex items-center gap-2 font-medium text-zinc-900 hover:text-primary">
                                <x-lucide-file-text class="h-3.5 w-3.5 text-secondary" />
                                <span>{{ $c->libelle }}</span>
                            </a>
                            @if($row['type_label'])
                                <p class="mt-0.5 text-xs text-zinc-400">{{ $row['type_label'] }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center whitespace-nowrap text-zinc-600">{{ \App\Models\Action::formatHeuresHM($row['credit']) }}</td>
                        <td class="px-5 py-3 text-center whitespace-nowrap text-zinc-600">
                            {{ $row['is_fixe'] ? '/' : \App\Models\Action::formatHeuresHM($row['temps_mois']) }}
                        </td>
                        <td class="px-5 py-3 text-center whitespace-nowrap text-zinc-600">{{ \App\Models\Action::formatHeuresHM($row['credit_annuel']) }}</td>
                        <td class="px-5 py-3 text-center whitespace-nowrap text-zinc-600">
                            {{ $row['is_fixe'] ? '/' : \App\Models\Action::formatHeuresHM($row['temps_annee']) }}
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap font-semibold text-zinc-900">{{ number_format($row['fact_ht'], 2, ',', ' ') }} €</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-zinc-400">
                            Aucun contrat à récapituler pour cette période.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Légende des types de contrat --}}
    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1.5 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5"><x-lucide-clock class="h-3.5 w-3.5 text-primary" /> Horaire sans heures sup</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-clock class="h-3.5 w-3.5 text-primary" /><x-lucide-plus class="h-3 w-3 text-secondary" /> Horaire + heures sup</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-calendar class="h-3.5 w-3.5 text-primary" /> Fixe</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-file class="h-3.5 w-3.5 text-primary" /> Sup temps réel</span>
    </div>

    {{-- Tableau des totaux financiers --}}
    <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-900 text-center text-xs font-semibold uppercase tracking-wider text-white">
                    <th class="px-5 py-3.5">Facture HT</th>
                    <th class="px-5 py-3.5">Facture TTC</th>
                    <th class="px-5 py-3.5">TVA à payer</th>
                    <th class="px-5 py-3.5">RSI</th>
                    <th class="px-5 py-3.5 text-secondary">Montant NET</th>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center font-semibold text-zinc-800">
                    <td class="px-5 py-4">{{ number_format($this->totals['ht'], 2, ',', ' ') }} €</td>
                    <td class="px-5 py-4">{{ number_format($this->totals['ttc'], 2, ',', ' ') }} €</td>
                    <td class="px-5 py-4">{{ number_format($this->totals['tva'], 2, ',', ' ') }} €</td>
                    <td class="px-5 py-4">{{ number_format($this->totals['rsi'], 2, ',', ' ') }} €</td>
                    <td class="px-5 py-4 text-base text-secondary">{{ number_format($this->totals['net'], 2, ',', ' ') }} €</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
