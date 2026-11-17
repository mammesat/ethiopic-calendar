@if ($iso !== null)<time datetime="{{ $iso }}" {{ $attributes }}>{{ $text }}</time>@else<span {{ $attributes }}>{{ $placeholder }}</span>@endif
