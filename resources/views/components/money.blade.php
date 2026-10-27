@props(['amount', 'decimals' => 2])

<span class="whitespace-nowrap">${{ number_format((float) $amount, (int) $decimals, ',', '.') }}</span>
