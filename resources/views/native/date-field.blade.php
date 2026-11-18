{{--
    Date field trigger for NativePHP screens using WithEthiopicDatePicker.

    @include('ethiopic-calendar::native.date-field', [
        'picker' => $this,               // required: the screen (included views don't get $this)
        'field' => 'startDate',          // required: public ?string property, Gregorian Y-m-d
        'title' => 'Start date',         // optional label above the box
        'placeholder' => null,           // optional, defaults to "Select a date"
        'secondary' => true,             // optional: also show the date in the other calendar
    ])
--}}
@php
    $dateFieldLabel = $picker->datePickerLabel($field);
    $dateFieldSecondary = ($secondary ?? false) ? $picker->datePickerSecondaryLabel($field) : null;
    $dateFieldPlaceholder = $placeholder ?? __('ethiopic-calendar::calendar.picker.placeholder');
@endphp
<native:column class="w-full gap-1">
    @if (! empty($title))
        <native:text class="text-sm text-theme-primary">{{ $title }}</native:text>
    @endif
    <native:pressable
        a11y-label="{{ trim(($title ?? '').' '.($dateFieldLabel ?? $dateFieldPlaceholder)) }}"
        @press="openDatePicker('{{ $field }}')"
        class="w-full min-h-[56] rounded-md border border-theme-outline bg-theme-surface px-4 justify-center"
    >
        <native:row class="w-full items-center gap-3">
            <native:text class="flex-1 text-base {{ $dateFieldLabel ? 'text-theme-on-surface' : 'text-theme-on-surface-variant' }}">{{ $dateFieldLabel ?? $dateFieldPlaceholder }}</native:text>
            <native:icon name="calendar_month" class="text-theme-on-surface-variant" :size="24" />
        </native:row>
    </native:pressable>
    @if ($dateFieldSecondary)
        <native:text class="text-sm text-theme-on-surface-variant px-1">{{ $dateFieldSecondary }}</native:text>
    @endif
</native:column>
