{{--
    Material-style date picker dialog for NativePHP screens using
    WithEthiopicDatePicker. Include once, as the LAST child of a
    <native:stack fill> screen root, so it layers over the whole screen:

    @include('ethiopic-calendar::native.date-picker-dialog', ['picker' => $this])
--}}
@php($dp = $picker->datePickerState())
@if ($dp['open'])
    {{-- Backdrop: a tap outside the card cancels --}}
    <native:pressable
        a11y-label="{{ $dp['strings']['cancel'] }}"
        @press="cancelDatePicker"
        :press-opacity="1"
        class="w-full h-full bg-black/40 items-center justify-center px-6"
    >
        {{-- Card: swallows taps so they don't reach the backdrop --}}
        <native:pressable
            @press="datePickerNoop"
            :press-opacity="1"
            :press-scale="1"
            class="w-full max-w-[380] rounded-[28] bg-theme-surface shadow-lg"
        >
            {{-- Header --}}
            <native:column class="w-full px-6 pt-5 pb-3 gap-2">
                <native:text class="text-sm text-theme-on-surface-variant">{{ $dp['strings']['select_date'] }}</native:text>
                <native:text class="text-3xl text-theme-on-surface">{{ $dp['headline'] }}</native:text>
            </native:column>
            <native:divider class="border-theme-outline" />

            {{-- Month / year row --}}
            <native:row class="w-full items-center px-3 pt-2">
                <native:pressable
                    a11y-label="{{ $dp['strings']['choose_year'] }}"
                    @press="datePickerToggleYears"
                    class="h-[48] px-3 rounded-full justify-center"
                >
                    <native:row class="items-center gap-1">
                        <native:text class="text-sm font-bold text-theme-on-surface-variant">{{ $dp['title'] }}</native:text>
                        <native:icon name="{{ $dp['mode'] === 'years' ? 'arrow_drop_up' : 'arrow_drop_down' }}" class="text-theme-on-surface-variant" :size="24" />
                    </native:row>
                </native:pressable>
                <native:spacer />
                @if ($dp['mode'] === 'days')
                    <native:pressable ref="date-picker-previous" a11y-label="{{ $dp['strings']['previous_month'] }}" @press="datePickerPreviousMonth" class="w-[48] h-[48] items-center justify-center rounded-full {{ $dp['canPrevious'] ? '' : 'opacity-30' }}">
                        <native:icon name="chevron_left" class="text-theme-on-surface-variant" :size="24" />
                    </native:pressable>
                    <native:pressable ref="date-picker-next" a11y-label="{{ $dp['strings']['next_month'] }}" @press="datePickerNextMonth" class="w-[48] h-[48] items-center justify-center rounded-full {{ $dp['canNext'] ? '' : 'opacity-30' }}">
                        <native:icon name="chevron_right" class="text-theme-on-surface-variant" :size="24" />
                    </native:pressable>
                @else
                    <native:pressable a11y-label="{{ $dp['strings']['previous_years'] }}" @press="datePickerYearsPage(-1)" class="w-[48] h-[48] items-center justify-center rounded-full">
                        <native:icon name="chevron_left" class="text-theme-on-surface-variant" :size="24" />
                    </native:pressable>
                    <native:pressable a11y-label="{{ $dp['strings']['next_years'] }}" @press="datePickerYearsPage(1)" class="w-[48] h-[48] items-center justify-center rounded-full">
                        <native:icon name="chevron_right" class="text-theme-on-surface-variant" :size="24" />
                    </native:pressable>
                @endif
            </native:row>

            @if ($dp['mode'] === 'days')
                {{-- Day grid, keyed by month: a month change builds a fresh grid instead of mutating cells in place --}}
                <native:column native:key="date-picker-grid-{{ $dp['gridKey'] }}" class="w-full px-3 pb-1">
                    <native:row class="w-full h-[40] items-center">
                        @foreach ($dp['weekdays'] as $weekday)
                            <native:text class="flex-1 text-sm text-center text-theme-on-surface">{{ $weekday }}</native:text>
                        @endforeach
                    </native:row>
                    @foreach ($dp['weeks'] as $week)
                        <native:row class="w-full">
                            @foreach ($week as $cell)
                                @if ($cell === null)
                                    <native:spacer class="flex-1 h-[48]" />
                                @elseif ($cell['disabled'])
                                    <native:column class="flex-1 h-[48] items-center justify-center">
                                        <native:text class="text-base text-theme-on-surface opacity-30">{{ $cell['day'] }}</native:text>
                                    </native:column>
                                @else
                                    <native:pressable
                                        a11y-label="{{ $cell['day'] }}"
                                        @press="datePickerPickDay({{ $cell['jdn'] }})"
                                        :press-opacity="1"
                                        class="flex-1 h-[48] items-center justify-center"
                                    >
                                        <native:column class="w-[40] h-[40] rounded-full items-center justify-center {{ $cell['selected'] ? 'bg-theme-primary' : ($cell['today'] ? 'border border-theme-primary' : '') }}">
                                            <native:text class="text-base {{ $cell['selected'] ? 'text-theme-on-primary' : ($cell['today'] ? 'text-theme-primary' : 'text-theme-on-surface') }}">{{ $cell['day'] }}</native:text>
                                        </native:column>
                                    </native:pressable>
                                @endif
                            @endforeach
                        </native:row>
                    @endforeach
                </native:column>
            @else
                {{-- Year grid --}}
                <native:column class="w-full px-4 py-2 gap-2">
                    @foreach ($dp['years'] as $row)
                        <native:row class="w-full gap-2">
                            @foreach ($row as $y)
                                @if ($y['disabled'])
                                    <native:column class="flex-1 h-[44] items-center justify-center">
                                        <native:text class="text-base text-theme-on-surface opacity-30">{{ $y['year'] }}</native:text>
                                    </native:column>
                                @else
                                    <native:pressable
                                        a11y-label="{{ $y['year'] }}"
                                        @press="datePickerPickYear({{ $y['year'] }})"
                                        class="flex-1 h-[44] rounded-full items-center justify-center {{ $y['selected'] ? 'bg-theme-primary' : '' }}"
                                    >
                                        <native:text class="text-base {{ $y['selected'] ? 'text-theme-on-primary' : 'text-theme-on-surface' }}">{{ $y['year'] }}</native:text>
                                    </native:pressable>
                                @endif
                            @endforeach
                        </native:row>
                    @endforeach
                </native:column>
            @endif

            {{-- Actions --}}
            <native:row class="w-full items-center justify-end gap-2 px-3 pt-2 pb-4">
                <native:button ref="date-picker-cancel" variant="ghost" label="{{ $dp['strings']['cancel'] }}" @press="cancelDatePicker" />
                <native:button ref="date-picker-ok" variant="ghost" label="{{ $dp['strings']['ok'] }}" @press="confirmDatePicker" />
            </native:row>
        </native:pressable>
    </native:pressable>
@endif
