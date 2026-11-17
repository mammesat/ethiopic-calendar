@php($labels = $config['strings']['labels'])
<div
    x-data="ethiopicDatePicker(@js($config))"
    x-modelable="value"
    {{ $attributes->merge(['class' => 'ethiopic-date-picker relative']) }}
    @keydown.escape.window="open && close()"
>
    @if ($label)
        <label for="{{ $inputId }}" class="mb-1 block text-sm font-medium text-gray-700">
            {{ $label }}@if ($required)<span class="text-red-600" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    @if ($name)
        <input type="hidden" name="{{ $name }}" x-bind:value="value ?? ''">
    @endif

    {{-- Trigger --}}
    <button
        type="button"
        id="{{ $inputId }}"
        @if ($disabled) disabled @endif
        x-on:click="show()"
        aria-haspopup="dialog"
        x-bind:aria-expanded="open"
        class="flex min-h-12 w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-base text-gray-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500"
    >
        <span x-text="label || @js($placeholder ?? $labels['placeholder'])" x-bind:class="label ? '' : 'text-gray-400'">{{ $placeholder ?? $labels['placeholder'] }}</span>
        <svg class="size-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
    </button>

    {{-- Bottom sheet --}}
    <div x-show="open" style="display: none" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-labelledby="{{ $inputId }}-title">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/40" x-on:click="close()"></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:opacity-0"
            x-on:touchstart.passive="touchStart($event)"
            x-on:touchend.passive="touchEnd($event)"
            class="relative w-full max-w-md select-none rounded-t-2xl bg-white px-4 pt-3 shadow-xl sm:rounded-2xl"
            style="padding-bottom: max(1rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto mb-2 h-1 w-10 rounded-full bg-gray-300 sm:hidden" aria-hidden="true"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between gap-2">
                <button type="button" x-on:click="view === 'years' ? pageYears(-1) : prev()" x-bind:disabled="view === 'days' && !canPrev" x-show="view !== 'months'"
                        class="flex size-12 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 disabled:opacity-30"
                        x-bind:aria-label="view === 'years' ? @js($labels['previous_years']) : @js($labels['previous_month'])">
                    <svg class="size-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                </button>
                <span x-show="view === 'months'" class="size-12"></span>

                <h2 id="{{ $inputId }}-title" class="flex-1 text-center">
                    <button type="button" x-show="view === 'days'" x-on:click="showMonths()" x-text="title" class="min-h-12 rounded-lg px-3 text-lg font-semibold text-gray-900 hover:bg-gray-100" aria-label="{{ $labels['choose_month'] }}"></button>
                    <button type="button" x-show="view === 'months'" x-on:click="showYears()" x-text="year" class="min-h-12 rounded-lg px-3 text-lg font-semibold text-gray-900 hover:bg-gray-100" aria-label="{{ $labels['choose_year'] }}"></button>
                    <span x-show="view === 'years'" x-text="yearList[0] + ' – ' + yearList[yearList.length - 1]" class="text-lg font-semibold text-gray-900"></span>
                </h2>

                <button type="button" x-on:click="view === 'years' ? pageYears(1) : next()" x-bind:disabled="view === 'days' && !canNext" x-show="view !== 'months'"
                        class="flex size-12 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 disabled:opacity-30"
                        x-bind:aria-label="view === 'years' ? @js($labels['next_years']) : @js($labels['next_month'])">
                    <svg class="size-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </button>
                <span x-show="view === 'months'" class="size-12"></span>
            </div>

            {{-- Day grid --}}
            <div x-show="view === 'days'" class="mt-2">
                <div class="grid grid-cols-7 text-center text-xs font-medium uppercase text-gray-500" aria-hidden="true">
                    <template x-for="(name, i) in weekdays" :key="i">
                        <div class="py-1" x-text="name"></div>
                    </template>
                </div>
                <div class="grid grid-cols-7 gap-y-1" role="grid">
                    <template x-for="(cell, i) in cells" :key="cell ? cell.iso : 'blank-' + i">
                        <div class="flex justify-center" role="gridcell">
                            <template x-if="cell">
                                <button
                                    type="button"
                                    x-on:click="pick(cell)"
                                    x-bind:disabled="isDisabled(cell)"
                                    x-bind:aria-pressed="isSelected(cell)"
                                    x-bind:aria-current="isToday(cell) ? 'date' : null"
                                    x-bind:data-date="cell.iso"
                                    x-text="cell.day"
                                    x-bind:class="isSelected(cell)
                                        ? 'bg-emerald-700 text-white font-semibold'
                                        : (isToday(cell) ? 'ring-2 ring-inset ring-emerald-600 text-emerald-800 font-semibold' : 'text-gray-900 hover:bg-gray-100')"
                                    class="flex size-11 items-center justify-center rounded-full text-base disabled:text-gray-300 disabled:hover:bg-transparent"
                                ></button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Month list --}}
            <div x-show="view === 'months'" class="mt-3 grid grid-cols-3 gap-2">
                <template x-for="m in monthList" :key="m.month">
                    <button type="button" x-on:click="pickMonth(m.month)" x-bind:disabled="isMonthDisabled(m.month)" x-text="m.name"
                            x-bind:class="m.month === month ? 'bg-emerald-700 text-white' : 'bg-gray-50 text-gray-900 hover:bg-gray-100'"
                            class="min-h-12 rounded-lg px-2 text-base font-medium disabled:opacity-30"></button>
                </template>
            </div>

            {{-- Year list --}}
            <div x-show="view === 'years'" class="mt-3 grid grid-cols-3 gap-2">
                <template x-for="y in yearList" :key="y">
                    <button type="button" x-on:click="pickYear(y)" x-bind:disabled="isYearDisabled(y)" x-text="y"
                            x-bind:class="y === year ? 'bg-emerald-700 text-white' : 'bg-gray-50 text-gray-900 hover:bg-gray-100'"
                            class="min-h-12 rounded-lg px-2 text-base font-medium disabled:opacity-30"></button>
                </template>
            </div>

            {{-- Footer --}}
            <div class="mt-3 flex items-center justify-between gap-2 border-t border-gray-100 pt-3">
                <button type="button" x-on:click="pickToday()" class="min-h-12 rounded-lg px-4 text-base font-semibold text-emerald-700 hover:bg-emerald-50">{{ $labels['today'] }}</button>
                <div class="flex gap-2">
                    @if ($clearable && ! $required)
                        <button type="button" x-on:click="clear()" class="min-h-12 rounded-lg px-4 text-base font-medium text-gray-600 hover:bg-gray-100">{{ $labels['clear'] }}</button>
                    @endif
                    <button type="button" x-on:click="close()" class="min-h-12 rounded-lg px-4 text-base font-medium text-gray-600 hover:bg-gray-100">{{ $labels['close'] }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
